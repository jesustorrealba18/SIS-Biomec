<?php
// =====================================================================
// CONTROLADOR PIVOTE: MARCAS DEPORTIVAS
// =====================================================================
session_start();

// 1. Filtro de Seguridad
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

// 2. Importamos los Modelos necesarios
use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Marca;
use GrupoProyecto\SisBiomec\modelo\Atleta;


$objMarca = new Marca();

// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados)
// =====================================================================

// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // Ruta A: El Buscador Predictivo pide los atletas
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        // Llama a la función de tu compañero (ajusta el nombre si es distinto)
        echo json_encode($objAtleta->listarAtletas());
        exit;
    }

    // Ruta B: Cargar la tabla principal de marcas
    if ($accion === 'listarMarcas') {
        header('Content-Type: application/json');
        $estado = $_GET['estado'] ?? 'Activo';
        echo json_encode($objMarca->listarMarcas($estado));
        exit;
    }

    // Ruta: Capturar detalles completos + Historial gráfico
    if ($accion === 'obtenerDetalleMarca') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $detalle = $objMarca->obtenerDetallePorId($id);
        echo json_encode($detalle);
        exit;
    }

    // Por defecto: Si no hay acción AJAX, cargamos la pantalla HTML
    require_once 'vista/marcas.php';
    exit;
}

// =====================================================================
// RUTAS POST: Para Guardar, Actualizar o Eliminar (Transacciones)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? '';

    // Ruta C: Guardar nueva marca
    if ($accionPost === 'guardar') {
        
        // 1. Validar reglas de negocio usando el Trait
        $errores = $objMarca->validarDatos($_POST);

        if (!empty($errores)) {
            // Si falta algún campo obligatorio, rebotamos la petición
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        //2. Enviar los datos validados a la transacción del modelo
        if ($objMarca->registrarMarca($_POST)) {
            echo json_encode(['status' => 'success', 'message' => 'Marca registrada exitosamente con sus parciales.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error al guardar en la base de datos.']);
        }
        exit;
        //Para ver cual es el problema
        // try {
        //     if ($objMarca->registrarMarca($_POST)) {
        //         echo json_encode(['status' => 'success', 'message' => 'Marca registrada.']);
        //     }
        // } catch (\Exception $e) {
        //     // Esto mandará el error exacto (ej. "Foreign key constraint fails", "Column not found") a tu JavaScript
        //     echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        // }
        // exit;
    }

    // Ruta D: Archivar (Borrado lógico con justificación)
    if ($accionPost === 'eliminar') {
        $id = (int)($_POST['id_marca'] ?? 0);
        $motivo = $_POST['motivo'] ?? 'Sin justificación'; // Capturamos el motivo

        // 1. El controlador orquesta la base de datos transaccional
        if ($objMarca->eliminarMarca($id, $motivo)) {

            // 2. El controlador orquesta la auditoría (Cero clases instanciadas dentro del modelo)
            Bitacora::registrar(
                $_SESSION['id'],            // Quién lo hizo (el usuario logueado)
                'Módulo Marcas',            // Módulo afectado
                'DELETE',                   // Tipo de operación
                $id,                        // ID de la marca anulada
                'estado / motivo',          // Campos alterados
                'Activo',                   // Cómo estaba antes
                'Inactivo - Motivo: ' . $motivo // Cómo quedó ahora
            );

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo archivar la marca.']);
        }
        exit;
    }

    // Ruta E: Reactivar registro
    if ($accionPost === 'reactivar') {
        $id = (int)($_POST['id_marca'] ?? 0);
        if ($objMarca->reactivarMarca($id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo restaurar la marca.']);
        }
        exit;
    }
}

