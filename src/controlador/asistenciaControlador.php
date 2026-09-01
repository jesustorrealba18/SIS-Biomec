<?php

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Notificacion;
use GrupoProyecto\SisBiomec\modelo\Asistencia;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

// =====================================================================
// CONTROLADOR PIVOTE: CONTROL DE ASISTENCIAS (RF-03)
// =====================================================================

// 1. Candado de Seguridad Principal
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}



$objAsistencia = new Asistencia();
$id_usuario = $_SESSION['id'] ?? 0;

// =====================================================================
// RUTAS GET: Para cargar datos de solo lectura (Listados)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'cargar_atletas') {
        header('Content-Type: application/json');
        
        $id_sesion = $_GET['id_sesion'] ?? null;
        
        if (!$id_sesion) {
            echo json_encode(['status' => 'warning', 'message' => 'No se seleccionó una sesión válida.']);
            exit;
        }

        $lista = $objAsistencia->obtenerAtletasPorSesion((int)$id_sesion);
        echo json_encode(['status' => 'success', 'data' => $lista]);
        exit;
    }

    if ($accion === 'listar_sesiones_activas') {
        header('Content-Type: application/json');
        
        $sesiones = $objAsistencia->obtenerSesionesActivas($id_usuario);
        echo json_encode(['status' => 'success', 'data' => $sesiones]);
        exit;
    }

    require_once 'vista/asistencia.php';
    exit;
}

// =====================================================================
// RUTAS POST: Para procesar inserciones y actualizaciones
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $accionPost = $_POST['accion'] ?? $_GET['accion'] ?? '';

    // ==========================================================
    // 1. Registro automático mediante Escáner QR
    // ==========================================================

    if ($accionPost === 'registrar_por_qr') {
        ob_start(); 
        // Autorizacion::exigir('asistencia', 'registrar'); 
        header('Content-Type: application/json');
        
        $data = $_POST;
        $data['id_usuario'] = $id_usuario;
        $objAsistencia->setDatos($data);
        $resultado = $objAsistencia->RegistrarPorQR();
        

        ob_clean(); 

        if (isset($resultado['status_http']) && $resultado['status_http'] === 'info') {
            echo json_encode(['status' => 'info', 'message' => $resultado['mensaje']]);
            exit;
        }
        
        if ($resultado['exito']) {
        $id_atleta = $objAsistencia->getCampo('id_atleta');
        Bitacora::registrar($id_usuario, 'Asistencia', 'INSERT', 0, 'asistencia_qr', '', "Escaneo QR exitoso: {$resultado['nombre_atleta']}");
        Notificacion::notificarAtletaYRepresentante(
            $id_atleta,
            "Asistencia Registrada", 
            "Se ha confirmado la entrada a la sesión de entrenamiento.", 
            "fa-check-circle", 
            "emerald" // Usamos verde (emerald)
            );
            echo json_encode(['status' => 'success', 'nombre_atleta' => $resultado['nombre_atleta']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
        }
        exit;
    }

 
    // ==========================================================
    // 2. Registro manual (Botones: Presente, Faltó, Permiso)
    // ==========================================================
    if ($accionPost === 'registrar_manual') {
        ob_start(); 
        // Autorizacion::exigir('asistencia', 'registrar');
        header('Content-Type: application/json');

        $data = $_POST;
        $data['id_usuario'] = $id_usuario;
        $objAsistencia->setDatos($data);
        
        if ($objAsistencia->RegistrarManual()) {
            $estado = $_POST['estado_asistencia'] ?? 'Desconocido';
            $id_atleta = (int)($_POST['id_atleta'] ?? 0); 
            
            Notificacion::notificarAtletaYRepresentante(
            $id_atleta,
            "Asistencia Registrada", 
            "Se ha confirmado la entrada a la sesión de entrenamiento.", 
            "fa-check-circle", 
            "emerald" // Usamos verde (emerald)
            );
            Bitacora::registrar($id_usuario, 'Asistencias', 'INSERT', $id_atleta, 'estado', '', "Ajuste manual a: $estado");
            
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
        } else {

            $errores = $objAsistencia->obtenerErrores();
            $mensajeError = !empty($errores) ? reset($errores) : 'Fallo de integridad al procesar la asistencia.';
            
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => $mensajeError]);
        }
        exit;
    }
}