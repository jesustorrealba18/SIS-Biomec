<?php

function esPeticionAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function mostrarErrorPagina(string $titulo, string $mensaje, string $codigo = ''): void {
    if (esPeticionAjax()) {
        http_response_code(503);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error_db',
            'message' => $mensaje
        ]);
        exit;
    }

    http_response_code(503);
    $tituloEsc = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    $mensajeEsc = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    $codigoEsc = htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');

    require RAIZ . 'vista/error_db.php';
    exit;
}

function mostrarError404(string $titulo = 'Página no encontrada', string $mensaje = 'La sección que buscas no existe o ha sido removida.'): void {
    if (esPeticionAjax()) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error_404',
            'message' => $mensaje
        ]);
        exit;
    }

    http_response_code(404);
    $tituloEsc = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    $mensajeEsc = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');

    require RAIZ . 'vista/error_404.php';
    exit;
}

function mostrarError403(string $titulo = 'Acceso denegado', string $mensaje = 'No tienes permisos para acceder a esta sección. Si crees que es un error, contacta al administrador.'): void {
    if (esPeticionAjax()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error_403',
            'message' => $mensaje
        ]);
        exit;
    }

    http_response_code(403);
    $tituloEsc = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    $mensajeEsc = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');

    require RAIZ . 'vista/error_403.php';
    exit;
}

function mostrarErrorGeneral(string $titulo, string $mensaje, string $codigo = ''): void {
    if (esPeticionAjax()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'message' => $mensaje
        ]);
        exit;
    }

    http_response_code(500);
    $tituloEsc = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    $mensajeEsc = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    $codigoEsc = htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');

    require RAIZ . 'vista/error_general.php';
    exit;
}

set_exception_handler(function (\Throwable $e) {
    $codigoRef = 'ERR-' . date('Ymd') . '-' . substr(md5((string)microtime(true)), 0, 6);

    if ($e instanceof \GrupoProyecto\SisBiomec\seguridad\ConexionException) {
        error_log("[$codigoRef] ConexionException [{$e->getTipoError()}]: {$e->getMessage()} | {$e->getFile()}:{$e->getLine()}");
        if ($e->getPrevious()) {
            error_log("[$codigoRef] PDO original: " . $e->getPrevious()->getMessage());
        }
        mostrarErrorPagina('Servicio no disponible', $e->getMessage(), $codigoRef);
    }

    if ($e instanceof \PDOException) {
        error_log("[$codigoRef] PDOException no capturada: {$e->getMessage()} | {$e->getFile()}:{$e->getLine()}");
        mostrarErrorPagina('Servicio no disponible', 'No se pudo conectar con la base de datos. Por favor, intente nuevamente.', $codigoRef);
    }

    error_log("[$codigoRef] Throwable no capturada: {$e->getMessage()} | {$e->getFile()}:{$e->getLine()}");
    mostrarErrorGeneral('Ocurrió un error inesperado', 'Ha ocurrido un error interno. El equipo técnico ha sido notificado.', $codigoRef);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($error['type'], $fatalTypes, true)) {
        return;
    }

    $codigoRef = 'ERR-' . date('Ymd') . '-' . substr(md5((string)microtime(true)), 0, 6);
    error_log("[$codigoRef] Fatal PHP: {$error['message']} en {$error['file']}:{$error['line']}");

    if (stripos($error['message'], 'mysql') !== false || stripos($error['message'], 'pdo') !== false || stripos($error['message'], 'SQLSTATE') !== false) {
        mostrarErrorPagina('Servicio no disponible', 'No se pudo conectar con la base de datos. Por favor, intente nuevamente.', $codigoRef);
    }

    mostrarErrorGeneral('Ocurrió un error inesperado', 'Ha ocurrido un error interno. El equipo técnico ha sido notificado.', $codigoRef);
});

try {
    $dotenv = Dotenv\Dotenv::createImmutable(RAIZ);
    $dotenv->load();
} catch (Exception $e) {
    die("Error crítico: No se encontró el archivo de configuración de entorno (.env).");
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', isset($_SERVER['HTTPS']) ? 'Strict' : 'Lax');
session_start();

$sessionTimeout = (int)($_ENV['SESSION_TIMEOUT'] ?? 28800);

if (!empty($_SESSION['id'])) {
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $sessionTimeout) {
        $_SESSION = [];
        session_destroy();
        header('Location: ?p=login&expirado=1');
        exit;
    }
    $_SESSION['ultimo_acceso'] = time();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
//header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https://ui-avatars.com; connect-src 'self' https://cdn.jsdelivr.net; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; frame-src 'none'; object-src 'none'; media-src 'self'");
//header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.datatables.net; font-src https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https://ui-avatars.com; connect-src 'self' https://cdn.jsdelivr.net; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; frame-src 'none'; object-src 'none'; media-src 'self'");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.datatables.net; font-src https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https://ui-avatars.com; connect-src 'self' https://cdn.jsdelivr.net https://cdn.datatables.net; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; frame-src 'none'; object-src 'none'; media-src 'self'");


$paginasPermitidas = [
    'login', 'inicio', 'entrenador', 'drills', 'atleta', 'eventos', 'marcas',
    'periodizacion', 'temporadas', 'antropometria', 'representante', 'calendario', 'salir', 'sesiones', 
    'carriles', 'horario', 'asignacion', 'lesion', 'categorias', 'grupo', 'bitacora', 'usuarios', 'roles', 'mantenimiento', 'cargaBienestar', 'mi_perfil', 'asistencia',
    'observacionesTecnicas', 'testFisico', 'normalizacion','notificaciones','analitica', 'live'
];



$pagina = "inicio";
if (!empty($_GET['p']) && in_array($_GET['p'], $paginasPermitidas, true)) {
    $pagina = $_GET['p'];
}

$rutasPublicas = ['login','live'];
$rutasGlobalesPrivadas = ['notificaciones'];

if (!in_array($pagina, $rutasPublicas, true) && empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

/* if (!in_array($pagina, $rutasPublicas, true) && !empty($_SESSION['id'])) {
    if (!\GrupoProyecto\SisBiomec\seguridad\Autorizacion::tieneAcceso($pagina)) {
        mostrarError403();
    }
} */

if (!in_array($pagina, $rutasPublicas, true) && !empty($_SESSION['id'])) {
    
    // Si la página NO forma parte de los servicios globales, exigimos validación por Rol/Permiso
    if (!in_array($pagina, $rutasGlobalesPrivadas, true)) {
        if (!\GrupoProyecto\SisBiomec\seguridad\Autorizacion::tieneAcceso($pagina)) {
            mostrarError403();
            exit; // Aseguramos que detenga la ejecución
        }
    }
}

$archivoControlador = RAIZ . "src/controlador/" . $pagina . "Controlador.php";

if (is_file($archivoControlador)) {
    require_once($archivoControlador);

    $claseControlador = "GrupoProyecto\\SisBiomec\\controlador\\" . ucfirst($pagina) . "Controlador";

    if (class_exists($claseControlador, false)) {
       $dependencias = [
    'GrupoProyecto\\SisBiomec\\controlador\\EntrenadorControlador' => [
        new \GrupoProyecto\SisBiomec\modelo\Entrenador()
    ],
];

    $params = $dependencias[$claseControlador] ?? [];
    $controlador = new $claseControlador(...$params);
    $controlador->handle();
    }
} else {
    mostrarError404();
}
