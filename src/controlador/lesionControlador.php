<?php

// =====================================================================
// CONTROLADOR PIVOTE: CONTROL CLÍNICO DE LESIONES (RF-10)
// REFORMADO SEGÚN PATRÓN DE MARCASCONTROLADOR.PHP
// =====================================================================

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\modelo\Lesion;
use GrupoProyecto\SisBiomec\modelo\Atleta;

$objLesion = new Lesion();
$id_usuario = $_SESSION['id']; // ID del usuario logueado

// =====================================================================
// RUTAS GET: Listados, selectores y detalles (JSON)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // 1. Buscador de atletas (para selects y filtros)
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->listar());
        exit;
    }

    // 2. Listado principal de lesiones con filtros (estado, atleta, tipo, gravedad)
    if ($accion === 'listarLesiones') {
        header('Content-Type: application/json');
        
        $estado    = $_GET['estado'] ?? 'Activo';
        $id_atleta = !empty($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        $tipo      = trim($_GET['tipo'] ?? '');
        $gravedad  = trim($_GET['gravedad'] ?? '');

        $lesiones = $objLesion->listarLesiones($estado, $id_atleta, $tipo, $gravedad);
        echo json_encode($lesiones);
        exit;
    }

    // 3. Detalle completo de una lesión (para edición y modal de ver + gráfica RPE)
    if ($accion === 'obtenerDetalleLesion') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de lesión inválido.']);
            exit;
        }
        $detalle = $objLesion->obtenerDetallePorId($id);
        echo json_encode($detalle);
        exit;
    }

    // Si no hay acción específica, cargar la vista principal
    require_once 'vista/lesion.php';
    exit;
}

// =====================================================================
// RUTAS POST: Operaciones de escritura (transacciones ACID)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? '';

    // Limpiar buffer por si hay warnings accidentales (para JMeter)
    ob_start();

    // -----------------------------------------------------------------
    // 1. REGISTRAR nueva lesión
    // -----------------------------------------------------------------
    if ($accionPost === 'registrar') {
        Autorizacion::exigir('lesiones', 'registrar');

        if ($objLesion->registrarLesion($_POST)) {
            // Éxito: limpiamos buffer y registramos en bitácora
            ob_end_clean();
            Bitacora::registrar(
                $id_usuario,
                'Lesiones',
                'INSERT',
                null, // el modelo podría devolver el ID; lo podemos obtener del objeto
                'Registro completo',
                null,
                json_encode($_POST, JSON_UNESCAPED_UNICODE)
            );
            echo json_encode(['status' => 'success', 'message' => 'Lesión registrada correctamente.']);
        } else {
            ob_end_clean();
            $errores = $objLesion->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'Error al registrar la lesión.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    // -----------------------------------------------------------------
    // 2. ACTUALIZAR lesión existente (edición completa)
    // -----------------------------------------------------------------
    if ($accionPost === 'actualizar') {
        Autorizacion::exigir('lesiones', 'editar');

        $id_lesion = (int)($_POST['id_lesion'] ?? 0);
        if ($id_lesion <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID de lesión no válido para actualización.']);
            exit;
        }

        if ($objLesion->actualizarLesion($_POST, $id_lesion)) {
            ob_end_clean();
            Bitacora::registrar(
                $id_usuario,
                'Lesiones',
                'UPDATE',
                $id_lesion,
                'Datos de lesión',
                'Ver registro previo',
                json_encode($_POST, JSON_UNESCAPED_UNICODE)
            );
            echo json_encode(['status' => 'success', 'message' => 'Lesión actualizada correctamente.']);
        } else {
            ob_end_clean();
            $errores = $objLesion->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'Error al actualizar la lesión.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    // -----------------------------------------------------------------
    // 3. ANULAR (baja lógica) con justificación obligatoria
    // -----------------------------------------------------------------
    if ($accionPost === 'anular') {
        Autorizacion::exigir('lesiones', 'anular');

        $id_lesion = (int)($_POST['id_lesion'] ?? 0);
        $motivo    = trim($_POST['motivo'] ?? '');

        if ($id_lesion <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
            exit;
        }
        if (strlen($motivo) < 10) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'La justificación debe tener al menos 10 caracteres.']);
            exit;
        }

        if ($objLesion->anularLesion($id_lesion, $motivo)) {
            ob_end_clean();
            Bitacora::registrar(
                $id_usuario,
                'Lesiones',
                'DELETE', // o 'ANULAR' si tu ENUM lo permite; se puede ajustar
                $id_lesion,
                'estado',
                'Activo',
                "Anulado (Motivo: $motivo)"
            );
            echo json_encode(['status' => 'success', 'message' => 'Lesión anulada correctamente.']);
        } else {
            ob_end_clean();
            $errores = $objLesion->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'No se pudo anular la lesión.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    // -----------------------------------------------------------------
    // 4. ELIMINAR FÍSICO (solo para registros ya anulados, con doble permiso)
    // -----------------------------------------------------------------
    if ($accionPost === 'eliminarFisico') {
        // Se requiere un permiso especial (por ejemplo, 'eliminar_fisico')
        Autorizacion::exigir('lesiones', 'eliminar_fisico');

        $id_lesion = (int)($_POST['id_lesion'] ?? 0);
        if ($id_lesion <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
            exit;
        }

        if ($objLesion->eliminarFisico($id_lesion)) {
            ob_end_clean();
            Bitacora::registrar(
                $id_usuario,
                'Lesiones',
                'DELETE_PHYSICAL', // o como manejes eliminación física
                $id_lesion,
                'Registro completo',
                'Datos previos eliminados',
                'Eliminación física permanente'
            );
            echo json_encode(['status' => 'success', 'message' => 'Registro eliminado físicamente.']);
        } else {
            ob_end_clean();
            $errores = $objLesion->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'No se pudo eliminar el registro (puede que no esté anulado).';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    // -----------------------------------------------------------------
    // Acción POST no reconocida
    // -----------------------------------------------------------------
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Acción no soportada por el controlador de lesiones.']);
    exit;
}