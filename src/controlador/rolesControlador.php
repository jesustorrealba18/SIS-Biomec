<?php

use GrupoProyecto\SisBiomec\modelo\RolesModelo;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objRoles = new RolesModelo();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarRoles') {
        header('Content-Type: application/json');
        echo json_encode($objRoles->listarRoles());
        exit;
    }

    if ($accion === 'obtenerRol' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $rol = $objRoles->obtenerPorId((int)$_GET['id']);
        $permisos = $objRoles->obtenerPermisosRol((int)$_GET['id']);
        echo json_encode(['rol' => $rol, 'permisos' => $permisos]);
        exit;
    }

    if ($accion === 'listarPermisos') {
        header('Content-Type: application/json');
        echo json_encode($objRoles->listarPermisosAgrupados());
        exit;
    }

    require_once 'vista/roles.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    Autorizacion::exigir('seguridad', 'roles');
    $accionPost = $_POST['accion'] ?? '';

    if ($accionPost === 'guardar') {
        $errores = $objRoles->validarDatos($_POST);
        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        if ($objRoles->crearRol($_POST)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'CREATE', null, 'rol', null, $_POST['nombre']);
            echo json_encode(['status' => 'success', 'message' => 'Rol creado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al crear el rol.']);
        }
        exit;
    }

    if ($accionPost === 'editar') {
        $errores = $objRoles->validarDatos($_POST);
        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        if ($objRoles->editarRol($_POST)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'UPDATE', (int)$_POST['id_rol'], 'rol', null, $_POST['nombre']);
            echo json_encode(['status' => 'success', 'message' => 'Rol actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el rol.']);
        }
        exit;
    }

    if ($accionPost === 'toggleEstado') {
        $id = (int)($_POST['id_rol'] ?? 0);
        $estado = $_POST['estado'] === '1';
        $accion = $estado ? 'Activar' : 'Desactivar';
        if ($objRoles->toggleActivo($id, $estado)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'UPDATE', $id, 'rol activo', $estado ? '0' : '1', (string)(int)$estado);
            echo json_encode(['status' => 'success', "message" => "Rol {$accion}do."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al cambiar estado.']);
        }
        exit;
    }

    if ($accionPost === 'guardarPermisos') {
        $idRol = (int)($_POST['id_rol'] ?? 0);
        $permisosIds = $_POST['permisos'] ?? [];

        if ($objRoles->actualizarPermisosRol($idRol, $permisosIds)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'UPDATE', $idRol, 'permisos rol', null, count($permisosIds) . ' permisos');
            echo json_encode(['status' => 'success', 'message' => 'Permisos actualizados correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar permisos.']);
        }
        exit;
    }

    if ($accionPost === 'eliminar') {
        $id = (int)($_POST['id_rol'] ?? 0);
        $rol = $objRoles->obtenerPorId($id);
        if (!$rol) {
            echo json_encode(['status' => 'error', 'message' => 'Rol no encontrado.']);
            exit;
        }
        if ($objRoles->eliminarRol($id)) {
            Bitacora::registrar($_SESSION['id'], 'Seguridad', 'DELETE', $id, 'rol', null, $rol['nombre']);
            echo json_encode(['status' => 'success', 'message' => 'Rol eliminado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el rol.']);
        }
        exit;
    }
}
