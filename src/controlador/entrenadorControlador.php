<?php

session_start();
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\modelo\entrenador;
$objEntrenador = new entrenador();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'guardar') {
        // CORREGIDO: Usamos la bandera explícita enviada desde el front
        $tipoAccion = isset($_POST['action_type']) ? $_POST['action_type'] : 'registrar';
        
        // Si editamos, enviamos la cédula para que la validación "único" la ignore
        $excluirCedula = ($tipoAccion === 'actualizar') ? ($_POST['cedula'] ?? null) : null;
        
        $errores = $objEntrenador->validarDatos($_POST, $excluirCedula);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $resultado = false; 
        
        if ($tipoAccion === 'actualizar') {
            $resultado = $objEntrenador->actualizarEntrenador($_POST);
        } else {
            $resultado = $objEntrenador->registrarEntrenador($_POST);
        }

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Operación realizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos al guardar o no se alteraron campos.']);
        }
        exit;
    }

    if ($accion === 'eliminar') {
        $id_entrenador = isset($_POST['id_entrenador']) ? $_POST['id_entrenador'] : null;

        if ($id_entrenador) {
            $resultado = $objEntrenador->eliminarEntrenador($id_entrenador);
            if ($resultado) {
                echo json_encode(['status' => 'success', 'message' => 'Entrenador eliminado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el registro de la BD.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID de entrenador no proporcionado.']);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'listarEntrenador') {
        header('Content-Type: application/json');
        echo json_encode($objEntrenador->listarEntrenador());
        exit;
    }

    if ($accion === 'obtenerEntrenador' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objEntrenador->obtenerPorId((int)$_GET['id']));
        exit;
    }
  
    require_once 'vista/entrenador.php';
}