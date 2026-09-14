<?php

use GrupoProyecto\SisBiomec\modelo\Recuperacion;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Captcha;
use GrupoProyecto\SisBiomec\seguridad\Mailer;

if (!empty($_SESSION['id'])) {
    header('Location: ?p=inicio');
    exit;
}

$error = "";
$mensaje = "";

if (!empty($_POST['usuario'])) {
    if (!Captcha::verificar($_POST['captcha'] ?? '')) {
        $error = "Codigo de verificacion incorrecto";
    } else {
        $correo = trim($_POST['usuario']);
        $objRecuperacion = new Recuperacion();
        $resultado = $objRecuperacion->solicitarToken($correo);

        if (!$resultado['ok'] && !empty($resultado['ip_limitada'])) {
            $error = "Demasiados intentos desde esta conexion. Intenta más tarde.";
        } elseif (!$resultado['ok']) {
            $error = "Error del sistema. Intenta más tarde.";
        } elseif (!empty($resultado['token'])) {
            $enlace = rtrim($_ENV['APP_URL'] ?? '', '/') . '/index.php?p=restablecer&token=' . urlencode($resultado['token']);
            $cuerpo = Mailer::plantillaRecuperacion($resultado['nombre'], $enlace, $resultado['minutos']);

            if (Mailer::enviar($correo, "Recuperacion de contrasena - SGRD", $cuerpo)) {
                Bitacora::registrar(
                    $resultado['id_usuario'],
                    'Seguridad',
                    'REQUEST',
                    $resultado['id_usuario'],
                    'recuperacion_contrasena',
                    null,
                    'Enlace enviado a ' . $correo
                );
            } else {
                error_log("RECOVER: No se pudo entregar el correo de recuperacion de [{$correo}].");
            }
        } elseif (!empty($resultado['enfriamiento'])) {
            error_log("RECOVER: Solicitud repetida bloqueada por enfriamiento (usuario {$resultado['id_usuario']}).");
        }

        $mensaje = "Si el correo esta registrado, recibirás un enlace para restablecer tu contrasena en los proximos minutos.";
    }
}

require_once 'vista/recuperar.php';
