<?php
// =====================================================================
// CONTROLADOR PIVOTE: SEGUIMIENTO ANTROPOMÉTRICO (RF-05)
// =====================================================================

// 1. Importamos los Modelos y Clases de Seguridad (PSR-4)
use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\MedicionAntropometrica;
use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;


// 2. Filtro de Seguridad estricto
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}



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

    /* // Ruta B: Cargar el Dashboard Principal (RF-05)
    if ($accion === 'cargarDashboard') {
        header('Content-Type: application/json');
        $datos = $objAntropometria->obtenerDashboardPrincipal();
        echo json_encode(['data' => $datos]);
        exit;
    } */

        // Ruta B: Cargar el Dashboard Principal (RF-05)
if ($accion === 'cargarDashboard') {
    header('Content-Type: application/json');
    $modo = $_GET['modo'] ?? 'activos';
    $id_atleta = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
    
    if ($modo === 'papelera') {
        // Mostrar todas las mediciones anuladas (detalle)
        $datos = $objAntropometria->listarMediciones($id_atleta, '', '', $modo);
    } else {
        // Mostrar resumen por atleta (última medición activa)
        $datos = $objAntropometria->obtenerDashboardPrincipal($id_atleta);
    }
    echo json_encode(['data' => $datos]);
    exit;
}

    // Ruta C: Listar mediciones con filtros (similar a marcas)
    if ($accion === 'listarMediciones') {
        header('Content-Type: application/json');
        $id_atleta    = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin    = $_GET['fecha_fin'] ?? '';
        
        $mediciones = $objAntropometria->listarMediciones($id_atleta, $fecha_inicio, $fecha_fin);
        echo json_encode($mediciones);
        exit;
    }

    // Ruta D: Modal de Gráficas e Historial
    if ($accion === 'verHistorial') {
        header('Content-Type: application/json');
        $id_atleta = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        
        // Obtenemos las mediciones (por defecto el modelo las trae DESC)
        $mediciones = $objAntropometria->listarMediciones($id_atleta);
        
        // Invertimos el arreglo para que Chart.js dibuje la línea de tiempo de más antigua a más reciente (ASC)
        $mediciones = array_reverse($mediciones);
        
        // Mapeamos las llaves para que coincidan EXACTAMENTE con lo que espera antropometria.js
        $data = [];
        foreach ($mediciones as $m) {
            $data[] = [
                'id_medicion'         => $m['id_medicion'],
                'id_atleta'           => $id_atleta, // Lo inyectamos para que el botón "Editar" del JS funcione
                'fecha'               => $m['fecha'],
                'peso'                => $m['peso_kg'],
                'talla'               => $m['talla_cm'],
                'envergadura'         => $m['envergadura_cm'],
                'perimetro_abdominal' => $m['perimetro_abdominal_cm'],
                'grasa_corporal'      => $m['porcentaje_grasa'],
                'imc'                 => $m['imc'],
                'responsable'         => $m['responsable']
            ];
        }
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // Ruta E: Obtener una medición específica para edición
if ($accion === 'obtenerMedicion') {
    header('Content-Type: application/json');
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }
    $medicion = $objAntropometria->obtenerDetallePorId($id);
    if ($medicion) {
        // Renombramos algunos campos para que coincidan con el JS
        $medicion['peso'] = $medicion['peso_kg'];
        $medicion['talla'] = $medicion['talla_cm'];
        $medicion['envergadura'] = $medicion['envergadura_cm'];
        $medicion['perimetro_abdominal'] = $medicion['perimetro_abdominal_cm'];
        $medicion['grasa_corporal'] = $medicion['porcentaje_grasa'];
        echo json_encode($medicion);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Medición no encontrada']);
    }
    exit;
}

// Ruta F: Obtener KPIs
if ($accion === 'obtenerKPIs') {
    header('Content-Type: application/json');
    $kpis = $objAntropometria->obtenerKPIs();
    echo json_encode($kpis);
    exit;
}

// Ruta G: Listar alertas (atletas con medición vencida)
if ($accion === 'listarAlertas') {
    header('Content-Type: application/json');
    $alertas = $objAntropometria->listarAlertas();
    echo json_encode($alertas);
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

    // Mapeo unificado de Payload: 
    // Extraemos los datos una sola vez para mantener el código DRY.
    // Las llaves coinciden exactamente con la Lista Blanca del Modelo.
    $payload = [
        'id_atleta'              => $_POST['id_atleta'] ?? null,
        'fecha'                  => $_POST['fecha'] ?? null,
        'peso_kg'                => $_POST['peso'] ?? null,
        'talla_cm'               => $_POST['talla'] ?? null,
        'envergadura_cm'         => $_POST['envergadura'] ?? null,
        'perimetro_abdominal_cm' => $_POST['perimetro_abdominal'] ?? null,
        'porcentaje_grasa'       => $_POST['grasa_corporal'] ?? null,
        'responsable'            => $_SESSION['nombre'] ?? $_SESSION['id'] ?? 'Sistema'
    ];

    // -----------------------------------------------------------------
    // Ruta E: Guardar Nueva Medición (RF-05.1)
    // -----------------------------------------------------------------
    if ($accionPost === 'guardar') {
        Autorizacion::exigir('antropometria', 'registrar');

        // Pasamos el payload unificado al modelo
        if ($objAntropometria->registrarMedicion($payload)) {
            // Auditoría: operación INSERT
            Bitacora::registrar(
                $_SESSION['id'],
                'Módulo Antropometría',
                'CREATE',
                0, // ID no disponible directamente tras el insert booleano, pero la acción queda registrada
                'N/A',
                'N/A',
                "Nueva medición. Atleta ID: {$payload['id_atleta']}, Peso: {$payload['peso_kg']} kg, Talla: {$payload['talla_cm']} cm"
            );
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
        Autorizacion::exigir('antropometria', 'registrar');
        $id_medicion = (int)($_POST['id_medicion'] ?? 0);
        $justificacion = trim($_POST['justificacion'] ?? '');

        // Regla de negocio y auditoría (es válido dejarla en el controlador porque corresponde a la acción del usuario)
        if (empty($justificacion)) {
            echo json_encode([
                'status' => 'error',
                'errores' => ['El campo <b>justificación</b> es obligatorio para editar un registro.']
            ]);
            exit;
        }

        // Le enviamos al modelo el payload unificado y el parámetro adicional requerido
        if ($objAntropometria->actualizarMedicion($payload, $id_medicion)) {
            Bitacora::registrar(
                $_SESSION['id'],
                'Módulo Antropometría',
                'UPDATE',
                $id_medicion,
                'peso_kg, talla_cm, envergadura_cm, perimetro_abdominal_cm, porcentaje_grasa',
                'Ver valores previos en auditoría',
                "Medición actualizada. Justificación: $justificacion. Nuevos datos: Peso {$payload['peso_kg']} kg, Talla {$payload['talla_cm']} cm"
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
        Autorizacion::exigir('antropometria', 'registrar');
        $id_medicion = (int)($_POST['id_medicion'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Sin justificación');

        // El modelo valida internamente el $id_medicion
        if ($objAntropometria->eliminarMedicion($id_medicion)) {
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

    // -----------------------------------------------------------------
// Ruta H: Anular medición (soft delete)
// -----------------------------------------------------------------
if ($accionPost === 'anular') {
    Autorizacion::exigir('antropometria', 'eliminar');
    $id_medicion = (int)($_POST['id_medicion'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? 'Sin motivo');
    if ($id_medicion <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }
    // Llama al método anular del modelo (lo implementaremos luego)
    if ($objAntropometria->anularMedicion($id_medicion, $motivo)) {
        Bitacora::registrar(
            $_SESSION['id'],
            'Módulo Antropometría',
            'UPDATE',
            $id_medicion,
            'deleted_at',
            'NULL',
            "Medición anulada. Motivo: $motivo"
        );
        echo json_encode(['status' => 'success', 'message' => 'Medición anulada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo anular la medición']);
    }
    exit;
}

// -----------------------------------------------------------------
// Ruta I: Reactivar medición
// -----------------------------------------------------------------
if ($accionPost === 'reactivar') {
    Autorizacion::exigir('antropometria', 'reactivar');
    $id_medicion = (int)($_POST['id_medicion'] ?? 0);
    if ($id_medicion <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }
    if ($objAntropometria->reactivarMedicion($id_medicion)) {
        Bitacora::registrar(
            $_SESSION['id'],
            'Módulo Antropometría',
            'UPDATE',
            $id_medicion,
            'deleted_at',
            'fecha anterior',
            "Medición reactivada"
        );
        echo json_encode(['status' => 'success', 'message' => 'Medición reactivada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo reactivar la medición']);
    }
    exit;
}

// -----------------------------------------------------------------
// Ruta J: Eliminar físicamente (hard delete)
// -----------------------------------------------------------------
if ($accionPost === 'eliminarFisico') {
    Autorizacion::exigir('antropometria', 'eliminardb');
    $id_medicion = (int)($_POST['id_medicion'] ?? 0);
    if ($id_medicion <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }
    // Usamos el método eliminarMedicion existente (hard delete)
    if ($objAntropometria->eliminarMedicion($id_medicion)) {
        Bitacora::registrar(
            $_SESSION['id'],
            'Módulo Antropometría',
            'DELETE',
            $id_medicion,
            'registro completo',
            'Datos eliminados permanentemente',
            "Medición eliminada físicamente"
        );
        echo json_encode(['status' => 'success', 'message' => 'Medición eliminada permanentemente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la medición']);
    }
    exit;
}


}