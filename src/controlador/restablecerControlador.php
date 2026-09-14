<?php

use GrupoProyecto\SisBiomec\modelo\Recuperacion;
use GrupoProyecto\SisBiomec\modelo\UsuarioModelo;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;

if (!empty($_SESSION['id'])) {
    header('Location: ?p=inicio');
    exit;
}

$error = "";
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$valido = false;
$nombreUsuario = "";

$objRecuperacion = new Recuperacion();

if ($token !== '' && strlen($token) === 64 && ctype_xdigit($token)) {
    $user = $objRecuperacion->validarToken($token);

    if ($user) {
        $valido = true;
        $nombreUsuario = $user['nombres'];

        if (isset($_POST['password'], $_POST['password_confirm'])) {
            $pass = $_POST['password'];
            $confirm = $_POST['password_confirm'];

            if ($pass !== $confirm) {
                $error = "Las contrasenas no coinciden.";
            } else {
                $objUsuarios = new UsuarioModelo();
                $errores = $objUsuarios->validarContrasena($pass);

                if (!empty($errores)) {
                    $error = reset($errores);
                } elseif ($objRecuperacion->restablecerContrasena($token, $pass)) {
                    Bitacora::registrar(
                        $user['id_usuario'],
                        'Seguridad',
                        'UPDATE',
                        $user['id_usuario'],
                        'contrasena',
                        '***',
                        '***'
                    );
                    header('Location: ?p=login&restablecido=1');
                    exit;
                } else {
                    $error = "Error del sistema. Intenta más tarde.";
                }
            }
        }
    }
}

require_once 'vista/restablecer.php';
