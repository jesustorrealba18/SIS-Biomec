<?php

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\modelo\Asignacion;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objAsignacion = new Asignacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] === 'eliminar') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            if ($objAsignacion->desactivarAsignacion($id)) { 
                echo json_encode(['status' => 'success', 'message' => 'Asignación desactivada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo desactivar la asignación.']);
            }
            exit;
        }
        
        if ($_POST['accion'] === 'eliminarFisico') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            if ($objAsignacion->eliminarAsignacion($id)) { 
                echo json_encode(['status' => 'success', 'message' => 'Asignación eliminada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la asignación.']);
            }
            exit;
        }
        
        if ($_POST['accion'] === 'reactivar') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            if ($objAsignacion->reactivarAsignacion($id)) { 
                echo json_encode(['status' => 'success', 'message' => 'Asignación reactivada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo reactivar la asignación.']);
            }
            exit;
        }
    }

    Autorizacion::exigir('asignacion', 'gestionar');
    
    $_POST['activa'] = isset($_POST['activa']) ? 1 : 0;

    if (isset($_POST['fecha_vigente_inicio']) && !isset($_POST['fecha_vigencia_inicio'])) {
        $_POST['fecha_vigencia_inicio'] = $_POST['fecha_vigente_inicio'];
    }
    if (isset($_POST['fecha_vigente_fin']) && !isset($_POST['fecha_vigencia_fin'])) {
        $_POST['fecha_vigencia_fin'] = $_POST['fecha_vigente_fin'];
    }

    $camposRequeridos = ['id_carril', 'id_bloque_horario', 'id_grupo', 'fecha_vigencia_inicio'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            echo json_encode([
                'status' => 'warning', 
                'errores' => [$campo => "El campo $campo es requerido"]
            ]);
            exit;
        }
    }
    
    $idOriginal = !empty($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : null;
    
    error_log("Datos normalizados: " . print_r($_POST, true));
  
    $errores = $objAsignacion->validarDatos($_POST, $idOriginal);

    if (!empty($errores)) {
        echo json_encode(['status' => 'warning', 'errores' => $errores]);
        exit;
    }
    
    $objAsignacion->setDatos($_POST);
    $resultado = false; 

    try {
        if ($idOriginal) {
            error_log("Ejecutando EDITAR asignación ID: $idOriginal");
            $resultado = $objAsignacion->editarAsignacion();
        } else {
            error_log("Ejecutando REGISTRAR nueva asignación");
            $resultado = $objAsignacion->registrarAsignacion();
        }
        
        error_log("Resultado de la operación: " . ($resultado ? 'TRUE' : 'FALSE'));

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Asignación guardada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en base de datos al guardar.']);
        }
    } catch (Exception $e) {
        error_log("EXCEPCIÓN en asignacion: " . $e->getMessage());
        error_log($e->getTraceAsString());
        echo json_encode(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
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

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarHorarios') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarHorariosActivos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarGruposParaSelect') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarTodosLosGrupos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerAsignacion' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->obtenerAsignacionPorId((int)$_GET['id']));
        exit; 
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleCarril' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $carril = $objAsignacion->obtenerCarrilPorId((int)$_GET['id']);
        echo json_encode($carril);
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleBloque' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $bloque = $objAsignacion->obtenerHorarioPorId((int)$_GET['id']);
        echo json_encode($bloque);
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleGrupo' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $grupo = $objAsignacion->obtenerGrupoPorId((int)$_GET['id']);
        echo json_encode($grupo);
        exit;
    }

    require_once 'vista/asignacion.php';
}