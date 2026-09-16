<?php
if (empty($_SESSION['id'])) { header('Location: ?p=login'); exit; }

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Sesiones;
use GrupoProyecto\SisBiomec\modelo\Grupo;
use GrupoProyecto\SisBiomec\modelo\Drills;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objSesiones = new Sesiones();
$id_entrenador_sesion = (int)$_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    $accionesGet = [
        'listarSesiones' => function() use ($objSesiones) {
            $id_grupo = !empty($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : null;
            $estado = !empty($_GET['estado']) ? $_GET['estado'] : null;
            return ['status' => 'success', 'data' => $objSesiones->listarSesiones($id_grupo, $estado)];
        },
        'obtenerDetalle' => function() use ($objSesiones) {
            return $objSesiones->obtenerDetalleSesion((int)$_GET['id']);
        },
        'listarGrupos' => function() {
            try {
                $objGrupo = new Grupo();
                return $objGrupo->listarGrupos(1) ?: [];
            } catch (Exception $e) {
                return [];
            }
        },
        'listarEntrenadores' => function() {
            try {
                $objEntrenador = new \GrupoProyecto\SisBiomec\modelo\Entrenador();
                return $objEntrenador->listarEntrenador() ?: [];
            } catch (Exception $e) {
                return [];
            }
        },
        'listarMicrociclos' => function() use ($objSesiones) {
            return $objSesiones->listarMicrociclos();
        },
        'listarDrillsActivos' => function() {
            $objDrills = new Drills();
            return $objDrills->listarDrills(['estado' => 'Activo']);
        },
    ];

    if (isset($accionesGet[$accion])) {
        header('Content-Type: application/json');
        echo json_encode($accionesGet[$accion]());
        exit;
    }

    require_once 'vista/sesiones.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

    $funcionesPost = [
        'guardar' => function() use ($objSesiones, $id_entrenador_sesion) {
            Autorizacion::exigir('sesiones', 'crear');

            $objSesiones->setDatos($_POST);

            $series = isset($_POST['series']) ? json_decode($_POST['series'], true) : [];
            $objSesiones->setSeries(is_array($series) ? $series : []);

            $errores = $objSesiones->validarDatos();
            if ($errores) {
                return ['status' => 'warning', 'errores' => $errores];
            }

            if ($objSesiones->registrarSesion()) {
                Bitacora::registrar($id_entrenador_sesion, 'Modulo Sesiones', 'INSERT', null, 'sesiones', null, 'Planificada para grupo: ' . $_POST['id_grupo']);
                return ['status' => 'success', 'message' => 'Sesión planificada exitosamente.'];
            }
            return ['status' => 'error', 'message' => 'Error al planificar la sesión.'];
        },
        'editar' => function() use ($objSesiones, $id_entrenador_sesion) {
            Autorizacion::exigir('sesiones', 'editar');

            $id_sesion = (int)($_POST['id_sesion'] ?? 0);
            if ($id_sesion <= 0) {
                return ['status' => 'error', 'message' => 'ID de sesión no proporcionado.'];
            }

            $objSesiones->setDatos($_POST);

            $series = isset($_POST['series']) ? json_decode($_POST['series'], true) : [];
            $objSesiones->setSeries(is_array($series) ? $series : []);

            $errores = $objSesiones->validarDatos($id_sesion);
            if ($errores) {
                return ['status' => 'warning', 'errores' => $errores];
            }

            if ($objSesiones->editarSesion()) {
                Bitacora::registrar($id_entrenador_sesion, 'Modulo Sesiones', 'UPDATE', $id_sesion, 'datos sesion', null, 'Modificación de planificación');
                return ['status' => 'success', 'message' => 'Sesión modificada exitosamente.'];
            }
            return ['status' => 'error', 'message' => 'Error al modificar la sesión.'];
        },
        'iniciarSesion' => function() use ($objSesiones, $id_entrenador_sesion) {
            Autorizacion::exigir('sesiones', 'editar');

            $objSesiones->setDatos($_POST);
            if ($objSesiones->InicializarSesion()) {
                $id_sesion = $objSesiones->getCampo('id_sesion');
                Bitacora::registrar($id_entrenador_sesion, 'Modulo Sesiones', 'UPDATE', $id_sesion, 'estado', 'Planificada', 'Parcial');
                return ['status' => 'success', 'message' => 'Sesión iniciada.'];
            }
            return ['status' => 'error', 'message' => 'Error al iniciar sesión.'];
        },
        'completarSesion' => function() use ($objSesiones, $id_entrenador_sesion) {
            Autorizacion::exigir('sesiones', 'editar');

            $objSesiones->setDatos($_POST);

            if ($objSesiones->completarSesion()) {
                $id_sesion = (int)($_POST['id_sesion'] ?? 0);
                Bitacora::registrar($id_entrenador_sesion, 'Modulo Sesiones', 'UPDATE', $id_sesion, 'estado/ejecucion', null, 'Estado cambiado a Completada');
                return ['status' => 'success', 'message' => 'Sesión completada exitosamente.'];
            }
            return ['status' => 'error', 'message' => 'Error al completar la sesión.'];
        },
        'cancelarSesion' => function() use ($objSesiones, $id_entrenador_sesion) {
            Autorizacion::exigir('sesiones', 'eliminar');

            $objSesiones->setDatos($_POST);

            if ($objSesiones->cancelarSesion()) {
                $id_sesion = (int)($_POST['id_sesion'] ?? 0);
                Bitacora::registrar($id_entrenador_sesion, 'Modulo Sesiones', 'DELETE_LOGIC', $id_sesion, 'estado', null, 'Cancelada');
                return ['status' => 'success', 'message' => 'Sesión cancelada exitosamente.'];
            }
            return ['status' => 'error', 'message' => 'Error al cancelar la sesión.'];
        },
    ];

    if (isset($funcionesPost[$accion])) {
        try {
            echo json_encode($funcionesPost[$accion]());
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'ERROR: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    exit;
}