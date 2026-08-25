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
use GrupoProyecto\SisBiomec\modelo\UsuarioModelo;


// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados)
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // El Buscador Predictivo pide los atletas
    if ($accion === 'obtener_mi_ficha') {
        header('Content-Type: application/json');
        /* $objAtleta = new Atleta();
        
        echo json_encode($objAtleta->obtenerDetallePorIdUSER($_SESSION['id'])); */

        $objUsuario = new UsuarioModelo();
        
        echo json_encode($objUsuario->obtenerPerfilModular($_SESSION['id']));
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


    $accion = $_GET['accion'] ?? ($_POST['accion'] ?? '');

   
  // ==========================================================
    // INYECCIÓN SGRD: Guardar Preferencias (Modo Oscuro, Crono)
    // ==========================================================
    if ($accion === 'guardar_preferencia') {
        // 1. Leer el payload JSON nativo de Fetch API
        $jsonBody = file_get_contents('php://input');
        $datosPayload = json_decode($jsonBody, true);

        // 2. Inyectar el ID del usuario en sesión directamente al payload
        $datosPayload['id_usuario'] = $_SESSION['id'];

        // 3. Instanciar e Hidratar (Todo entra por el embudo seguro)
        $objUsuario = new UsuarioModelo();
        $objUsuario->setAtributos($datosPayload);

        // 4. Ejecutar la acción sin pasar ni un solo parámetro extra
        if ($objUsuario->guardarPreferencia()) {
            echo json_encode(['status' => 'success', 'message' => 'Preferencia guardada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Fallo al procesar la preferencia en BD.']);
        }
        exit;
    }


}
