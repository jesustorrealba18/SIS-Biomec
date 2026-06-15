<?php

if (empty($_SESSION['id'])) {
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\modelo\carriles;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
$objCarril = new carriles();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'guardar') {
        $tipoAccion = isset($_POST['action_type']) ? $_POST['action_type'] : 'registrar';
        Autorizacion::exigir('carriles', $tipoAccion === 'actualizar' ? 'editar' : 'crear');
        
        $excluirCedula = ($tipoAccion === 'actualizar') ? ($_POST['id_carril'] ?? null) : null;
        
        $errores = $objCarril->validarDatos($_POST, $excluirCedula);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $resultado = false; 
        
        if ($tipoAccion === 'actualizar') {
            $resultado = $objCarril->actualizarCarriles($_POST);
        } else {
            $resultado = $objCarril->registrarCarriles($_POST);
        }

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Operación realizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos al guardar.']);
        }
        exit;
    }

    if ($accion === 'eliminar') {
        Autorizacion::exigir('carriles', 'eliminar');
        $id_carril = isset($_POST['id_carril']) ? $_POST['id_carril'] : null;

        if ($id_carril) {
            $resultado = $objCarril->eliminarCarril($id_carril);
            if ($resultado) {
                echo json_encode(['status' => 'success', 'message' => 'Carril eliminado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el carril.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID del carril no proporcionado.']);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'listarCarriles') {
        header('Content-Type: application/json');
        echo json_encode($objCarril->listarCarriles());
        exit;
    }

    if ($accion === 'obtenerCarriles' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objCarril->obtenerPorId((int)$_GET['id']));
        exit;
    }
  
    require_once 'vista/carriles.php';
}