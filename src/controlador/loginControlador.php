<?php
session_start();

require_once 'src/modelo/login.php';

// Si ya está logueado, mandarlo al inicio
if (!empty($_SESSION['id'])) {
    header('Location: ?p=inicio');
    exit;
}

$error = "";

if (!empty($_POST['usuario']) && !empty($_POST['password'])) {
    $objLogin = new Login();
    $datosUser = $objLogin->validarUsuario($_POST['usuario'], $_POST['password']);

    if ($datosUser) {
        // Usamos los nombres de columna de tu base de datos
        $_SESSION['id']     = $datosUser['id_usuario']; 
        $_SESSION['nombre'] = $datosUser['nombre'];
        $_SESSION['rol']    = $datosUser['rol']; 
        
        header('Location: ?p=inicio');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}

require_once 'vista/login.php';