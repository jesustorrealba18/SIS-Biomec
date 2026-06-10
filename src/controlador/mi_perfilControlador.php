<?php

// =====================================================================
// CONTROLADOR PIVOTE: Mi Perfil
// =====================================================================


if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;


// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados)
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // El Buscador Predictivo pide los atletas
    if ($accion === 'obtener_mi_ficha') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        
        echo json_encode($objAtleta->obtenerDetallePorId());
        exit;
    }

     require_once 'vista/mi_perfil.php';
    exit;
}

// =====================================================================
// RUTAS POST: Para Guardar, Actualizar o Eliminar (Transacciones)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? '';

    $id_usuario = $_SESSION['id'] ?? 0;
    
    
}
