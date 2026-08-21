<?php
// Controlador: notificacionesControlador.php
use GrupoProyecto\SisBiomec\modelo\Notificacion;

// 1. Candado de seguridad
if (empty($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? '';
$id_usuario_actual = $_SESSION['id'];

// ==========================================================
// PIVOTE: Listar Notificaciones para la Campana (AJAX)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'listar') {
    header('Content-Type: application/json');
    
    $notificaciones = Notificacion::listarPorUsuario($id_usuario_actual, 10);
    $no_leidas = Notificacion::contarNoLeidas($id_usuario_actual);

    foreach ($notificaciones as &$notif) {
        $notif['tiempo_relativo'] = date("d/m/Y h:i A", strtotime($notif['fecha']));
    }

    echo json_encode([
        'status' => 'success', 
        'data' => $notificaciones,
        'no_leidas' => $no_leidas
    ]);
    exit;
}

// ==========================================================
// PIVOTE AJAX: Marcar Notificación como Leída
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'marcar_leida') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Instanciamos el modelo y lo hidratamos
        $notificacion = new Notificacion();
        $notificacion->setDatos([
            'id_notificacion' => $input['id'] ?? '',
            'id_usuario'      => $_SESSION['id'] ?? ''
        ]);

        if ($notificacion->marcarcomoLeida()) {
            enviarJson(['status' => 'success']);
        } else {
            $errores = $notificacion->obtenerErrores();
            $mensajeError = !empty($errores) ? reset($errores) : 'Error de integridad de datos.';
            enviarJson(['status' => 'error', 'message' => $mensajeError]);
        }
        
    } catch (\Throwable $th) {
        enviarJson(['status' => 'error', 'message' => 'Fallo interno: ' . $th->getMessage()]);
    }
}

// Función auxiliar
function enviarJson($datos) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}