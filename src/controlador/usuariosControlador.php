<?php

use GrupoProyecto\SisBiomec\modelo\UsuarioModelo;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objUsuario = new UsuarioModelo();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarUsuarios') {
        Autorizacion::exigir('seguridad', 'usuarios');
        header('Content-Type: application/json');
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $busqueda = substr(trim($_GET['busqueda'] ?? ''), 0, 100);
        $ordenar = preg_replace('/[^a-zA-Z_]/', '', $_GET['ordenar'] ?? 'id_usuario');
        $direccion = strtoupper($_GET['direccion'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        echo json_encode($objUsuario->listarUsuarios($pagina, 20, $busqueda, $ordenar, $direccion));
        exit;
    }

    if ($accion === 'obtenerUsuario' && isset($_GET['id'])) {
        Autorizacion::exigir('seguridad', 'usuarios');
        header('Content-Type: application/json');
        echo json_encode($objUsuario->obtenerPorId((int)$_GET['id']));
        exit;
    }

    if ($accion === 'listarRoles') {
        Autorizacion::exigir('seguridad', 'usuarios');
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

    $camposTexto = ['nombres', 'apellidos', 'cedula', 'correo', 'contrasena', 'nueva_contrasena'];
    foreach ($camposTexto as $c) {
        if (isset($_POST[$c]) && is_string($_POST[$c])) {
            $_POST[$c] = trim($_POST[$c]);
        }
    }

    if ($accionPost === 'guardar') {
        $datos = [
            'nombres'    => $_POST['nombres'] ?? '',
            'apellidos'  => $_POST['apellidos'] ?? '',
            'cedula'     => $_POST['cedula'] ?: null,
            'correo'     => $_POST['correo'] ?? '',
            'contrasena' => $_POST['contrasena'] ?? '',
            'roles'      => $_POST['roles'] ?? [],
        ];

        $errores = $objUsuario->validarDatos($datos);
        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $erroresRoles = $objUsuario->validarRoles($datos['roles']);
        if (!empty($erroresRoles)) {
            echo json_encode(['status' => 'warning', 'errores' => $erroresRoles]);
            exit;
        }

        $erroresPass = $objUsuario->validarContrasena($datos['contrasena']);
        if (!empty($erroresPass)) {
            echo json_encode(['status' => 'warning', 'errores' => $erroresPass]);
            exit;
        }

        $id = $objUsuario->crearUsuario($datos);
        if ($id) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'CREATE', $id, 'usuario', null, $datos['nombres'] . ' ' . $datos['apellidos']);
            echo json_encode(['status' => 'success', 'message' => 'Usuario creado correctamente.']);
        } else {
            $errores = $objUsuario->obtenerErrores();
            $mensaje = !empty($errores) ? implode(' ', $errores) : 'Error al crear el usuario.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    if ($accionPost === 'editar') {
        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $existente = $objUsuario->obtenerPorId($idUsuario);
        if (!$existente) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
            exit;
        }

        $datos = [
            'id_usuario' => $idUsuario,
            'nombres'    => $_POST['nombres'] ?? '',
            'apellidos'  => $_POST['apellidos'] ?? '',
            'cedula'     => $_POST['cedula'] ?: null,
            'correo'     => $_POST['correo'] ?? '',
            'roles'      => $_POST['roles'] ?? [],
        ];

        $errores = $objUsuario->validarDatos($datos, $datos['correo']);
        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $erroresRoles = $objUsuario->validarRoles($datos['roles']);
        if (!empty($erroresRoles)) {
            echo json_encode(['status' => 'warning', 'errores' => $erroresRoles]);
            exit;
        }

        if ($objUsuario->actualizarUsuario($datos)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'UPDATE', $idUsuario, 'usuario', null, $datos['nombres'] . ' ' . $datos['apellidos']);
            echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
        } else {
            $errores = $objUsuario->obtenerErrores();
            $mensaje = !empty($errores) ? implode(' ', $errores) : 'Error al actualizar el usuario.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
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
        $erroresPass = $objUsuario->validarContrasena($nuevaPass);
        if (!empty($erroresPass)) {
            echo json_encode(['status' => 'warning', 'errores' => ['nueva_contrasena' => $erroresPass['contrasena']]]);
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

}
