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
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // Ruta A: El Buscador Predictivo pide los atletas
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        // Llama a la función de tu compañero (ajusta el nombre si es distinto)
        //echo json_encode($objAtleta->listarAtletas());
        echo json_encode($objAtleta->listar());
        exit;
    }

    // Ruta B: Cargar la tabla principal de marcas
    if ($accion === 'listarMarcas') {
        header('Content-Type: application/json');
       // 1. Capturamos todos los filtros que viajan desde el JS por la URL (GET)
        $estado = $_GET['estado'] ?? 'Activo';
       $id_atleta = !empty($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        $distancia = !empty($_GET['distancia']) ? (int)$_GET['distancia'] : 0;
        $estilo    = trim($_GET['estilo'] ?? '');
        $piscina   = trim($_GET['piscina'] ?? '');

        // Inyectamos los 5 parámetros en el modelo
        $marcas = $objMarca->listarMarcas($estado, $id_atleta, $distancia, $estilo, $piscina);

    
        echo json_encode($marcas);
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
        
            // El controlador manda a guardar. El modelo retorna 'false' y entra al bloque ELSE.
        if ($objMarca->registrarMarca($_POST)) {
            echo json_encode(['status' => 'success', 'message' => 'Marca registrada.']);
        } else {

            // El controlador le dice al objeto: "Vi que fallaste (false). Dame tu lista de errores."
            $errores = $objMarca->obtenerErrores(); 
            
            if (!empty($errores)) {
                // Si hay errores en la lista, se los mandamos al JavaScript (SweetAlert)
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
            } else {
                // Si la lista está vacía, significa que no fue un error de validación, 
                // sino que se cayó la base de datos (PDOException)
                echo json_encode(['status' => 'error', 'message' => 'Error con el servidor.']);
            }
        }
      
    }

    // Ruta C.2: Actualizar registro existente
    if ($accionPost === 'actualizar') {
        $id_marca = (int)($_POST['id_marca'] ?? 0);
        
        if ($id_marca > 0 && $objMarca->actualizarMarca($_POST, $id_marca)) {
            // Opcional: Llamar a Bitacora::registrar para auditar la modificación (RF-06.2)
            echo json_encode(['status' => 'success']);
        } else {
            // Si validaciones fallan, extraemos el primer error del Trait
            $errores = $objMarca->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'Error estructural al actualizar la marca.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    // Ruta D: Archivar (Borrado lógico con justificación)
    if ($accionPost === 'eliminar') {
        $id = (int)($_POST['id_marca'] ?? 0);
        $motivo = $_POST['motivo'] ?? 'Sin justificación'; // Capturamos el motivo

        // 1. El controlador orquesta la base de datos transaccional
        if ($objMarca->eliminarMarca($id, $motivo)) {

            // 2. El controlador orquesta la auditoría (Cero clases instanciadas dentro del modelo)
           /*  Bitacora::registrar(
                $_SESSION['id'],            // Quién lo hizo (el usuario logueado)
                'Módulo Marcas',            // Módulo afectado
                'DELETE',                   // Tipo de operación
                $id,                        // ID de la marca anulada
                'estado / motivo',          // Campos alterados
                'Activo',                   // Cómo estaba antes
                'Inactivo - Motivo: ' . $motivo // Cómo quedó ahora
            ); */

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

