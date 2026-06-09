<?php
// =====================================================================
// CONTROLADOR PIVOTE: BITÁCORA DE SEGURIDAD
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;

// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // RUTA API: Extraer los datos para la tabla
    if ($accion === 'listar') {
        
        // El pivote solo llama al modelo y responde a JavaScript
        $datosBitacora = Bitacora::listar();
        
        if ($datosBitacora !== false) {
            echo json_encode(['status' => 'success', 'data' => $datosBitacora]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al extraer los datos del modelo.']);
        }
        
        exit;
    }

    // Si no hay acción, cargamos la interfaz visual
    require_once 'vista/bitacora.php';
    exit;
}