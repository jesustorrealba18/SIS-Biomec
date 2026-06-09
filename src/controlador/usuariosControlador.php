<?php

use GrupoProyecto\SisBiomec\modelo\UsuarioModelo;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objUsuario = new UsuarioModelo();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarUsuarios') {
        header('Content-Type: application/json');
        echo json_encode($objUsuario->listarUsuarios());
        exit;
    }

    if ($accion === 'obtenerUsuario' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objUsuario->obtenerPorId((int)$_GET['id']));
        exit;
    }

    if ($accion === 'listarRoles') {
        header('Content-Type: application/json');
        $conex = $objUsuario->getConex1();
        $stmt = $conex->query("SELECT id_rol, nombre FROM roles WHERE activo = 1 ORDER BY nombre");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    require_once 'vista/usuarios.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    Autorizacion::exigir('seguridad', 'usuarios');
    $accionPost = $_POST['accion'] ?? '';

    if ($accionPost === 'guardar') {
        $errores = $objUsuario->validarDatos($_POST);
        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        if (empty($_POST['contrasena']) || strlen(trim($_POST['contrasena'])) < 6) {
            echo json_encode(['status' => 'warning', 'errores' => ['contrasena' => 'La contrasena debe tener al menos 6 caracteres.']]);
            exit;
        }

        if ($objUsuario->crearUsuario($_POST)) {
            $conex = $objUsuario->getConex1();
            $id = (int)$conex->lastInsertId();
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'CREATE', $id, 'usuario', null, $_POST['nombres'] . ' ' . $_POST['apellidos']);
            echo json_encode(['status' => 'success', 'message' => 'Usuario creado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al crear el usuario.']);
        }
        exit;
    }

    if ($accionPost === 'editar') {
        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $datos = $objUsuario->obtenerPorId($idUsuario);
        if (!$datos) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
            exit;
        }

        $errores = $objUsuario->validarDatos($_POST, $_POST['correo']);
        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $_POST['id_usuario'] = $idUsuario;
        if ($objUsuario->actualizarUsuario($_POST)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'UPDATE', $idUsuario, 'usuario', null, $_POST['nombres'] . ' ' . $_POST['apellidos']);
            echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el usuario.']);
        }
        exit;
    }

    if ($accionPost === 'toggleEstado') {
        $id = (int)($_POST['id_usuario'] ?? 0);
        $estado = $_POST['estado'] === '1';
        $accion = $estado ? 'Activar' : 'Desactivar';
        if ($objUsuario->toggleActivo($id, $estado)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'UPDATE', $id, 'activo', $estado ? '0' : '1', (string)(int)$estado);
            echo json_encode(['status' => 'success', 'message' => "Usuario {$accion}do."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al cambiar estado.']);
        }
        exit;
    }

    if ($accionPost === 'resetPassword') {
        $id = (int)($_POST['id_usuario'] ?? 0);
        $nuevaPass = $_POST['nueva_contrasena'] ?? '';
        if (strlen(trim($nuevaPass)) < 6) {
            echo json_encode(['status' => 'warning', 'errores' => ['nueva_contrasena' => 'La contrasena debe tener al menos 6 caracteres.']]);
            exit;
        }
        if ($objUsuario->resetearContrasena($id, $nuevaPass)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'UPDATE', $id, 'contrasena', '***', '***');
            echo json_encode(['status' => 'success', 'message' => 'Contrasena actualizada.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al resetear contrasena.']);
        }
        exit;
    }

    if ($accionPost === 'eliminar') {
        $id = (int)($_POST['id_usuario'] ?? 0);
        if ($id === (int)($_SESSION['id'] ?? 0)) {
            echo json_encode(['status' => 'error', 'message' => 'No puedes eliminar tu propia cuenta.']);
            exit;
        }
        if ($objUsuario->eliminarUsuario($id)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'DELETE', $id, 'usuario', null, null);
            echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el usuario.']);
        }
        exit;
    }
}
