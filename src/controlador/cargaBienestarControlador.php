<?php
// =====================================================================
// CONTROLADOR: CARGA DE BIENESTAR (RPE)
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\CargaBienestar;
use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objCarga = new CargaBienestar();

// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // Buscador predictivo de atletas (para formularios)
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->listar());
        exit;
    }

    // Listar registros de carga (para DataTable)
    if ($accion === 'listarCargas') {
        header('Content-Type: application/json');
        $estado       = $_GET['estado']       ?? 'Activo';
        $id_atleta    = !empty($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : null;
        $fecha_desde  = $_GET['fecha_desde'] ?? null;
        $fecha_hasta  = $_GET['fecha_hasta'] ?? null;

        $registros = $objCarga->listar($estado, $id_atleta, $fecha_desde, $fecha_hasta);
        echo json_encode($registros);
        exit;
    }

    // Obtener detalle completo de un registro (para edición o visualización)
    if ($accion === 'obtenerDetalleCarga') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $detalle = $objCarga->obtenerPorId($id);
        echo json_encode($detalle);
        exit;
    }

    // Si no hay acción específica, cargar la vista principal
    require_once 'vista/cargaBienestar.php';
    exit;
}

// =====================================================================
// RUTAS POST: Para Guardar, Actualizar, Anular o Reactivar
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? '';
    $id_usuario = $_SESSION['id'] ?? 0;

    // -----------------------------------------------------------------
    // Guardar (insertar o actualizar)
    // -----------------------------------------------------------------
    if ($accionPost === 'guardar') {
        Autorizacion::exigir('carga_bienestar', 'registrar');

        $resultado = $objCarga->guardar($_POST);

        if ($resultado['exito']) {
            // Registrar en bitácora según sea inserción o actualización
            $esNuevo = empty($_POST['id_rpe']);
            $id_registro = $resultado['id'];

            if ($esNuevo) {
                $datosGuardados = $_POST;
                unset($datosGuardados['accion']);
                Bitacora::registrar(
                    $id_usuario,
                    'CargaBienestar',
                    'CREATE',
                    $id_registro,
                    'Registro RPE',
                    null,
                    json_encode($datosGuardados, JSON_UNESCAPED_UNICODE)
                );
            } else {
                $datosActualizados = $_POST;
                unset($datosActualizados['accion'], $datosActualizados['id_rpe']);
                Bitacora::registrar(
                    $id_usuario,
                    'CargaBienestar',
                    'UPDATE',
                    $id_registro,
                    'Actualización RPE',
                    null,
                    json_encode($datosActualizados, JSON_UNESCAPED_UNICODE)
                );
            }

            echo json_encode(['status' => 'success', 'message' => $resultado['mensaje'], 'id' => $id_registro]);
        } else {
            // Si hay errores de validación
            if (isset($resultado['errores'])) {
                echo json_encode(['status' => 'warning', 'errores' => $resultado['errores']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
            }
        }
        exit;
    }

    // -----------------------------------------------------------------
    // Anular (soft delete)
    // -----------------------------------------------------------------
    if ($accionPost === 'anular') {
        Autorizacion::exigir('carga_bienestar', 'anular');

        $id_rpe   = (int)($_POST['id_rpe'] ?? 0);
        $motivo   = trim($_POST['motivo'] ?? '');
        $id_usuario = $_SESSION['id'] ?? 0;

        $resultado = $objCarga->anularRegistro($id_rpe, $motivo, $id_usuario);

        if ($resultado['exito']) {
            Bitacora::registrar(
                $id_usuario,
                'CargaBienestar',
                'DELETE',
                $id_rpe,
                'estado',
                'Activo',
                "Anulado. Motivo: $motivo"
            );
            echo json_encode(['status' => 'success', 'message' => $resultado['mensaje']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
        }
        exit;
    }

    // -----------------------------------------------------------------
    // Reactivar (cambiar de Anulado a Activo)
    // -----------------------------------------------------------------
    if ($accionPost === 'reactivar') {
        Autorizacion::exigir('carga_bienestar', 'registrar');

        $id_rpe = (int)($_POST['id_rpe'] ?? 0);
        $resultado = $objCarga->reactivarRegistro($id_rpe, $_SESSION['id']);

        if ($resultado['exito']) {
            Bitacora::registrar(
                $_SESSION['id'],
                'CargaBienestar',
                'RESTORE',
                $id_rpe,
                'estado',
                'Anulado',
                'Activo'
            );
            echo json_encode(['status' => 'success', 'message' => $resultado['mensaje']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
        }
        exit;
    }

    // Si ninguna acción coincide
    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    exit;
}