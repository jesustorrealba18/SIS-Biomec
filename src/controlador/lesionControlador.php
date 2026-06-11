<?php
// =====================================================================
// CONTROLADOR: CONTROL CLÍNICO DE LESIONES (RF-10)
// Protegido contra caídas en JMeter mediante Buffering (ob_start)
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\modelo\Lesion;
use GrupoProyecto\SisBiomec\modelo\Atleta;

$objLesion = new Lesion();
$id_usuario = $_SESSION['id'];

// =====================================================================
// RUTAS GET (Listados y Detalles)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // Selector de atletas para el formulario y filtros
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        echo json_encode((new Atleta())->listar());
        exit;
    }

    // Listado principal (Soporta modo activos e inactivos/papelera)
    if ($accion === 'listarLesiones') {
        header('Content-Type: application/json');
        $estadoClinico = trim($_GET['estado'] ?? '');
        $id_atleta = (int)($_GET['id_atleta'] ?? 0);
        $tipo = trim($_GET['tipo'] ?? '');
        $zona = trim($_GET['zona'] ?? '');
        
        // RECIBE EL MODO EXPLÍCITO DESDE JS
        $modo = $_GET['modo'] ?? 'activos';
        $incluirInactivos = ($modo === 'papelera');
        
        $lesiones = $objLesion->listarLesiones($estadoClinico, $id_atleta, $tipo, $zona, $incluirInactivos);
        echo json_encode($lesiones);
        exit;
    }

    // Detalle para edición o vista completa
    if ($accion === 'obtenerDetalleLesion') {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            echo json_encode($objLesion->obtenerDetallePorId($id));
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID de lesión inválido']);
        }
        exit;
    }

    // Cargar la vista HTML por defecto
    require_once 'vista/lesion.php';
    exit;
}

// =====================================================================
// RUTAS POST (Transacciones ACID)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    //$accion = $_POST['accion'] ?? '';

    $accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
    
    // Iniciamos el buffer de salida para proteger el JSON
    ob_start(); 

    // 1. REGISTRAR
    if ($accion === 'registrar') {
        Autorizacion::exigir('lesiones', 'registrar');
        $res = $objLesion->registrarLesion($_POST);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'INSERT', null, 'Nueva lesión registrada', null, json_encode($_POST));
            echo json_encode(['status' => 'success', 'message' => 'Informe clínico registrado con éxito.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al registrar la lesión.']);
        }
        exit;
    }

    // 2. ACTUALIZAR
    if ($accion === 'actualizar') {
        Autorizacion::exigir('lesiones', 'editar');
        $id = (int)($_POST['id_lesion'] ?? 0);
        
        if ($id <= 0) { 
            ob_end_clean(); 
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']); 
            exit; 
        }
        
        $res = $objLesion->actualizarLesion($_POST, $id);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'UPDATE', $id, 'Actualización de diagnóstico/estado', null, json_encode($_POST));
            echo json_encode(['status' => 'success', 'message' => 'Informe clínico actualizado.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al actualizar.']);
        }
        exit;
    }

    // 3. ELIMINADO LÓGICO (Mover a Papelera)
    if ($accion === 'anular') {
        // CORRECCIÓN: Se exige el permiso "eliminar" como está en tu Base de Datos
        Autorizacion::exigir('lesiones', 'eliminar');
        $id = (int)($_POST['id_lesion'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        
        if ($id <= 0 || strlen($motivo) < 5) { 
            ob_end_clean(); 
            echo json_encode(['status' => 'error', 'message' => 'Debe proveer un ID válido y un motivo justificado (min. 10 caracteres).']); 
            exit; 
        }
        
        $res = $objLesion->eliminarLesionLogicamente($id, $motivo);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'SOFT_DELETE', $id, 'Movido a papelera', null, "Motivo: $motivo");
            echo json_encode(['status' => 'success', 'message' => 'Registro movido a la papelera.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al anular.']);
        }
        exit;
    }

    // 4. REACTIVAR (Sacar de la Papelera)
    if ($accion === 'reactivar') {
        Autorizacion::exigir('lesiones', 'reactivar');
        $id = (int)($_POST['id_lesion'] ?? 0);
        
        if ($id <= 0) { 
            ob_end_clean(); 
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']); 
            exit; 
        }
        
        $res = $objLesion->reactivarLesion($id);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'REACTIVATE', $id, 'Restaurado desde papelera', null, null);
            echo json_encode(['status' => 'success', 'message' => 'Lesión reactivada exitosamente.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al reactivar el registro.']);
        }
        exit;
    }

    // 5. ELIMINACIÓN FÍSICA PERMANENTE (Solo permitida si ya estaba en papelera)
    if ($accion === 'eliminardb') {
        Autorizacion::exigir('lesiones', 'eliminardb');
        $id = (int)($_POST['id_lesion'] ?? 0);
        
        if ($id <= 0) { 
            ob_end_clean(); 
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']); 
            exit; 
        }
        
        $res = $objLesion->eliminarfisico($id);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'DELETE_PHYSICAL', $id, 'Eliminación física en base de datos', null, 'Registro borrado permanentemente');
            echo json_encode(['status' => 'success', 'message' => 'Registro eliminado físicamente del sistema.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error: No se pudo eliminar físicamente.']);
        }
        exit;
    }

    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Acción POST no soportada.']);
    exit;
}