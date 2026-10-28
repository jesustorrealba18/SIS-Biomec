<?php

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\modelo\horario;

$objHorario = new horario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'guardar') {
        $tipoAccion = $_POST['action_type'] ?? 'registrar';
        $objHorario->setDatos($_POST);

        $excluirId = null;
        if (in_array($tipoAccion, ['editar', 'actualizar'], true)) {
            $excluirId = !empty($_POST['id_bloque']) ? (int)$_POST['id_bloque'] : null;
            if (empty($excluirId)) {
                echo json_encode(['status' => 'error', 'message' => 'ID de bloque no proporcionado']);
                exit;
            }
        }

        $errores = $objHorario->validarDatos($excluirId);
        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $ok = in_array($tipoAccion, ['editar', 'actualizar'], true)
            ? $objHorario->editarHorario()
            : $objHorario->registrarHorario();

        echo json_encode($ok
            ? ['status' => 'success', 'message' => 'Operación realizada con éxito.']
            : ['status' => 'error',   'message' => 'Error en la base de datos al guardar.']
        );
        exit;
    }

    if ($accion === 'eliminar') {
        if (empty($_POST['id_bloque'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado.']);
            exit;
        }
        $objHorario->setIdEliminar((int)$_POST['id_bloque']);
        echo json_encode($objHorario->eliminarHorario()
            ? ['status' => 'success', 'message' => 'Bloque desactivado correctamente.']
            : ['status' => 'error',   'message' => 'No se pudo desactivar el bloque.']
        );
        exit;
    }

    if ($accion === 'reactivar') {
        if (empty($_POST['id_bloque'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado.']);
            exit;
        }
        $objHorario->setIdEliminar((int)$_POST['id_bloque']);
        echo json_encode($objHorario->reactivarHorario()
            ? ['status' => 'success', 'message' => 'Bloque reactivado correctamente.']
            : ['status' => 'error',   'message' => 'No se pudo reactivar (posible solapamiento con otro bloque activo).']
        );
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => "Acción no reconocida: $accion"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarHorario') {
        header('Content-Type: application/json');
        echo json_encode($objHorario->listarHorario());
        exit;
    }

    if ($accion === 'obtenerBloque' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objHorario->obtenerPorId((int)$_GET['id']));
        exit;
    }

    require_once 'vista/horario.php';
}