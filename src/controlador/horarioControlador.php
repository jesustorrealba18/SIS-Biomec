<?php

if (empty($_SESSION['id'])) {
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\modelo\horario;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
$objHorario = new horario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'guardar') {
        $tipoAccion = isset($_POST['action_type']) ? $_POST['action_type'] : 'registrar';
        
        $excluirId = ($tipoAccion === 'actualizar') ? ($_POST['id_bloque'] ?? null) : null;
        
        if (empty($_POST['hora_inicio']) || empty($_POST['hora_fin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Las horas son requeridas']);
            exit;
        }
        
        $_POST['hora_inicio'] = substr($_POST['hora_inicio'], 0, 5);
        $_POST['hora_fin'] = substr($_POST['hora_fin'], 0, 5);
        
        error_log("HORAS NORMALIZADAS: inicio={$_POST['hora_inicio']}, fin={$_POST['hora_fin']}");
        
        $errores = $objHorario->validarDatos($_POST, $excluirId);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $resultado = false; 
        
        if ($tipoAccion === 'actualizar') {
            error_log("EJECUTANDO ACTUALIZAR para ID: " . ($_POST['id_bloque'] ?? 'null'));
            $resultado = $objHorario->actualizarHorario($_POST);
            error_log("RESULTADO ACTUALIZAR: " . ($resultado ? 'true' : 'false'));
        } else {
            error_log("EJECUTANDO REGISTRAR");
            $resultado = $objHorario->registrarHorario($_POST);
            error_log("RESULTADO REGISTRAR: " . ($resultado ? 'true' : 'false'));
        }

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Operación realizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos al guardar.']);
        }
        exit;
    }

    if ($accion === 'eliminar') {
        $id_bloque = isset($_POST['id_bloque']) ? $_POST['id_bloque'] : null;

        if ($id_bloque) {
            $resultado = $objHorario->eliminarHorario($id_bloque);
            if ($resultado) {
                echo json_encode(['status' => 'success', 'message' => 'Bloque de Horario eliminado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el bloque.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID del bloque de horario no proporcionado.']);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'listarHorario') {
        header('Content-Type: application/json');
        $resultado = $objHorario->listarHorario();
        error_log("LISTAR HORARIO: " . json_encode($resultado));
        echo json_encode($resultado);
        exit;
    }

    if ($accion === 'obtenerBloque' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $resultado = $objHorario->obtenerPorId((int)$_GET['id']);
        error_log("OBTENER BLOQUE ID={$_GET['id']}: " . json_encode($resultado));
        echo json_encode($resultado);
        exit;
    }
  
    require_once 'vista/horario.php';
}