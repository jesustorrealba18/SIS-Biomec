<?php
session_start();


use GrupoProyecto\SisBiomec\modelo\Login;

if (!empty($_SESSION['id'])) {
    header('Location: ?p=inicio');
    exit;
}

$error = "";

if (!empty($_POST['usuario']) && !empty($_POST['password'])) {
    $objLogin = new Login();
    $datosUser = $objLogin->validarUsuario($_POST['usuario'], $_POST['password']);

    if (isset($datosUser['error'])) {
        switch ($datosUser['error']) {
            case 'credenciales':
                $error = "Usuario o contraseña incorrectos";
                break;
            case 'inactivo':
                $error = "Tu cuenta está desactivada. Contacta al administrador.";
                break;
            case 'bloqueado':
                $error = "Cuenta bloqueada temporalmente. Intenta después de " . $datosUser['bloqueado_hasta'];
                break;
            default:
                $error = "Error del sistema. Intenta más tarde.";
        }
    } else {
        $_SESSION['id']     = $datosUser['id_usuario'];
        $_SESSION['nombre'] = $datosUser['nombres'] . ' ' . $datosUser['apellidos'];
        $_SESSION['rol']    = $datosUser['roles'];

        header('Location: ?p=inicio');
        exit;
    }
}

require_once 'vista/login.php';
