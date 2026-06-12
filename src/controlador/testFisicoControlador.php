<?php

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\modelo\TestFisico;
use GrupoProyecto\SisBiomec\modelo\Atleta;

$objTest = new TestFisico();
$id_usuario = $_SESSION['id'];

// =====================================================================
// RUTAS GET
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarTests') {
        header('Content-Type: application/json');
        $id_atleta = !empty($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        $id_tipo_test = !empty($_GET['id_tipo_test']) ? (int)$_GET['id_tipo_test'] : 0;
        $estado = trim($_GET['estado'] ?? '');

        echo json_encode($objTest->listarTests($id_atleta, $id_tipo_test, $estado));
        exit;
    }

    if ($accion === 'listarTiposTests') {
        header('Content-Type: application/json');
        echo json_encode($objTest->listarTiposTests());
        exit;
    }

    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->listar());
        exit;
    }

    if ($accion === 'obtenerVariables') {
        header('Content-Type: application/json');
        $id_tipo_test = isset($_GET['id_tipo_test']) ? (int)$_GET['id_tipo_test'] : 0;
        $id_test_pers = isset($_GET['id_test_pers']) ? (int)$_GET['id_test_pers'] : 0;

        if ($id_tipo_test > 0) {
            echo json_encode($objTest->listarVariablesPorTipo($id_tipo_test));
        } elseif ($id_test_pers > 0) {
            echo json_encode($objTest->listarVariablesPorPersonalizado($id_test_pers));
        } else {
            echo json_encode([]);
        }
        exit;
    }

    if ($accion === 'obtenerDetalle') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID invalido.']);
            exit;
        }
        $detalle = $objTest->obtenerDetallePorId($id);
        echo json_encode($detalle);
        exit;
    }

    if ($accion === 'resumenAtleta') {
        header('Content-Type: application/json');
        $id_atleta = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        if ($id_atleta <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de atleta invalido.']);
            exit;
        }
        echo json_encode($objTest->resumenPorAtleta($id_atleta));
        exit;
    }

    require_once 'vista/testFisico.php';
    exit;
}

// =====================================================================
// RUTAS POST
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? '';

    ob_start();

    if ($accionPost === 'registrar') {
        Autorizacion::exigir('testFisico', 'registrar');

        $_POST['id_usuario_toma'] = $id_usuario;

        if ($objTest->registrarTest($_POST)) {
            ob_end_clean();
            $datosGuardados = $_POST;
            unset($datosGuardados['accion']);
            Bitacora::registrar(
                $id_usuario,
                'Tests Fisicos',
                'CREATE',
                null,
                'Registro completo',
                null,
                json_encode($datosGuardados, JSON_UNESCAPED_UNICODE)
            );
            echo json_encode(['status' => 'success', 'message' => 'Test fisico registrado correctamente.']);
        } else {
            ob_end_clean();
            $errores = $objTest->obtenerErrores();
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al registrar el test.']);
            }
        }
        exit;
    }

    if ($accionPost === 'actualizar') {
        Autorizacion::exigir('testFisico', 'registrar');

        $id_registro = (int)($_POST['id_registro_test'] ?? 0);
        if ($id_registro <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID de registro no valido.']);
            exit;
        }

        if ($objTest->actualizarTest($_POST, $id_registro)) {
            ob_end_clean();
            $datosNuevos = $_POST;
            unset($datosNuevos['accion'], $datosNuevos['id_registro_test']);
            Bitacora::registrar(
                $id_usuario,
                'Tests Fisicos',
                'UPDATE',
                $id_registro,
                'Datos del test',
                'Ver historial previo',
                json_encode($datosNuevos, JSON_UNESCAPED_UNICODE)
            );
            echo json_encode(['status' => 'success', 'message' => 'Test fisico actualizado correctamente.']);
        } else {
            ob_end_clean();
            $errores = $objTest->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'Error al actualizar el test.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    if ($accionPost === 'eliminar') {
        Autorizacion::exigir('testFisico', 'registrar');

        $id = (int)($_POST['id_registro_test'] ?? 0);
        if ($id <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID invalido.']);
            exit;
        }

        if ($objTest->eliminarTest($id)) {
            ob_end_clean();
            Bitacora::registrar(
                $id_usuario,
                'Tests Fisicos',
                'DELETE',
                $id,
                'Registro completo',
                null,
                'Eliminado'
            );
            echo json_encode(['status' => 'success']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el test.']);
        }
        exit;
    }

    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Accion no soportada.']);
    exit;
}
