<?php
use GrupoProyecto\SisBiomec\modelo\Marca;

$objMarca = new Marca();

if (isset($_GET['accion']) && $_GET['accion'] === 'get_telemetria') {
    header('Content-Type: application/json');
    
    // Capturamos el canal (id_atleta) de la URL
    $id_atleta = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
    
    if ($id_atleta > 0) {
        $datos = $objMarca->obtenerTelemetriaActual($id_atleta);
        
        if ($datos) {
            echo json_encode([
                'status' => 'success', 
                'data' => $datos,
                'server_time' => round(microtime(true) * 1000)
            ]);
        } else {
            echo json_encode(['status' => 'empty', 'message' => 'Carrera finalizada o no disponible.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Falta el ID de la sala (Atleta).']);
    }
    exit;
}

if (isset($_GET['accion']) && $_GET['accion'] === 'get_lobby') {
    header('Content-Type: application/json');
    
    // Llamamos a la nueva función recolectora de basura y listado que creaste en Marca.php
    $salasActivas = $objMarca->obtenerLobbyActivo();
    
    echo json_encode([
        'status' => 'success',
        'salas' => $salasActivas
    ]);
    exit;
}

require_once 'vista/live.php';