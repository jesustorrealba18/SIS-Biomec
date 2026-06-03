<?php
// =====================================================================
// CONTROLADOR PIVOTE: SEGUIMIENTO ANTROPOMÉTRICO (RF-05)
// =====================================================================
session_start();

// 1. Filtro de Seguridad estricto
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

// 2. Importamos los Modelos y Clases de Seguridad (PSR-4)
use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\MedicionAntropometrica;
use GrupoProyecto\SisBiomec\modelo\Atleta;

$objAntropometria = new MedicionAntropometrica();

// =====================================================================
// RUTAS GET: Carga de vistas, consultas y datos para gráficas
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // Ruta A: Listar atletas para el buscador predictivo del formulario
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->listar());
        exit;
    }

    // Ruta B: Cargar el Dashboard Principal (RF-05)
    if ($accion === 'cargarDashboard') {
        header('Content-Type: application/json');
        $datos = $objAntropometria->obtenerDashboardPrincipal();
        echo json_encode(['data' => $datos]);
        exit;
    }

    // Ruta C: Listar mediciones con filtros (similar a marcas)
    if ($accion === 'listarMediciones') {
        header('Content-Type: application/json');
        $id_atleta   = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin    = $_GET['fecha_fin'] ?? '';
        
        $mediciones = $objAntropometria->listarMediciones($id_atleta, $fecha_inicio, $fecha_fin);
        echo json_encode($mediciones);
        exit;
    }

    // Ruta D: Obtener detalle completo de una medición + historial evolutivo
    if ($accion === 'obtenerDetalleMedicion') {
        header('Content-Type: application/json');
        $id_medicion = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $detalle = $objAntropometria->obtenerDetallePorId($id_medicion);
        echo json_encode($detalle);
        exit;
    }

    // Por defecto: cargar la pantalla HTML
    require_once 'vista/antropometria.php';
    exit;
}

// =====================================================================
// RUTAS POST: Procesamiento de transacciones (Guardar, Editar, Eliminar)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? '';

    // -----------------------------------------------------------------
    // Ruta E: Guardar Nueva Medición (RF-05.1)
    // -----------------------------------------------------------------
    if ($accionPost === 'guardar') {
        // Mapeo de campos del formulario a los nombres esperados por el modelo
        $datos = [
            'id_atleta'              => $_POST['id_atleta'] ?? null,
            'fecha'                  => $_POST['fecha'] ?? null,
            'peso_kg'                => $_POST['peso'] ?? null,
            'talla_cm'               => $_POST['talla'] ?? null,
            'envergadura_cm'         => $_POST['envergadura'] ?? null,
            'perimetro_abdominal_cm' => $_POST['perimetro_abdominal'] ?? null,
            'porcentaje_grasa'       => $_POST['porcentaje_grasa'] ?? null,
            'responsable'            => $_SESSION['nombre'] ?? $_SESSION['id'] ?? 'Sistema'
        ];

        if ($objAntropometria->registrarMedicion($datos)) {
            // Auditoría: operación INSERT
           /* Bitacora::registrar(
                $_SESSION['id'],
     
                'Módulo Antropometría',
                'INSERT',
                0, // El ID de la medición aún no se conoce, se puede obtener con lastInsertId si se modifica el modelo
                'N/A',
                'N/A',
                "Nueva medición. Atleta ID: {$datos['id_atleta']}, Peso: {$datos['peso_kg']} kg, Talla: {$datos['talla_cm']} cm"
            );*/
            echo json_encode(['status' => 'success', 'message' => 'Evaluación registrada correctamente.']);
        } else {
            $errores = $objAntropometria->obtenerErroresValidacion();
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error de base de datos al guardar la medición.']);
            }
        }
        exit;
    }

    // -----------------------------------------------------------------
    // Ruta F: Editar/Corregir Registro (RF-05.3)
    // -----------------------------------------------------------------
    if ($accionPost === 'editar') {
        $id_medicion = (int)($_POST['id_medicion'] ?? 0);
        $justificacion = trim($_POST['justificacion'] ?? '');

        // Regla de negocio: toda modificación requiere justificación
        if (empty($justificacion)) {
            echo json_encode([
                'status' => 'error',
                'errores' => ['El campo <b>justificación</b> es obligatorio para editar un registro.']
            ]);
            exit;
        }

        // Mapeo de campos igual que en guardar
        $datos = [
            'id_atleta'              => $_POST['id_atleta'] ?? null,
            'fecha'                  => $_POST['fecha'] ?? null,
            'peso_kg'                => $_POST['peso'] ?? null,
            'talla_cm'               => $_POST['talla'] ?? null,
            'envergadura_cm'         => $_POST['envergadura'] ?? null,
            'perimetro_abdominal_cm' => $_POST['perimetro_abdominal'] ?? null,
            'porcentaje_grasa'       => $_POST['porcentaje_grasa'] ?? null,
            'responsable'            => $_SESSION['nombre'] ?? $_SESSION['id'] ?? 'Sistema'
        ];

        if ($objAntropometria->actualizarMedicion($datos, $id_medicion)) {
            Bitacora::registrar(
                $_SESSION['id'],
                'Módulo Antropometría',
                'UPDATE',
                $id_medicion,
                'peso_kg, talla_cm, envergadura_cm, perimetro_abdominal_cm, porcentaje_grasa',
                'Ver valores previos en auditoría',
                "Medición actualizada. Justificación: $justificacion. Nuevos datos: Peso {$datos['peso_kg']} kg, Talla {$datos['talla_cm']} cm"
            );
            echo json_encode(['status' => 'success', 'message' => 'El registro ha sido corregido exitosamente.']);
        } else {
            $errores = $objAntropometria->obtenerErroresValidacion();
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al intentar actualizar el registro.']);
            }
        }
        exit;
    }

    // -----------------------------------------------------------------
    // Ruta G: Eliminar físicamente una medición (Hard Delete)
    // -----------------------------------------------------------------
    if ($accionPost === 'eliminar') {
        $id_medicion = (int)($_POST['id_medicion'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Sin justificación');

        if ($id_medicion > 0 && $objAntropometria->eliminarMedicion($id_medicion)) {
            Bitacora::registrar(
                $_SESSION['id'],
                'Módulo Antropometría',
                'DELETE',
                $id_medicion,
                'registro completo',
                'Datos eliminados permanentemente',
                "Medición eliminada. Motivo: $motivo"
            );
            echo json_encode(['status' => 'success', 'message' => 'Medición eliminada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la medición.']);
        }
        exit;
    }
}