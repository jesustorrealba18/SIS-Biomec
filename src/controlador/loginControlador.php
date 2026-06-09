<?php

use GrupoProyecto\SisBiomec\modelo\Login;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\seguridad\Captcha;

if (!empty($_SESSION['id'])) {
    header('Location: ?p=inicio');
    exit;
}

$error = "";

if (!empty($_POST['usuario']) && !empty($_POST['password'])) {
    if (!Captcha::verificar($_POST['captcha'] ?? '')) {
        $error = "Codigo de verificacion incorrecto";
    } else {
        $objLogin = new Login();
        $datosUser = $objLogin->validarUsuario($_POST['usuario'], $_POST['password']);

        if (isset($datosUser['error'])) {
            if ($datosUser['error'] !== 'sistema') {
                $error = "Usuario o contraseña incorrectos";
            } else {
                $error = "Error del sistema. Intenta más tarde.";
            }
        } else {
            $_SESSION['id']     = $datosUser['id_usuario'];
            $_SESSION['nombre'] = $datosUser['nombres'] . ' ' . $datosUser['apellidos'];
            $_SESSION['rol']    = $datosUser['roles'];
            if (!Autorizacion::cargarPermisos($datosUser['id_usuario'])) {
                $_SESSION = [];
                session_destroy();
                $error = "Error del sistema. Intenta más tarde.";
                require_once 'vista/login.php';
                exit;
            }
            session_regenerate_id(true);

            header('Location: ?p=inicio');
            exit;
        }
    }
}

require_once 'vista/login.php';
