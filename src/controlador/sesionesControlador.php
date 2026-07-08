<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Sesiones;
use GrupoProyecto\SisBiomec\modelo\Grupo;
use GrupoProyecto\SisBiomec\modelo\Drills;        
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objSesiones = new Sesiones();
$id_entrenador_sesion = (int)$_SESSION['id']; 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarSesiones') {
        header('Content-Type: application/json');
        $id_grupo = !empty($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : null;
        $estado = !empty($_GET['estado']) ? $_GET['estado'] : null;
        
        echo json_encode($objSesiones->listarSesiones($id_grupo, $estado));
        exit;
    }

    if ($accion === 'obtenerDetalle') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        echo json_encode($objSesiones->obtenerDetalleSesion($id));
        exit;
    }

    if ($accion === 'listarGrupos') {
        header('Content-Type: application/json');
        try {
            $objGrupo = new Grupo();
            $grupos = $objGrupo->listarGrupos(1);
            
            if (!$grupos) {
                $grupos = [];
            }
            
            echo json_encode($grupos);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    if ($accion === 'listarEntrenadores') {
        header('Content-Type: application/json');
        try {
            $objEntrenador = new \GrupoProyecto\SisBiomec\modelo\Entrenador();
            $entrenadores = $objEntrenador->listarEntrenador();
            
            echo json_encode($entrenadores ?: []);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    if ($accion === 'listarMicrociclos') {
        header('Content-Type: application/json');
        echo json_encode($objSesiones->listarMicrociclos());
        exit;
    }

    if ($accion === 'listarDrillsActivos') {
        header('Content-Type: application/json');
        $objDrills = new Drills();
        echo json_encode($objDrills->listarDrills(['estado' => 'Activo']));
        exit;
    }

    require_once 'vista/sesiones.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? $_GET['accion'] ?? '';

    if ($accionPost === 'guardar') {
        Autorizacion::exigir('sesiones', 'crear');

        $errores = [];
        
        if (empty($_POST['id_entrenador'])) {
            $errores['id_entrenador'] = 'Debe seleccionar un entrenador.';
        }
        
        if (empty($_POST['id_grupo'])) {
            $errores['id_grupo'] = 'No se permitirán sesiones sin grupo asignado.';
        }

        if (empty($_POST['fecha'])) {
            $errores['fecha'] = 'La fecha de la sesión es de carácter obligatorio.';
        } else {
            $fechaSesion = strtotime($_POST['fecha']);
            $fechaHoy = strtotime(date('Y-m-d'));
            if ($fechaSesion < $fechaHoy) {
                $errores['fecha'] = 'No se permite crear sesiones para fechas pasadas.';
            }
        }

        if (empty($_POST['tipo_sesion'])) {
            $errores['tipo_sesion'] = 'El tipo de sesión es obligatorio.';
        }

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $series = isset($_POST['series']) ? json_decode($_POST['series'], true) : [];

        try {
            if ($objSesiones->registrarSesion($_POST, $series)) {
                Bitacora::registrar(
                    $id_entrenador_sesion, 'Modulo Sesiones', 'INSERT', null,
                    'sesiones', null, 'Planificada para grupo: ' . $_POST['id_grupo']
                );
                echo json_encode(['status' => 'success', 'message' => 'Sesión de entrenamiento planificada exitosamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error inesperado al procesar la planificación en la BD.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'ERROR: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($accionPost === 'editar') {
        Autorizacion::exigir('sesiones', 'editar');
        
        $id_sesion = (int)($_POST['id_sesion'] ?? 0);
        $errores = [];

        if (empty($_POST['id_entrenador'])) {
            $errores['id_entrenador'] = 'Debe seleccionar un entrenador.';
        }

        if (empty($_POST['id_grupo'])) {
            $errores['id_grupo'] = 'No se permitirán sesiones sin grupo asignado.';
        }

        if (empty($_POST['fecha'])) {
            $errores['fecha'] = 'La fecha es requerida.';
        } else {
            $fechaSesion = strtotime($_POST['fecha']);
            $fechaHoy = strtotime(date('Y-m-d'));
            if ($fechaSesion < $fechaHoy) {
                $errores['fecha'] = 'No se permite actualizar la sesión a una fecha pasada.';
            }
        }

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $series = isset($_POST['series']) ? json_decode($_POST['series'], true) : [];

        if ($objSesiones->editarSesion($id_sesion, $_POST, $series)) {
            Bitacora::registrar(
                $id_entrenador_sesion, 'Modulo Sesiones', 'UPDATE',
                $id_sesion, 'datos sesion', null, 'Modificación de planificación'
            );
            echo json_encode(['status' => 'success', 'message' => 'Planificación de la sesión modificada exitosamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error al intentar actualizar los datos en el servidor.']);
        }
        exit;
    }

    if ($accionPost === 'completarSesion') {
        Autorizacion::exigir('sesiones', 'editar');

        $id_sesion = (int)($_POST['id_sesion'] ?? 0);
        $volumen_ejecutado = isset($_POST['volumen_ejecutado']) ? (int)$_POST['volumen_ejecutado'] : null;
        $observaciones = $_POST['observaciones'] ?? '';

        $datosCierre = [
            'id_sesion'         => $id_sesion,
            'volumen_ejecutado' => $volumen_ejecutado,
            'observaciones'     => $observaciones,
            'estado'            => 'Completada'
        ];

        if ($objSesiones->completarSesion($datosCierre)) {
            Bitacora::registrar(
                $id_entrenador_sesion, 'Modulo Sesiones', 'UPDATE',
                $id_sesion, 'estado/ejecucion', null, 'Estado cambiado a Completada. Vol: ' . $volumen_ejecutado
            );
            echo json_encode(['status' => 'success', 'message' => 'La sesión de entrenamiento ha sido cerrada y guardada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar la ejecución de la sesión.']);
        }
        exit;
    }

    if ($accionPost === 'cancelarSesion') {
        Autorizacion::exigir('sesiones', 'eliminar');
        
        $id_sesion = (int)($_POST['id_sesion'] ?? 0);

        if ($objSesiones->cancelarSesion($id_sesion)) {
            Bitacora::registrar(
                $id_entrenador_sesion, 'Modulo Sesiones', 'DELETE_LOGIC',
                $id_sesion, 'estado', null, 'Cancelada'
            );
            echo json_encode(['status' => 'success', 'message' => 'La sesión ha sido cancelada con éxito de la planificación.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo aplicar la cancelación de la sesión elegida.']);
        }
        exit;
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    exit;
}