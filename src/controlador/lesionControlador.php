<?php

// =====================================================================
// CONTROLADOR PIVOTE: CONTROL CLÍNICO DE LESIONES (RF-10)
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Lesion;
use GrupoProyecto\SisBiomec\modelo\Atleta;

$objLesion = new Lesion();
$id_usuario = $_SESSION['id']; // ID del entrenador/médico en sesión para la Bitácora

// =====================================================================
// RUTAS GET: Para cargar vistas y solicitar datos (Listados JSON)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // El Buscador Predictivo o Select2 pide los atletas activos
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->listar());
        exit;
    }

    // Cargar el historial clínico filtrado por Atleta (Limpio para JMeter e IA)
    if ($accion === 'listarHistorial') {
        header('Content-Type: application/json');
        
        $id_atleta = !empty($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        
        if ($id_atleta <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de atleta inválido o no suministrado.']);
            exit;
        }

        $historial = $objLesion->obtenerHistorial($id_atleta);
        echo json_encode($historial);
        exit;
    }
}

// =====================================================================
// RUTAS POST: Acciones de Escritura y Modificación de Estado (ACID)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionPost = $_POST['accion'] ?? '';
    header('Content-Type: application/json');

    // Activar almacenamiento en búfer para prevenir que "warnings" accidentales rompan el JSON en JMeter
    ob_start();

    // 1. REGISTRAR NUEVA LESIÓN O EVENTO MÉDICO
    if ($accionPost === 'registrar') {
        $resultado = $objLesion->registrarLesion($_POST);

        if ($resultado && isset($resultado['exito'])) {
            // Limpiar búfer antes de responder con éxito
            ob_end_clean();
            
            // Auditoría obligatoria en Bitácora
            Bitacora::registrar(
                $id_usuario, 
                'Lesiones', 
                'INSERT', 
                $resultado['id_lesion'], 
                'Todos', 
                NULL, 
                "Registro clínico exitoso para el atleta ID: " . $_POST['id_atleta']
            );

            echo json_encode([
                'status' => 'success', 
                'message' => $resultado['mensaje'], 
                'id_lesion' => $resultado['id_lesion']
            ]);
        } else {
            // Si el modelo arroja fallas de validación interna (Trait)
            ob_end_clean();
            $errores = $objLesion->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'Error al guardar el informe clínico.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    // 2. ANULACIÓN CLÍNICA (Eliminado Lógico con Justificación Obligatoria)
    if ($accionPost === 'eliminar') {
        $id = (int)($_POST['id_lesion'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');

        // Validación preventiva en capa del controlador (Blindaje de Caja Negra)
        if (empty($motivo) || strlen($motivo) < 10) {
            ob_end_clean();
            echo json_encode([
                'status' => 'error', 
                'message' => 'La justificación de la anulación es obligatoria y debe tener al menos 10 caracteres.'
            ]);
            exit;
        }

        $resultadoAnulacion = $objLesion->anularLesion($id, $motivo);

        if ($resultadoAnulacion && isset($resultadoAnulacion['exito'])) {
            ob_end_clean();
            
            // Guardamos el movimiento en la bitácora con el motivo explícito
            Bitacora::registrar(
                $id_usuario, 
                'Lesiones', 
                'DELETE', 
                $id, 
                'estado', 
                'Activo', 
                "Anulado (Justificación: $motivo)"
            );

            echo json_encode([
                'status' => 'success', 
                'message' => $resultadoAnulacion['mensaje']
            ]);
        } else {
            ob_end_clean();
            $errores = $objLesion->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'No se pudo procesar la anulación del registro.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    // Si llega un POST con una acción desconocida
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Acción transaccional no soportada por el controlador.']);
    exit;
}