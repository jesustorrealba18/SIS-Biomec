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
        
        if (!isset($_POST['dia_semana']) || empty($_POST['dia_semana'])) {
            echo json_encode(['status' => 'error', 'message' => 'El día de la semana es requerido']);
            exit;
        }
        
        if (!isset($_POST['hora_inicio']) || empty($_POST['hora_inicio'])) {
            echo json_encode(['status' => 'error', 'message' => 'La hora de inicio es requerida']);
            exit;
        }
        
        if (!isset($_POST['hora_fin']) || empty($_POST['hora_fin'])) {
            echo json_encode(['status' => 'error', 'message' => 'La hora de fin es requerida']);
            exit;
        }
        
        $hora_inicio = $_POST['hora_inicio'];
        $hora_fin = $_POST['hora_fin'];
        
        if (strlen($hora_inicio) > 5) {
            $hora_inicio = substr($hora_inicio, 0, 5);
        }
        if (strlen($hora_fin) > 5) {
            $hora_fin = substr($hora_fin, 0, 5);
        }
        
        $_POST['hora_inicio'] = $hora_inicio;
        $_POST['hora_fin'] = $hora_fin;
        
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora_inicio)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de hora inicio inválido (debe ser HH:MM)']);
            exit;
        }
        
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora_fin)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de hora fin inválido (debe ser HH:MM)']);
            exit;
        }
        
        if ($hora_inicio >= $hora_fin) {
            echo json_encode(['status' => 'error', 'message' => 'La hora de fin debe ser mayor a la hora de inicio']);
            exit;
        }
        
        $excluirId = null;
        if ($tipoAccion === 'actualizar' || $tipoAccion === 'editar') {
            if (!isset($_POST['id_bloque']) || empty($_POST['id_bloque'])) {
                echo json_encode(['status' => 'error', 'message' => 'ID de bloque no proporcionado para actualizar']);
                exit;
            }
            $excluirId = $_POST['id_bloque'];
        }
        
        $errores = $objHorario->validarDatos($_POST, $excluirId);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $objHorario->setDatos($_POST);
        
        $resultado = false; 
        
        if ($tipoAccion === 'editar' || $tipoAccion === 'actualizar') {
            $resultado = $objHorario->editarHorario(); 
        } else {
            $resultado = $objHorario->registrarHorario();
        }

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Operación realizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos al guardar.']);
        }
        exit;
    }

    if ($accion === 'eliminar') {
        if (!isset($_POST['id_bloque']) || empty($_POST['id_bloque'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID del bloque de horario no proporcionado.']);
            exit;
        }
        
        $id_bloque = $_POST['id_bloque'];
       
        $objHorario->setIdEliminar($id_bloque);
        
        $resultado = $objHorario->eliminarHorario(); 
        
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Bloque de Horario eliminado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el bloque.']);
        }
        exit;
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida: ' . $accion]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'listarHorario') {
        header('Content-Type: application/json');
        $resultado = $objHorario->listarHorario();
        echo json_encode($resultado);
        exit;
    }

    if ($accion === 'obtenerBloque' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $id = (int)$_GET['id'];
        $resultado = $objHorario->obtenerPorId($id);
        echo json_encode($resultado);
        exit;
    }
  
    require_once 'vista/horario.php';
}