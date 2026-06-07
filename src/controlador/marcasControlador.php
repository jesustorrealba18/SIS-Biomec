<?php

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Marca;
use GrupoProyecto\SisBiomec\modelo\Atleta;


$objMarca = new Marca();

// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // El Buscador Predictivo pide los atletas
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        
        echo json_encode($objAtleta->listar());
        exit;
    }

    // Cargar la tabla principal de marcas
    if ($accion === 'listarMarcas') {
        header('Content-Type: application/json');
       // Capturamos todos los filtros que viajan desde el JS por la URL (GET)
        $estado = $_GET['estado'] ?? 'Activo';
       $id_atleta = !empty($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
        $distancia = !empty($_GET['distancia']) ? (int)$_GET['distancia'] : 0;
        $estilo    = trim($_GET['estilo'] ?? '');
        $piscina   = trim($_GET['piscina'] ?? '');

        $marcas = $objMarca->listarMarcas($estado, $id_atleta, $distancia, $estilo, $piscina);

        echo json_encode($marcas);
        exit;
    }

    // Capturar detalles completos + Historial gráfico
    if ($accion === 'obtenerDetalleMarca') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $detalle = $objMarca->obtenerDetallePorId($id);
        echo json_encode($detalle);
        exit;
    }


    require_once 'vista/marcas.php';
    exit;
}

// =====================================================================
// RUTAS POST: Para Guardar, Actualizar o Eliminar (Transacciones)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? '';

    $id_usuario = $_SESSION['id'] ?? 0;

    // Guardar nueva marca
    if ($accionPost === 'registrar') {

         // El controlador manda a guardar. El modelo retorna 'false' y entra al bloque ELSE.
        if ($objMarca->registrarMarca($_POST)) {

                $datosGuardados = $_POST;
                unset($datosGuardados['accion']);

                Bitacora::registrar(
                    $id_usuario, 
                    'Marcas', 
                    'CREATE', // Respeta estrictamente tu ENUM
                    null, 
                    'Múltiples campos (Registro Completo)', 
                    null, 
                    json_encode($datosGuardados, JSON_UNESCAPED_UNICODE) 
                );           

            echo json_encode(['status' => 'success', 'message' => 'Marca registrada.']);
        } else {

            $errores = $objMarca->obtenerErrores(); 
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error con el servidor.']);
            }
        }

       
      
    }

    // Actualizar registro existente
    if ($accionPost === 'actualizar') {
        $id_marca = (int)($_POST['id_marca'] ?? 0);
        
        if ($id_marca > 0 && $objMarca->actualizarMarca($_POST, $id_marca)) {

            $datosNuevos = $_POST;
            unset($datosNuevos['accion'], $datosNuevos['id_marca']);

           Bitacora::registrar(
                    $id_usuario, 
                    'Marcas', 
                    'UPDATE', 
                    $id_marca, 
                    'Datos de la Marca', 
                    'Ver historial previo', 
                    json_encode($datosNuevos, JSON_UNESCAPED_UNICODE)
                );
                
            echo json_encode(['status' => 'success']);
        } else {
            // Si validaciones fallan, extraemos el primer error del Trait
            $errores = $objMarca->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'Error al actualizar la marca.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    //Archivar (Borrado lógico con justificación)
    if ($accionPost === 'eliminar') {
        $id = (int)($_POST['id_marca'] ?? 0);
        $motivo = $_POST['motivo'] ?? 'Sin justificación'; // Capturamos el motivo

        if ($objMarca->eliminarMarca($id, $motivo)) {
            Bitacora::registrar($id_usuario, 'Marcas', 'DELETE', $id, 'estado', 'Activo', "Inactivo (Motivo: $motivo)");
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo archivar la marca.']);
        }
        exit;
    }

    // Reactivar registro
    if ($accionPost === 'reactivar') {
        $id = (int)($_POST['id_marca'] ?? 0);
        if ($objMarca->reactivarMarca($id)) {
            Bitacora::registrar($id_usuario, 'Marcas', 'RESTORE', $id, 'estado', 'Inactivo', 'Activo');
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo restaurar la marca.']);
        }
        exit;
    }
}

