<?php
use GrupoProyecto\SisBiomec\modelo\Marca;

$objMarca = new Marca();

if (isset($_GET['accion']) && $_GET['accion'] === 'get_telemetria') {
    header('Content-Type: application/json');
    $datos = $objMarca->obtenerTelemetriaActual();
    
    if ($datos) {
        echo json_encode([
            'status' => 'success', 
            'data' => $datos,
            'server_time' => round(microtime(true) * 1000) // <-- Inyección del reloj maestro
        ]);
    } else {
        echo json_encode(['status' => 'empty', 'message' => 'No hay carreras activas.']);
    }
    exit;
}

require_once 'vista/live.php';