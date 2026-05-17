<?php
session_start();
require_once 'modelo/entrenador.php';

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

$objEntrenador = new Entrenador();
$mensaje = ""; // Alertas

// Eliminar
if (isset($_GET['eliminar'])) {
    $objEntrenador->eliminarEntrenador($_GET['eliminar']);
    // Redirigimos con un parámetro de mensaje 'm' para el JS
    header('Location: ?p=entrenador&m=eliminado'); 
    exit;
}

// 2. Lógica de Procesamiento de Formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CASO: Edición
    if (!empty($_POST['id_entrenador']) && !empty($_POST['cedula'])) {
        $resultado = $objEntrenador->editarEntrenador($_POST);
        // Redirigimos para limpiar el POST y mostrar éxito
        header('Location: ?p=entrenador&m=editado');
        exit;
    } 
    
    // CASO: Registro Nuevo
    else if (!empty($_POST['cedula'])) {
        $resultado = $objEntrenador->registrarEntrenador($_POST);
        // Redirigimos para limpiar el POST y mostrar éxito
        header('Location: ?p=entrenador&m=registrado');
        exit;
    }
}

// 3. Capturar mensajes de la URL (provenientes de las redirecciones anteriores)
if (isset($_GET['m'])) {
    $mensaje = $_GET['m'];
}

// 4. Carga de datos para la vista
$Entrenador = $objEntrenador->listarEntrenador();

// 5. Llamada a la vista
require_once 'vista/entrenador.php';