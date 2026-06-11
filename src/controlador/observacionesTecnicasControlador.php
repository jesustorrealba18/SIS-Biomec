<?php

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\modelo\ObservacionTecnica;
use GrupoProyecto\SisBiomec\modelo\Atleta;

$objObs = new ObservacionTecnica();
$id_usuario = $_SESSION['id'];

// =====================================================================
// RUTAS GET
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarObservaciones') {
        header('Content-Type: application/json');
        $id_atleta = !empty($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        $id_sesion = !empty($_GET['id_sesion']) ? (int)$_GET['id_sesion'] : 0;
        $id_aspecto = !empty($_GET['id_aspecto']) ? (int)$_GET['id_aspecto'] : 0;

        echo json_encode($objObs->listarObservaciones($id_atleta, $id_sesion, $id_aspecto));
        exit;
    }

    if ($accion === 'listarAspectosTecnicos') {
        header('Content-Type: application/json');
        echo json_encode($objObs->listarAspectosTecnicos());
        exit;
    }

    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->listar());
        exit;
    }

    if ($accion === 'obtenerDetalle') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID invalido.']);
            exit;
        }
        $detalle = $objObs->obtenerDetallePorId($id);
        echo json_encode($detalle);
        exit;
    }

    if ($accion === 'resumenAspectos') {
        header('Content-Type: application/json');
        $id_atleta = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        if ($id_atleta <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de atleta invalido.']);
            exit;
        }
        echo json_encode($objObs->resumenPorAspecto($id_atleta));
        exit;
    }

    require_once 'vista/observacionesTecnicas.php';
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
        Autorizacion::exigir('observacionesTecnicas', 'registrar');

        $_POST['id_usuario'] = $id_usuario;

        if ($objObs->registrarObservacion($_POST)) {
            ob_end_clean();
            $datosGuardados = $_POST;
            unset($datosGuardados['accion']);
            Bitacora::registrar(
                $id_usuario,
                'Observaciones Tecnicas',
                'CREATE',
                null,
                'Registro completo',
                null,
                json_encode($datosGuardados, JSON_UNESCAPED_UNICODE)
            );
            echo json_encode(['status' => 'success', 'message' => 'Observacion registrada correctamente.']);
        } else {
            ob_end_clean();
            $errores = $objObs->obtenerErrores();
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al registrar la observacion.']);
            }
        }
        exit;
    }

    if ($accionPost === 'actualizar') {
        Autorizacion::exigir('observacionesTecnicas', 'registrar');

        $id_observacion = (int)($_POST['id_observacion'] ?? 0);
        if ($id_observacion <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID de observacion no valido.']);
            exit;
        }

        if ($objObs->actualizarObservacion($_POST, $id_observacion)) {
            ob_end_clean();
            $datosNuevos = $_POST;
            unset($datosNuevos['accion'], $datosNuevos['id_observacion']);
            Bitacora::registrar(
                $id_usuario,
                'Observaciones Tecnicas',
                'UPDATE',
                $id_observacion,
                'Datos de la observacion',
                'Ver historial previo',
                json_encode($datosNuevos, JSON_UNESCAPED_UNICODE)
            );
            echo json_encode(['status' => 'success', 'message' => 'Observacion actualizada correctamente.']);
        } else {
            ob_end_clean();
            $errores = $objObs->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'Error al actualizar la observacion.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    if ($accionPost === 'eliminar') {
        Autorizacion::exigir('observacionesTecnicas', 'registrar');

        $id = (int)($_POST['id_observacion'] ?? 0);
        if ($id <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID invalido.']);
            exit;
        }

        if ($objObs->eliminarObservacion($id)) {
            ob_end_clean();
            Bitacora::registrar(
                $id_usuario,
                'Observaciones Tecnicas',
                'DELETE',
                $id,
                'Registro completo',
                null,
                'Eliminado'
            );
            echo json_encode(['status' => 'success']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la observacion.']);
        }
        exit;
    }

    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Accion no soportada.']);
    exit;
}
