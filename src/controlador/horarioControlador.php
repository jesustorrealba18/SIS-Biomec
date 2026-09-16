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

        $objHorario->setDatos($_POST);

        $excluirId = null;
        if ($tipoAccion === 'editar' || $tipoAccion === 'actualizar') {
            $excluirId = isset($_POST['id_bloque']) ? (int)$_POST['id_bloque'] : null;
            if (empty($excluirId)) {
                echo json_encode(['status' => 'error', 'message' => 'ID de bloque no proporcionado para actualizar']);
                exit;
            }
        }

        $errores = $objHorario->validarDatos($excluirId);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

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

        $id_bloque = (int)$_POST['id_bloque'];
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
        echo json_encode($objHorario->listarHorario());
        exit;
    }

    if ($accion === 'obtenerBloque' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $id = (int)$_GET['id'];
        echo json_encode($objHorario->obtenerPorId($id));
        exit;
    }

    require_once 'vista/horario.php';
}