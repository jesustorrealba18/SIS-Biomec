<?php
// =====================================================================
// CONTROLADOR PIVOTE: CARGA Y BIENESTAR (RF-11)
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\CargaBienestar;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objCarga = new CargaBienestar();
$id_usuario = $_SESSION['id'];

// =====================================================================
// RUTAS GET: Lectura de datos y carga de vista
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // Listar historial de carga/bienestar de un atleta (para la tabla)
    if ($accion === 'listarHistorial') {
        header('Content-Type: application/json');
        $id_atleta = (int)($_GET['id_atleta'] ?? 0);
        if ($id_atleta <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de atleta inválido']);
            exit;
        }
        $historial = $objCarga->obtenerHistorialAtleta($id_atleta);
        echo json_encode($historial);
        exit;
    }

    // Obtener un evento específico para edición (carga el modal)
    if ($accion === 'obtenerEvento') {
        header('Content-Type: application/json');
        $id_evento = (int)($_GET['id_evento'] ?? 0);
        if ($id_evento <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de evento inválido']);
            exit;
        }
        $evento = $objCarga->obtenerPorId($id_evento);
        if (empty($evento)) {
            echo json_encode(['status' => 'error', 'message' => 'Evento no encontrado']);
            exit;
        }
        echo json_encode($evento);
        exit;
    }

    // Si no hay acción específica, cargar la vista principal
    require_once 'vista/cargaBienestar.php';
    exit;
}

// =====================================================================
// RUTAS POST: Acciones transaccionales (CRUD con auditoría)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? '';

    // ========================= REGISTRAR =========================
    if ($accionPost === 'registrar') {
        Autorizacion::exigir('cargaBienestar', 'registrar');

        // Intentar registrar
        if ($objCarga->registrarEvento($_POST)) {
            // Registrar en bitácora (se omiten datos sensibles)
            $datosRegistrados = $_POST;
            unset($datosRegistrados['accion']);
            Bitacora::registrar(
                $id_usuario,
                'CargaBienestar',
                'INSERT',
                0,
                'Nuevo registro',
                null,
                json_encode($datosRegistrados, JSON_UNESCAPED_UNICODE)
            );
            echo json_encode(['status' => 'success', 'message' => 'Evento registrado exitosamente']);
        } else {
            $errores = $objCarga->obtenerErrores();
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error del servidor al registrar el evento']);
            }
        }
        exit;
    }

    // ========================= EDITAR =========================
    if ($accionPost === 'editar') {
        Autorizacion::exigir('cargaBienestar', 'editar');

        // Validar justificación obligatoria (RF-11.3)
        $justificacion = trim($_POST['justificacion_cambio'] ?? '');
        if (empty($justificacion)) {
            echo json_encode(['status' => 'error', 'message' => 'La justificación del cambio es obligatoria']);
            exit;
        }

        // Intentar editar
        try {
            if ($objCarga->editarEvento($_POST)) {
                Bitacora::registrar(
                    $id_usuario,
                    'CargaBienestar',
                    'UPDATE',
                    (int)($_POST['id_evento'] ?? 0),
                    'RPE, calidad_sueño, fatiga, descripción',
                    'Registro anterior',
                    "Editado con justificación: $justificacion"
                );
                echo json_encode(['status' => 'success', 'message' => 'Evento actualizado correctamente']);
            } else {
                $errores = $objCarga->obtenerErrores();
                $mensaje = !empty($errores) ? reset($errores) : 'Error al actualizar el evento';
                echo json_encode(['status' => 'error', 'message' => $mensaje]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ========================= ANULAR (Soft Delete) =========================
    if ($accionPost === 'anular') {
        Autorizacion::exigir('cargaBienestar', 'eliminar');

        $id_evento = (int)($_POST['id_evento'] ?? 0);
        $justificacion = trim($_POST['justificacion_cambio'] ?? '');
        if (empty($justificacion)) {
            echo json_encode(['status' => 'error', 'message' => 'La justificación para anular es obligatoria']);
            exit;
        }

        try {
            if ($objCarga->anularEvento($id_evento, $justificacion)) {
                Bitacora::registrar(
                    $id_usuario,
                    'CargaBienestar',
                    'DELETE',
                    $id_evento,
                    'estado',
                    'Activo',
                    "Anulado con justificación: $justificacion"
                );
                echo json_encode(['status' => 'success', 'message' => 'Evento anulado correctamente']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo anular el registro']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Si la acción no es reconocida
    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    exit;
}