<?php

ob_start();

if (empty($_SESSION['id'])) {
    ob_end_clean();
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Temporada;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objTemporada = new Temporada();

// =====================================================================
// RUTAS GET
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarTemporadas') {
        header('Content-Type: application/json');
        ob_end_clean();
        echo json_encode($objTemporada->listarTemporadas());
        exit;
    }

    if ($accion === 'obtenerTemporada') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        ob_end_clean();
        echo json_encode($objTemporada->obtenerPorId($id));
        exit;
    }

    require_once 'vista/temporadas.php';
    exit;
}

// =====================================================================
// RUTAS POST
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['id'])) {
        ob_end_clean();
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Sesion expirada']);
        exit;
    }

    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? $_GET['accion'] ?? '';

    if ($accionPost === 'guardar') {
        Autorizacion::exigir('temporadas', 'registrar');
        $errores = $objTemporada->validarDatos($_POST);

        if (!empty($errores)) {
            ob_end_clean();
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $idTemporada = (int)($_POST['id_temporada'] ?? 0);

        if ($idTemporada > 0) {
            if ($objTemporada->actualizarTemporada($_POST)) {
                Bitacora::registrar(
                    $_SESSION['id'], 'Modulo Temporadas', 'UPDATE',
                    $idTemporada, 'temporada', null, $_POST['nombre'] ?? ''
                );
                ob_end_clean();
                echo json_encode(['status' => 'success', 'message' => 'Temporada actualizada exitosamente.']);
            } else {
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'Ocurrio un error al actualizar la temporada.']);
            }
        } else {
            if ($objTemporada->registrarTemporada($_POST)) {
                Bitacora::registrar(
                    $_SESSION['id'], 'Modulo Temporadas', 'CREATE', null,
                    'temporada', null, $_POST['nombre'] ?? 'Nueva temporada'
                );
                ob_end_clean();
                echo json_encode(['status' => 'success', 'message' => 'Temporada registrada exitosamente.']);
            } else {
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'Ocurrio un error al registrar la temporada.']);
            }
        }
        exit;
    }

    if ($accionPost === 'eliminar') {
        Autorizacion::exigir('temporadas', 'registrar');
        $id = (int)($_POST['id_temporada'] ?? 0);

        $resultado = $objTemporada->eliminarTemporada($id);

        if ($resultado['exito']) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Temporadas', 'DELETE',
                $id, 'temporada', null, 'Eliminada'
            );
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => $resultado['mensaje']]);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
        }
        exit;
    }

    if ($accionPost === 'activar') {
        Autorizacion::exigir('temporadas', 'registrar');
        $id = (int)($_POST['id_temporada'] ?? 0);

        if ($objTemporada->activarTemporada($id)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Temporadas', 'UPDATE',
                $id, 'activa', null, 'Activada'
            );
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => 'Temporada activada exitosamente.']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'No se pudo activar la temporada.']);
        }
        exit;
    }
}
