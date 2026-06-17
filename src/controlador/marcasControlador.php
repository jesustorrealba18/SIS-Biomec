<?php

use GrupoProyecto\SisBiomec\modelo\Marca;
use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\modelo\sesiones;
use GrupoProyecto\SisBiomec\modelo\Evento;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;


if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}




$objMarca = new Marca();
$objAtleta = new Atleta();

// =====================================================================
// RUTAS GET: Para cargar vistas y pedir datos (Listados)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // El Buscador Predictivo pide los atletas
    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        
        
        echo json_encode($objAtleta->listar());
        exit;
    }

    // Selector de Sesiones
    if ($accion === 'listarSesionesSelect') {
        header('Content-Type: application/json');
        $objSesion = new Sesiones();
        echo json_encode($objSesion->listarSesionesSelectMarca());
        exit;
    }

    // Selector de Eventos
    if ($accion === 'listarEventosSelect') {
        header('Content-Type: application/json');
        $objEvento = new Evento();
        echo json_encode($objEvento->listarEventosSelectMarca());
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
        Autorizacion::exigir('marcas', 'registrar');

        $objMarca->setAtributos($_POST);

        if ($objMarca->getRegistrarMarca()) {

                $datosFiltrados = $objMarca->obtenerDatos();
                unset($datosFiltrados['accion']);

                Bitacora::registrar(
                    $id_usuario, 
                    'Marcas', 
                    'CREATE', 
                    null, 
                    'Múltiples campos (Registro Completo)', 
                    null, 
                    json_encode($datosFiltrados, JSON_UNESCAPED_UNICODE) 
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
        Autorizacion::exigir('marcas', 'registrar');

        

            $objMarca->setAtributos($_POST);
        if ($objMarca->getActualizarMarca()) {
            
            
            $id_marca = $objMarca->getCampo('id_marca');
            $datosFiltrados = $objMarca->obtenerDatos();
            unset($datosFiltrados['id_marca'], $datosFiltrados['accion']);

           Bitacora::registrar(
                    $id_usuario, 
                    'Marcas', 
                    'UPDATE', 
                    $id_marca, 
                    'Datos de la Marca', 
                    'Ver historial previo', 
                    json_encode($datosFiltrados, JSON_UNESCAPED_UNICODE)
                );
                
            echo json_encode(['status' => 'success']);
        } else {

            $errores = $objMarca->obtenerErrores();
            $mensaje = !empty($errores) ? reset($errores) : 'Error al actualizar el registro.';
            echo json_encode(['status' => 'error', 'message' => $mensaje]);
            
            // $errores = $objMarca->obtenerErrores();
            // $mensaje = !empty($errores) ? reset($errores) : 'Error al actualizar la marca.';
            // echo json_encode(['status' => 'error', 'message' => $mensaje]);
        }
        exit;
    }

    //Archivar (Borrado lógico con justificación)
    if ($accionPost === 'eliminar') {
        Autorizacion::exigir('marcas', 'registrar');
        

        $objMarca->setAtributos($_POST);

        if ($objMarca->getEliminarMarca()) {
            
            $id = $objMarca->getCampo('id_marca');
            $motivo = $objMarca->getCampo('motivo_eliminacion');
            
            Bitacora::registrar($id_usuario, 'Marcas', 'DELETE', $id, 'estado', 'Activo', "Inactivo (Motivo: $motivo)");
            
            echo json_encode(['status' => 'success']);
        } else {

            $errores = $objMarca->obtenerErrores();
            
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'message' => reset($errores),'errores' => $errores]);
            } else {
                echo json_encode(['status' => 'error','message' => 'El servidor no pudo consolidar Archivar registro por un problema de infraestructura interna.'
                ]);
            }


        }
        exit;
    }

    // Reactivar registro
    if ($accionPost === 'reactivar') {
        Autorizacion::exigir('marcas', 'registrar');

        $objMarca->setAtributos($_POST);

        if ($objMarca->getReactivarMarca()) {
            $id = $objMarca->getCampo('id_marca');
            Bitacora::registrar($id_usuario, 'Marcas', 'RESTORE', $id, 'estado', 'Inactivo', 'Activo');
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo restaurar la marca.']);
        }
        exit;
    }

    // =====================================================================
    // RUTAS PARA LISTADOS DINÁMICOS EN CASCADA (AJAX POST)
    // =====================================================================

    $accionURL = $_GET['accion'] ?? ($_POST['accion'] ?? '');

    // Listar Atletas Presentes en una Sesión
    if ($accionURL === 'listarAtletasPorSesion') {
        header('Content-Type: application/json');
        
        // Atrapamos el id_contexto que envía el FormData desde el JS
        $id_sesion = (int)($_POST['id_contexto'] ?? 0);
        
        $atletas = $objAtleta->obtenerAtletasPorSesion($id_sesion);
        echo json_encode($atletas);
        exit;
    }

    // Listar Atletas Inscritos en un Evento
    if ($accionURL === 'listarAtletasPorEvento') {
        header('Content-Type: application/json');
        
        $id_evento = (int)($_POST['id_contexto'] ?? 0);
        
        $atletas = $objAtleta->obtenerAtletasPorEvento($id_evento);
        echo json_encode($atletas);
        exit;
    }


}

