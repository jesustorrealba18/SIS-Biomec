<?php
// Iniciamos la sesión para poder destruirla
session_start();

// Limpiamos todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borramos también la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruimos la sesión en el servidor
session_destroy();

// Redirigimos al login (esto cargará de nuevo la vista de acceso)
header("Location: ?p=login");
exit;