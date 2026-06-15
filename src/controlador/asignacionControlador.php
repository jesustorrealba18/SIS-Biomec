<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/SIS-Biomec/errores.log');

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\modelo\Asignacion;
use GrupoProyecto\SisBiomec\modelo\Carriles;
use GrupoProyecto\SisBiomec\modelo\Horario;
use GrupoProyecto\SisBiomec\modelo\Grupo;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objAsignacion = new Asignacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        Autorizacion::exigir('asignacion', 'gestionar');
        $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;

        if ($objAsignacion->desactivarAsignacion($id)) { 
            echo json_encode(['status' => 'success', 'message' => 'Asignación desactivada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo desactivar la asignación.']);
        }
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminarFisico') {
        Autorizacion::exigir('asignacion', 'gestionar');
        $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;

        if ($objAsignacion->eliminarAsignacion($id)) { 
            echo json_encode(['status' => 'success', 'message' => 'Asignación eliminada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la asignación.']);
        }
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'reactivar') {
        Autorizacion::exigir('asignacion', 'gestionar');
        $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
        
        if ($objAsignacion->reactivarAsignacion($id)) { 
            echo json_encode(['status' => 'success', 'message' => 'Asignación reactivada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo reactivar la asignación.']);
        }
        exit;
    }    

    Autorizacion::exigir('asignacion', 'gestionar');
    
    $idOriginal = !empty($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : null;
    
    $errores = $objAsignacion->validarDatos($_POST, $idOriginal);

    if (!empty($errores)) {
        echo json_encode(['status' => 'warning', 'errores' => $errores]);
        exit;
    }

    $resultado = false; 

    if ($idOriginal) {
        $resultado = $objAsignacion->actualizarAsignacion($_POST);
    } else {
        $resultado = $objAsignacion->registrarAsignacion($_POST);
    }

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Asignación guardada con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en base de datos al guardar.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarAsignaciones') {
        header('Content-Type: application/json');
        $estadoInput = $_GET['estado'] ?? 'Activo';
        $estadoInt = ($estadoInput === 'Activo') ? 1 : 0;
        echo json_encode($objAsignacion->listarAsignaciones($estadoInt));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarCarriles') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarCarrilesActivos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarBloquesHorarios') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarHorariosActivos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarGruposParaSelect') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarTodosLosGrupos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDatosFormulario') {
        header('Content-Type: application/json');
        $datos = [
            'carriles' => $objAsignacion->listarCarrilesActivos(),
            'bloques_horarios' => $objAsignacion->listarHorariosActivos(),
            'grupos' => $objAsignacion->listarTodosLosGrupos()
        ];
        echo json_encode($datos);
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerAsignacion' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->obtenerAsignacionPorId((int)$_GET['id']));
        exit; 
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerGrupo' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->obtenerGrupoPorId((int)$_GET['id']));
        exit; 
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarGrupos') {
        header('Content-Type: application/json');
        $estadoInput = $_GET['estado'] ?? 'Activo';
        $estadoInt = ($estadoInput === 'Activo') ? 1 : 0;
        echo json_encode($objAsignacion->listarGrupos($estadoInt));
        exit;
    }

    require_once 'vista/asignacion.php';
}