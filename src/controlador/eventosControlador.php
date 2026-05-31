<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Evento;
use GrupoProyecto\SisBiomec\modelo\Atleta;

$objEvento = new Evento();

// =====================================================================
// RUTAS GET
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarEventos') {
        header('Content-Type: application/json');
        $estado = !empty($_GET['estado']) ? $_GET['estado'] : null;
        $tipo = !empty($_GET['tipo']) ? $_GET['tipo'] : null;
        echo json_encode($objEvento->listarEventos($estado, $tipo));
        exit;
    }

    if ($accion === 'obtenerDetalle') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        echo json_encode($objEvento->obtenerDetallePorId($id));
        exit;
    }

    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->listarAtletas());
        exit;
    }

    if ($accion === 'listarCategorias') {
        header('Content-Type: application/json');
        $objAtleta = new Atleta();
        echo json_encode($objAtleta->obtenerCategorias());
        exit;
    }

    if ($accion === 'calendario') {
        header('Content-Type: application/json');
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : null;
        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : null;
        echo json_encode($objEvento->obtenerEventosCalendario($mes, $anio));
        exit;
    }

    if ($accion === 'eventosProximos') {
        header('Content-Type: application/json');
        $dias = isset($_GET['dias']) ? (int)$_GET['dias'] : 14;
        echo json_encode($objEvento->obtenerEventosProximos($dias));
        exit;
    }

    require_once 'vista/eventos.php';
    exit;
}

// =====================================================================
// RUTAS POST
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? $_GET['accion'] ?? '';

    if ($accionPost === 'guardar') {
        $errores = $objEvento->validarDatosEvento($_POST);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        if ($objEvento->registrarEvento($_POST)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Eventos', 'INSERT', null,
                'evento', null, $_POST['nombre']
            );
            echo json_encode(['status' => 'success', 'message' => 'Evento registrado exitosamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ocurrio un error al guardar el evento.']);
        }
        exit;
    }

    if ($accionPost === 'editar') {
        $errores = $objEvento->validarDatosEvento($_POST);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        if ($objEvento->editarEvento($_POST)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Eventos', 'UPDATE',
                (int)$_POST['id_evento'], 'datos evento', null, $_POST['nombre']
            );
            echo json_encode(['status' => 'success', 'message' => 'Evento actualizado exitosamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ocurrio un error al actualizar el evento.']);
        }
        exit;
    }

    if ($accionPost === 'actualizarEstado') {
        $id = (int)($_POST['id_evento'] ?? 0);
        $nuevoEstado = $_POST['nuevo_estado'] ?? '';

        if ($objEvento->actualizarEstadoEvento($id, $nuevoEstado)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Eventos', 'UPDATE',
                $id, 'estado', null, $nuevoEstado
            );
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado a ' . $nuevoEstado . '.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Transicion de estado no permitida.']);
        }
        exit;
    }

    if ($accionPost === 'guardarMetas') {
        $id_evento = (int)($_POST['id_evento'] ?? 0);
        $metasJson = $_POST['metas'] ?? '[]';
        $metas = json_decode($metasJson, true);

        if (!is_array($metas) || empty($metas)) {
            echo json_encode(['status' => 'warning', 'errores' => ['metas' => 'Debe agregar al menos una meta.']]);
            exit;
        }

        $objAtleta = new Atleta();
        foreach ($metas as $meta) {
            $errores = $objEvento->validarDatosMeta($meta);
            if (!empty($errores)) {
                echo json_encode(['status' => 'warning', 'errores' => $errores]);
                exit;
            }
        }

        if ($objEvento->registrarMetasLote($id_evento, $metas)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Metas', 'INSERT',
                $id_evento, 'metas_competitivas', null, count($metas) . ' metas'
            );
            echo json_encode(['status' => 'success', 'message' => 'Metas guardadas exitosamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar las metas.']);
        }
        exit;
    }

    if ($accionPost === 'inscribirAtletas') {
        $id_evento = (int)($_POST['id_evento'] ?? 0);
        $atletas_ids = $_POST['atletas_ids'] ?? [];

        if (empty($atletas_ids)) {
            echo json_encode(['status' => 'warning', 'errores' => ['atletas_ids' => 'Seleccione al menos un atleta.']]);
            exit;
        }

        if ($objEvento->inscribirAtletasLote($id_evento, $atletas_ids)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Eventos', 'INSERT',
                $id_evento, 'evento_inscripcion', null, count($atletas_ids) . ' atletas'
            );
            echo json_encode(['status' => 'success', 'message' => 'Atletas inscritos exitosamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al inscribir atletas.']);
        }
        exit;
    }

    if ($accionPost === 'eliminarMeta') {
        $id_meta = (int)($_POST['id_meta'] ?? 0);

        if ($objEvento->eliminarMeta($id_meta)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Metas', 'DELETE',
                $id_meta, 'meta_competitiva', null, 'Eliminada'
            );
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la meta.']);
        }
        exit;
    }

    if ($accionPost === 'eliminarInscripcion') {
        $id_evento = (int)($_POST['id_evento'] ?? 0);
        $id_atleta = (int)($_POST['id_atleta'] ?? 0);

        if ($objEvento->eliminarInscripcion($id_evento, $id_atleta)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Eventos', 'DELETE',
                $id_evento, 'evento_inscripcion', (string)$id_atleta, null
            );
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la inscripcion.']);
        }
        exit;
    }
}
