<?php

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\modelo\Evento;

// =====================================================================
// RUTAS GET
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'calendario') {
        header('Content-Type: application/json');

        if (!Autorizacion::verificar('calendario', 'ver') && !Autorizacion::verificar('eventos', 'ver')) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Sin permisos para esta accion.']);
            exit;
        }

        $objEvento = new Evento();
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : null;
        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : null;
        $eventos = $objEvento->obtenerEventosCalendario($mes, $anio);

        // Quien no gestiona eventos no recibe enlaces hacia el modulo de Eventos
        if (!Autorizacion::verificar('eventos', 'ver')) {
            foreach ($eventos as &$ev) {
                $ev['url'] = null;
            }
            unset($ev);
        }

        echo json_encode($eventos);
        exit;
    }
}

require_once 'vista/calendario.php';
exit;
