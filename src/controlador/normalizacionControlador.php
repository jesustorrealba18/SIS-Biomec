<?php

// =====================================================================
// CONTROLADOR PIVOTE: NORMALIZACIÓN DE TIEMPOS (RF-08)
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\NormalizacionTiempo;
use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objNormalizacion = new NormalizacionTiempo();
$id_usuario = $_SESSION['id'];

// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados / Selects)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // El Buscador Predictivo o Select pide los atletas activos
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->listar());
        exit;
    }

    // Cargar la tabla principal de registros de normalización
    if ($accion === 'listarNormalizaciones') {
        header('Content-Type: application/json');
        $estado = $_GET['estado'] ?? 'Activo';
        $id_atleta = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : null;
        
        // Llamada segura al modelo extractor
        $resultados = $objNormalizacion->listar($estado, $id_atleta);
        echo json_encode($resultados);
        exit;
    }
}

// =====================================================================
// RUTAS POST: Procesamiento de Formularios Transaccionales (ACID)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionPost = $_POST['accion'] ?? '';

    // -----------------------------------------------------------------
    // REGISTRAR Y CALCULAR NUEVA NORMALIZACIÓN
    // -----------------------------------------------------------------
    if ($accionPost === 'registrar') {
        // Activación táctica del buffer para blindar contra hilos de JMeter
        ob_start();
        header('Content-Type: application/json');
        
        // Control de Accesos mediante ACL del sistema
        Autorizacion::exigir('normalizacion_tiempos', 'registrar');

        // El modelo procesa la hidratación, validación y transaccionalidad ACID
        $respuesta = $objNormalizacion->guardarNormalizacion($_POST);

        if ($respuesta['status'] === 'success') {
            // Inyección imperativa en la Bitácora de Auditoría si el commit fue exitoso
            Bitacora::registrar(
                $id_usuario, 
                'NormalizacionTiempos', 
                'INSERT', 
                0, // ID autogenerado
                'tiempo_convertido_seg', 
                '0.00', 
                $respuesta['data_ia']['tiempo_convertido']
            );
            
            // Limpiamos cualquier warning residual antes de escupir el JSON puro
            ob_end_clean();
            echo json_encode([
                'status' => 'success',
                'message' => $respuesta['mensaje'],
                'data' => $respuesta['data_ia'] // Contrato de datos inmediato para el componente inteligente
            ]);
        } else {
            ob_end_clean();
            echo json_encode([
                'status' => 'error', 
                'message' => $respuesta['mensaje']
            ]);
        }
        exit;
    }

    // -----------------------------------------------------------------
    // ARCHIVAR (Borrado lógico con justificación exigido por el SRS)
    // -----------------------------------------------------------------
    if ($accionPost === 'eliminar') {
        ob_start();
        header('Content-Type: application/json');
        
        Autorizacion::exigir('normalizacion_tiempos', 'registrar');
        
        $id = (int)($_POST['id_normalizacion'] ?? 0);
        $motivo = $_POST['motivo'] ?? 'Sin justificación'; // Captura obligatoria de auditoría

        if ($objNormalizacion->eliminarNormalizacion($id, $motivo)) {
            Bitacora::registrar(
                $id_usuario, 
                'NormalizacionTiempos', 
                'DELETE', 
                $id, 
                'estado', 
                'Activo', 
                "Inactivo (Motivo: $motivo)"
            );
            ob_end_clean();
            echo json_encode(['status' => 'success']);
        } else {
            ob_end_clean();
            echo json_encode([
                'status' => 'error', 
                'message' => 'No se pudo archivar el registro de normalización.'
            ]);
        }
        exit;
    }
}