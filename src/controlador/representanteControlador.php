<?php
// src/controlador/representanteControlador.php

// 1. Filtro de Reglas Básicas (El pivote ataja a los intrusos)
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

// Traemos el Modelo que sí es una clase y hace el trabajo pesado
use GrupoProyecto\SisBiomec\modelo\Representante;
use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
$objRepresentante = new Representante();
$objAtleta = new Atleta();

// 2. Pivote para acciones POST (Guardar / Actualizar desde AJAX/Fetch)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');


     // Si la acción es eliminar (Atrapamos la petición de SweetAlert)
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        Autorizacion::exigir('representantes', 'gestionar');
        $id = isset($_POST['id_representante']) ? (int)$_POST['id_representante'] : 0;

      

        if ($objRepresentante->eliminarRepresentante($id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo desactivar el registro.']);
        }
        exit;
    }



        if (isset($_POST['accion']) && $_POST['accion'] === 'reactivar') {
            Autorizacion::exigir('representantes', 'gestionar');
            $id = isset($_POST['id_representante']) ? (int)$_POST['id_representante'] : 0;
            if ($objRepresentante->reactivarRepresentante($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo reactivar el registro.']);
            }
            exit;
        }    
    
    // Verificamos si es una actualización (si trae ID) o un registro nuevo
    Autorizacion::exigir('representantes', 'gestionar');
    $excluirCedula = !empty($_POST['cedula_original']) ? $_POST['cedula_original'] : null;
    
    // El pivote NO valida, le pasa la pelota al Modelo
    $errores = $objRepresentante->validarDatos($_POST, $excluirCedula);

    if (!empty($errores)) {
        // Si el modelo dice que hay errores, el pivote los devuelve al Frontend
        echo json_encode(['status' => 'warning', 'errores' => $errores]);
        exit;
    }

    // SOLUCIÓN A LA ADVERTENCIA: Inicializamos la variable por defecto
    $resultado = false; 

    // Si todo está bien, el pivote le ordena al Modelo que guarde en la BD
    if ($excluirCedula) {
        // Lógica de actualizar (Descomentar cuando esté lista en el modelo)
        $resultado = $objRepresentante->actualizarRepresentante($_POST, $excluirCedula);
    } else {
        $resultado = $objRepresentante->registrarRepresentante($_POST);
    }

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Operación exitosa.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en BD o función en desarrollo.']);
    }
    exit; // Cortamos ejecución, el pivote no hace más nada
}

// 3. Pivote para acciones GET (UNIFICADO - Consultas, AJAX y Vistas)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {



    // B) ¡NUEVO! Petición para listar representantes en la tabla


        if (isset($_GET['accion']) && $_GET['accion'] === 'listarRepresentantes') {
            header('Content-Type: application/json');
            // Capturamos el estado que manda JavaScript, si no viene, por defecto es 'Activo'
            $estado = $_GET['estado'] ?? 'Activo';
            echo json_encode($objRepresentante->listarRepresentantes($estado));
            exit;
        }
    

    // D) ¡NUEVO! Petición para ver el mini-perfil de un atleta vinculado
    if (isset($_GET['accion']) && $_GET['accion'] === 'verPerfilAtleta' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        // Usamos la función que ya hizo tu líder en su modelo
        $datosAtleta = $objAtleta->obtenerDetallePorId((int)$_GET['id']);
        echo json_encode($datosAtleta);
        exit;
    }
    
    // A) Petición de JavaScript (Fetch) para listar atletas en los checkboxes
    if (isset($_GET['accion']) && $_GET['accion'] === 'listarAtletas') {
        header('Content-Type: application/json');
        echo json_encode($objAtleta->listarMenoresSinRepresentante());
        exit;
    }
        // if (isset($_GET['accion']) && $_GET['accion'] === 'listarAtletas') {
        //         header('Content-Type: application/json');
                
        //         // Aquí pregunta: ¿Me mandaron un ID para editar? Si no, pongo 0 (Registrar)
        //         $id_rep = isset($_GET['id_representante']) ? (int)$_GET['id_representante'] : 0;
                
        //         // Le pasamos el número al Modelo y él se encarga de cambiar el SQL internamente
        //         echo json_encode($objAtleta->listarMenoresParaRepresentante($id_rep));
        //         exit;
        //     }

    // ACCIÓN: Petición de JavaScript para rellenar el formulario al Editar
    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerRepresentante' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        // Buscamos al representante y lo devolvemos en JSON limpio
        echo json_encode($objRepresentante->obtenerPorId((int)$_GET['id']));
        exit; // <- ESTO ES VITAL para que no se imprima el <!DOCTYPE html> debajo
    }

   
  

    // C) Petición GET normal: El usuario entra al directorio
    // El pivote pide los datos al modelo y carga la vista
    // $representantes = $objRepresentante->listarRepresentantes();
    require_once 'vista/representante.php';
}