<?php

session_start();
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\modelo\entrenador;

$objentrenador = new Entrenador();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $excluirCedula = !empty($_POST['cedula']) ? $_POST['cedula'] : null;
    $errores = $objentrenador->validarDatos($_POST, $excluirCedula);

    if (!empty($errores)) {
        echo json_encode(['status' => 'warning', 'errores' => $errores]);
        exit;
    }

    $resultado = false; 

    if ($excluirCedula) {
        $resultado = $objentrenador->actualizarEntrenador($_POST, $excluirCedula);
    } else {
        $resultado = $objentrenador->registrarEntrenador($_POST);
    }

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Operación exitosa.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en BD o función en desarrollo.']);
    }
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarEntrenador') {
        header('Content-Type: application/json');
        echo json_encode($objentrenador->listarEntrenador());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'verPerfilEntrenador' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $datosEntrenador = $objentrenador->obtenerPorId((int)$_GET['id']);
        echo json_encode($datosEntrenador);
        exit;
    }
    
       
    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerEntrenador' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objentrenador->obtenerPorId((int)$_GET['id']));
        exit; 
    }
  
   if (isset($_GET['eliminar'])) {
    $objentrenador->eliminarEntrenador($_GET['eliminar']);
    header('Location: ?p=entrenador&m=eliminado'); 
    exit;
}

$entrenador = $objentrenador->listarEntrenador();
    require_once 'vista/entrenador.php';
}