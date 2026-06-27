<?php

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
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

    // Cargar la lista de atletas convocados a una sesión
    if ($accion === 'cargar_atletas') {
        header('Content-Type: application/json');
        
        $id_sesion = $_GET['id_sesion'] ?? null;
        
        if (!$id_sesion) {
            echo json_encode(['status' => 'warning', 'message' => 'No se seleccionó una sesión válida.']);
            exit;
        }

        // Llamamos al modelo para listar
        $lista = $objAsistencia->obtenerAtletasPorSesion((int)$id_sesion);
        echo json_encode(['status' => 'success', 'data' => $lista]);
        exit;
    }

    if ($accion === 'listar_sesiones_activas') {
        header('Content-Type: application/json');
        
        $sesiones = $objAsistencia->obtenerSesionesActivas();
        echo json_encode(['status' => 'success', 'data' => $sesiones]);
        exit;
    }

    require_once 'vista/asistencia.php';
    exit;
}

// =====================================================================
// RUTAS POST: Para procesar inserciones y actualizaciones
// =====================================================================
/* if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capturamos la acción (soporta si viene por FormData o por URL en el fetch)
    $accionPost = $_POST['accion'] ?? $_GET['accion'] ?? '';

    // 1. Registro automático mediante Escáner QR
    if ($accionPost === 'registrar_por_qr') {
        ob_start();
        // Autorizacion::exigir('asistencia', 'registrar'); // Descomentar al integrar roles
        header('Content-Type: application/json');
        
        $token_qr = $_POST['token_qr'] ?? null;
        $id_sesion = $_POST['id_sesion'] ?? null;

        // Hidratamos la propiedad id_sesion
        $objAsistencia->setDatos($_POST);
        
       $resultado = $objAsistencia->RegistrarPorQR();

       if ($resultado['status_http'] === 'info') {
        ob_clean();
            echo json_encode(['status' => 'info', 'message' => $resultado['mensaje']]);
            exit;
        }
        
        if ($resultado['exito']) {
            Bitacora::registrar($id_usuario, 'Asistencia', 'INSERT', 0, 'asistencia_qr', '', "Escaneo QR exitoso: {$resultado['nombre_atleta']}");
            ob_clean();
            echo json_encode(['status' => 'success', 'nombre_atleta' => $resultado['nombre_atleta']]);
        } else {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
        }
        exit;
    }

    // 2. Registro manual (Botones: Presente, Faltó, Permiso)
    if ($accionPost === 'registrar_manual') {
        // Autorizacion::exigir('asistencia', 'registrar');
        header('Content-Type: application/json');

        // Hidratación limpia usando tu Trait
        $objAsistencia->setDatos($_POST);
        
        if ($objAsistencia->RegistrarManual()) {
            $estado = $_POST['estado_asistencia'] ?? 'Desconocido';
            $id_atleta = $_POST['id_atleta'] ?? 0;
            
            // Registro de auditoría para operaciones manuales
            Bitacora::registrar($id_usuario, 'Asistencias', 'UPSERT', $id_atleta, 'estado', '', "Ajuste manual a: $estado");
            
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Fallo interno al guardar la asistencia.']);
        }
        exit;
    }
} */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capturamos la acción (soporta si viene por FormData o por URL en el fetch)
    $accionPost = $_POST['accion'] ?? $_GET['accion'] ?? '';

    // ==========================================================
    // 1. Registro automático mediante Escáner QR
    // ==========================================================

    if ($accionPost === 'registrar_por_qr') {
        ob_start(); 
        // Autorizacion::exigir('asistencia', 'registrar'); 
        header('Content-Type: application/json');
        
        $objAsistencia->setDatos($_POST);
        $resultado = $objAsistencia->RegistrarPorQR();

        ob_clean(); 

        // CORRECCIÓN: Se usa isset para evitar errores de PHP
        if (isset($resultado['status_http']) && $resultado['status_http'] === 'info') {
            echo json_encode(['status' => 'info', 'message' => $resultado['mensaje']]);
            exit;
        }
        
        if ($resultado['exito']) {
            Bitacora::registrar($id_usuario, 'Asistencia', 'INSERT', 0, 'asistencia_qr', '', "Escaneo QR exitoso: {$resultado['nombre_atleta']}");
            echo json_encode(['status' => 'success', 'nombre_atleta' => $resultado['nombre_atleta']]);
        } else {
            // Ahora sí mostrará el mensaje real: "Token de seguridad inválido" o "Falta sesión"
            echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
        }
        exit;
    }

  /*   if ($accionPost === 'registrar_por_qr') {
        ob_start(); // Iniciamos el buffer para proteger el JSON
        // Autorizacion::exigir('asistencia', 'registrar'); 
        header('Content-Type: application/json');
        
        // Hidratamos el modelo con los datos recibidos
        $objAsistencia->setDatos($_POST);
        
        $resultado = $objAsistencia->RegistrarPorQR();

        // Limpiamos cualquier basura en el buffer antes de imprimir JSON
        ob_clean(); 

        if ($resultado['status_http'] === 'info') {
            echo json_encode(['status' => 'info', 'message' => $resultado['mensaje']]);
            exit;
        }
        
        if ($resultado['exito']) {
            Bitacora::registrar($id_usuario, 'Asistencia', 'INSERT', 0, 'asistencia_qr', '', "Escaneo QR exitoso: {$resultado['nombre_atleta']}");
            echo json_encode(['status' => 'success', 'nombre_atleta' => $resultado['nombre_atleta']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
        }
        exit;
    } */

    // ==========================================================
    // 2. Registro manual (Botones: Presente, Faltó, Permiso)
    // ==========================================================
    if ($accionPost === 'registrar_manual') {
        ob_start(); // También lo usamos aquí por seguridad
        // Autorizacion::exigir('asistencia', 'registrar');
        header('Content-Type: application/json');

        // Hidratación limpia usando tu Trait
        $objAsistencia->setDatos($_POST);
        
        // Nota: Asegúrate de que en tu modelo Asistencia.php este método se llame RegistrarManual (antes lo llamamos guardar())
        if ($objAsistencia->RegistrarManual()) {
            $estado = $_POST['estado_asistencia'] ?? 'Desconocido';
            $id_atleta = (int)($_POST['id_atleta'] ?? 0); // Casteo a (int) por seguridad
            
            // Registro de auditoría para operaciones manuales
            Bitacora::registrar($id_usuario, 'Asistencias', 'INSERT', $id_atleta, 'estado', '', "Ajuste manual a: $estado");
            
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
        } else {
            // ¡LA MAGIA AQUÍ! Extraemos el error real del modelo
            $errores = $objAsistencia->obtenerErrores();
            $mensajeError = !empty($errores) ? reset($errores) : 'Fallo de integridad al procesar la asistencia.';
            
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => $mensajeError]);
        }
        exit;
    }
}