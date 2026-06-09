<?php
// =====================================================================
// CONTROLADOR PIVOTE: MANTENIMIENTO Y RESPALDOS
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Mantenimiento;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

// =====================================================================
// RUTAS POST: Procesamiento de Backup y Restore
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionPost = $_GET['accion'] ?? '';
    $id_usuario = $_SESSION['id'];

    // -----------------------------------------------------------------
    // ACCIÓN: GENERAR RESPALDO (BACKUP)
    // -----------------------------------------------------------------
    if ($accionPost === 'backup') {
        Autorizacion::exigir('seguridad', 'mantenimiento');

        try {
            $objMantenimiento = new Mantenimiento();
            $archivoGenerado = $objMantenimiento->generarRespaldo();

            if ($archivoGenerado) {
                // Registrar en bitácora (Usamos EXPORT que está en tu ENUM)
                Bitacora::registrar($id_usuario, 'Mantenimiento', 'EXPORT', null, 'Base de Datos', null, 'Backup generado: ' . basename($archivoGenerado));
                
                echo json_encode([
                    'status' => 'success', 
                    // Se asume que tienes una carpeta pública para descargas temporales
                    'url_descarga' => 'assets/backups/' . basename($archivoGenerado) 
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo generar el archivo de respaldo.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // -----------------------------------------------------------------
    // ACCIÓN: RESTAURAR SISTEMA (RESTORE)
    // -----------------------------------------------------------------
    if ($accionPost === 'restore') {
        Autorizacion::exigir('seguridad', 'mantenimiento');

        if (!isset($_FILES['archivo_respaldo']) || $_FILES['archivo_respaldo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió ningún archivo válido.']);
            exit;
        }

        $archivoTmp = $_FILES['archivo_respaldo']['tmp_name'];
        $nombreOriginal = $_FILES['archivo_respaldo']['name'];

        try {
            $objMantenimiento = new Mantenimiento();
            $restaurado = $objMantenimiento->restaurarSistema($archivoTmp);

            if ($restaurado) {
                // Registrar esta acción crítica
                Bitacora::registrar($id_usuario, 'Mantenimiento', 'RESTORE', null, 'Base de Datos', 'Estado Anterior', 'Restauración ejecutada con: ' . $nombreOriginal);
                
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Falló la ejecución del script SQL.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// =====================================================================
// RUTAS GET: Cargar la interfaz visual
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

$accionGet = $_GET['accion'] ?? '';

    if ($accionGet === 'info_respaldo') {
        try {
            $objMantenimiento = new Mantenimiento();
            $ultimaFecha = $objMantenimiento->obtenerUltimoRespaldo();
            
            echo json_encode([
                'status' => 'success', 
                'fecha' => $ultimaFecha
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }


    require_once 'vista/mantenimiento.php';
    exit;
}