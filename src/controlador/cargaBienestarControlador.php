<?php
// =====================================================================
// CONTROLADOR: CARGA INTERNA Y BIENESTAR (RPE)
// Protegido contra caídas en JMeter mediante Buffering (ob_start)
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\modelo\CargaBienestar;
use GrupoProyecto\SisBiomec\modelo\Atleta;

$objCarga = new CargaBienestar();
$id_usuario = $_SESSION['id'];

// =====================================================================
// RUTAS GET (Listados, detalles y utilerías)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // Selector de atletas para combos
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        echo json_encode((new Atleta())->listar());
        exit;
    }

    // Listado principal (activos / papelera)
    if ($accion === 'listarRPE') {
        header('Content-Type: application/json');
        $fechaInicio = trim($_GET['fechaInicio'] ?? '');
        $fechaFin    = trim($_GET['fechaFin'] ?? '');
        $id_atleta   = (int)($_GET['id_atleta'] ?? 0);
        $modo        = $_GET['modo'] ?? 'activos';   // 'activos' o 'papelera'
        $modoPapelera = ($modo === 'papelera');
        
        $registros = $objCarga->listarRPE($fechaInicio, $fechaFin, $id_atleta, $modoPapelera);
        echo json_encode($registros);
        exit;
    }

    // Obtener un registro para edición o detalle
    if ($accion === 'obtenerRPE') {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            echo json_encode($objCarga->obtenerRPEPorId($id));
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID de registro inválido']);
        }
        exit;
    }

    // Promedio RPE últimos 3 días (para cruce con lesiones)
    if ($accion === 'rpePromedioReciente') {
        header('Content-Type: application/json');
        $id_atleta = (int)($_GET['id_atleta'] ?? 0);
        $dias = (int)($_GET['dias'] ?? 3);
        if ($id_atleta > 0) {
            $promedio = $objCarga->obtenerRpePromedioUltimosDias($id_atleta, $dias);
            echo json_encode(['promedio' => $promedio]);
        } else {
            echo json_encode(['promedio' => null, 'error' => 'ID de atleta requerido']);
        }
        exit;
    }

    // Listar inconsistencias biológicas (RPE=1 y récord personal)
    if ($accion === 'listarInconsistencias') {
        header('Content-Type: application/json');
        echo json_encode($objCarga->listarInconsistencias());
        exit;
    }

    if ($accion === 'anularPorInconsistencia') {
    Autorizacion::exigir('rpe', 'eliminar');
    $id_rpe = (int)($_POST['id_rpe'] ?? 0);
    $motivo = "Inconsistencia biológica detectada: RPE (Reposo) incongruente con marcas de rendimiento (Récord) registradas este día.";
    if ($id_rpe <= 0) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }
    $res = $objCarga->anularRPE($id_rpe, $motivo);
    ob_end_clean();
    if ($res) {
        Bitacora::registrar($id_usuario, 'RegistroRPE', 'SOFT_DELETE', $id_rpe, 'Anulado por inconsistencia biológica', null, $motivo);
        echo json_encode(['status' => 'success', 'message' => 'Registro anulado automáticamente.']);
    } else {
        $err = $objCarga->obtenerErrores();
        echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al anular.']);
    }
    exit;
}

    if ($accion === 'listarRecomendacionesEntrenador') {
        header('Content-Type: application/json');
        $id_usuario = $_SESSION['id'] ?? 0;
        if ($id_usuario <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit;
        }
        $recomendaciones = $objCarga->obtenerRecomendacionesPorEntrenador($id_usuario);
        echo json_encode($recomendaciones);
        exit;
    }

    // Cargar la vista HTML por defecto
    require_once 'vista/cargaBienestar.php';
    exit;
}

// =====================================================================
// RUTAS POST (Transacciones ACID)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
    
    ob_start(); // Buffer para proteger JSON

    // 1. REGISTRAR
    if ($accion === 'registrar') {
        Autorizacion::exigir('rpe', 'registrar');
        $res = $objCarga->registrarRPE($_POST);
        ob_end_clean();
        
        if ($res && is_array($res) && $res['exito']) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'INSERT', $res['id_rpe'], 'Nuevo registro RPE', null, json_encode($_POST));
            echo json_encode(['status' => 'success', 'message' => 'Registro RPE guardado con éxito.']);
        } else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al guardar el registro.']);
        }
        exit;
    }

    // 2. ACTUALIZAR
    if ($accion === 'actualizar') {
        Autorizacion::exigir('rpe', 'editar');
        $id = (int)($_POST['id_rpe'] ?? 0);
        if ($id <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit;
        }
        $res = $objCarga->actualizarRPE($_POST, $id);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'UPDATE', $id, 'Actualización de registro RPE', null, json_encode($_POST));
            echo json_encode(['status' => 'success', 'message' => 'Registro RPE actualizado.']);
        } else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al actualizar.']);
        }
        exit;
    }

    // 3. ANULAR (Soft delete -> papelera)
    if ($accion === 'anularRPE') {
        Autorizacion::exigir('rpe', 'eliminar');
        $id = (int)($_POST['id_rpe'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        if ($id <= 0 || strlen($motivo) < 5) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Debe proveer un ID válido y un motivo justificado (mínimo 10 caracteres).']);
            exit;
        }
        $res = $objCarga->anularRPE($id, $motivo);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'SOFT_DELETE', $id, 'Movido a papelera', null, "Motivo: $motivo");
            echo json_encode(['status' => 'success', 'message' => 'Registro movido a la papelera.']);
        } else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al anular.']);
        }
        exit;
    }

    // 4. REACTIVAR
    if ($accion === 'reactivarRPE') {
        Autorizacion::exigir('rpe', 'reactivar');
        $id = (int)($_POST['id_rpe'] ?? 0);
        if ($id <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit;
        }
        $res = $objCarga->reactivarRPE($id);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'REACTIVATE', $id, 'Restaurado desde papelera', null, null);
            echo json_encode(['status' => 'success', 'message' => 'Registro reactivado exitosamente.']);
        } else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al reactivar.']);
        }
        exit;
    }

    // 5. ELIMINACIÓN FÍSICA PERMANENTE (solo si ya estaba en papelera)
    if ($accion === 'eliminarFisicoRPE') {
        Autorizacion::exigir('rpe', 'eliminardb');
        $id = (int)($_POST['id_rpe'] ?? 0);
        if ($id <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit;
        }
        $res = $objCarga->eliminarFisicoRPE($id);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'DELETE_PHYSICAL', $id, 'Eliminación física', null, 'Registro borrado permanentemente');
            echo json_encode(['status' => 'success', 'message' => 'Registro eliminado físicamente del sistema.']);
        } else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error: No se pudo eliminar físicamente.']);
        }
        exit;
    }

    // 6. MARCAR RECOMENDACIÓN COMO LEÍDA
if ($accion === 'marcarRecomendacionLeida') {
    $id_recomendacion = (int)($_POST['id_recomendacion'] ?? 0);
    if ($id_recomendacion <= 0) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'ID de recomendación inválido']);
        exit;
    }
    
    $res = $objCarga->marcarRecomendacionLeida($id_recomendacion);
    ob_end_clean();
    
    if ($res) {
        echo json_encode(['status' => 'success', 'message' => 'Recomendación marcada como leída']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar']);
    }
    exit;
}

    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Acción POST no soportada.']);
    exit;
}