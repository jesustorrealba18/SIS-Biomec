<?php

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\modelo\drills;
$objDrills = new drills();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'guardar') {
        $tipoAccion = isset($_POST['action_type']) ? $_POST['action_type'] : 'registrar';

        $_POST['activo'] = isset($_POST['activo']) ? 1 : 0;
        $_POST['personalizado'] = isset($_POST['personalizado']) ? 1 : 0;

        if (isset($_POST['id']) && !isset($_POST['id_drill'])) {
            $_POST['id_drill'] = $_POST['id'];
        }

        if (empty($_POST['id_usuario_creador']) && !empty($_SESSION['id'])) {
            $_POST['id_usuario_creador'] = $_SESSION['id'];
        }

        $objDrills->setDatos($_POST);

        $excluirId = ($tipoAccion === 'editar') ? (int)($_POST['id_drill'] ?? 0) : null;
        $errores = $objDrills->validarDatos($tipoAccion, $excluirId);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $resultado = false;
        if ($tipoAccion === 'editar') {
            $resultado = $objDrills->editarDrills();
            error_log("Ejecutando edición de drill ID: " . ($_POST['id_drill'] ?? 'sin ID'));
        } else {
            $resultado = $objDrills->registrarDrills();
            error_log("Ejecutando registro de nuevo drill");
        }

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Operación realizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos al guardar.']);
        }
        exit;
    }

    if ($accion === 'eliminar') {
        $id_drill = isset($_POST['id_drill']) ? (int)$_POST['id_drill'] : 0;

        if ($id_drill > 0) {
            $objDrills->setIdEliminar($id_drill);
            $resultado = $objDrills->eliminarDrills();

            if ($resultado) {
                echo json_encode(['status' => 'success', 'message' => 'Drill eliminado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el registro.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID de drill no válido.']);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    if ($accion === 'listarDrills') {
        header('Content-Type: application/json');
        echo json_encode($objDrills->listarDrills());
        exit;
    }

    if ($accion === 'obtenerDrills' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $drill = $objDrills->obtenerPorId((int)$_GET['id']);
        echo json_encode($drill);
        exit;
    }

    require_once 'vista/drills.php';
}