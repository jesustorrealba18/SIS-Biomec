<?php

// src/controlador/representanteControlador.php

// 1. Filtro de Reglas Básicas (El pivote ataja a los intrusos)
session_start();
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

// Traemos el Modelo que sí es una clase y hace el trabajo pesado
use GrupoProyecto\SisBiomec\modelo\Representante;
$objRepresentante = new Representante();

// 2. Pivote para acciones POST (Guardar / Actualizar desde AJAX/Fetch)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Verificamos si es una actualización (si trae ID) o un registro nuevo
    $excluirCedula = !empty($_POST['cedula_original']) ? $_POST['cedula_original'] : null;
    
    // El pivote NO valida, le pasa la pelota al Modelo
    $errores = $objRepresentante->validarDatos($_POST, $excluirCedula);

    if (!empty($errores)) {
        // Si el modelo dice que hay errores, el pivote los devuelve al Frontend
        echo json_encode(['status' => 'warning', 'errores' => $errores]);
        exit;
    }

    // Si todo está bien, el pivote le ordena al Modelo que guarde en la BD
    if ($excluirCedula) {
        // Lógica de actualizar (si la tuvieran armada en el modelo)
        // $resultado = $objRepresentante->actualizarRepresentante($_POST);
    } else {
        $resultado = $objRepresentante->registrarRepresentante($_POST);
    }

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Operación exitosa.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos.']);
    }
    exit; // Cortamos ejecución, el pivote no hace más nada
}

// 3. Pivote para acciones GET (Eliminar por URL o Cargar la Vista)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Si mandaron a eliminar
    if (isset($_GET['eliminar'])) {
        // $objRepresentante->eliminarRepresentante($_GET['eliminar']);
        header('Location: ?p=representante&msg=eliminado');
        exit;
    }

    // Si es una petición GET normal (entrar al módulo), el pivote pide los datos...
    $representantes = $objRepresentante->listarRepresentantes();
    
    // ...y carga la vista inyectándole los datos.
    require_once 'vista/representante.php';
}
?>