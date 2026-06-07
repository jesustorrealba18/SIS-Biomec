<?php

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Sesion;

$objSesion = new Sesion();


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarSesiones') {
        header('Content-Type: application/json');
        $idEntrenador = (int)$_SESSION['id']; 
        echo json_encode($objSesion->listarSesionesPorEntrenador($idEntrenador));
        exit;
    }

    if ($accion === 'obtenerDetalle') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        echo json_encode($objSesion->obtenerDetalleSesionCompleta($id));
        exit;
    }

    require_once 'vista/sesion.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? $_GET['accion'] ?? '';

    if ($accionPost === 'guardar') {
       
        $errores = $objSesion->validarDatos($_POST, false);

        $seriesJson = $_POST['series'] ?? '[]';
        $series = is_array($seriesJson) ? $seriesJson : json_decode($seriesJson, true);

        if (empty($series)) {
            $errores['series'] = 'Debe agregar al menos una serie al plan de entrenamiento.';
        } else {
            foreach ($series as $index => $serie) {
                $erroresSerie = $objSesion->validarDatosSerie($serie);
                if (!empty($erroresSerie)) {
                    $errores['series_detalle'][$index] = $erroresSerie;
                }
            }
        }

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $_POST['series'] = $series;

        if ($objSesion->registrarSesion($_POST)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Sesiones', 'INSERT', null,
                'sesion_entrenamiento', null, 'Tipo: ' . $_POST['tipo_sesion'] . ' - Fecha: ' . $_POST['fecha']
            );
            echo json_encode(['status' => 'success', 'message' => 'Sesión de entrenamiento planificada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error al guardar la sesión en el sistema.']);
        }
        exit;
    }

    if ($accionPost === 'editar') {
        $errores = $objSesion->validarDatos($_POST, true);

        $seriesJson = $_POST['series'] ?? '[]';
        $series = is_array($seriesJson) ? $seriesJson : json_decode($seriesJson, true);

        if (empty($series)) {
            $errores['series'] = 'La sesión debe contener al menos una serie.';
        } else {
            foreach ($series as $index => $serie) {
                $erroresSerie = $objSesion->validarDatosSerie($serie);
                if (!empty($erroresSerie)) {
                    $errores['series_detalle'][$index] = $erroresSerie;
                }
            }
        }

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $_POST['series'] = $series;

        if ($objSesion->editarSesion($_POST)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Sesiones', 'UPDATE',
                (int)$_POST['id_sesion'], 'datos sesion', null, 'Fecha: ' . $_POST['fecha']
            );
            echo json_encode(['status' => 'success', 'message' => 'Sesión de entrenamiento modificada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error al actualizar la sesión de entrenamiento.']);
        }
        exit;
    }

    if ($accionPost === 'actualizarEstado') {
        $id = (int)($_POST['id_sesion'] ?? 0);
        $nuevoEstado = $_POST['nuevo_estado'] ?? '';

        if ($objSesion->cambiarEstadoSesion($id, $nuevoEstado)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Sesiones', 'UPDATE',
                $id, 'estado', null, $nuevoEstado
            );
            echo json_encode(['status' => 'success', 'message' => 'El estado de la sesión cambió a ' . $nuevoEstado . '.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'La transición de estado solicitada no es válida.']);
        }
        exit;
    }

    if ($accionPost === 'completarSesion') {
        $idSesion = (int)($_POST['id_sesion'] ?? 0);
        
        if ($idSesion === 0) {
            echo json_encode(['status' => 'warning', 'message' => 'Identificador de sesión inválido.']);
            exit;
        }

        if (!isset($_POST['volumen_ejecutado']) || $_POST['volumen_ejecutado'] === '') {
            echo json_encode(['status' => 'warning', 'errores' => ['volumen_ejecutado' => 'El volumen ejecutado es obligatorio.']]);
            exit;
        }

        if ($objSesion->completarSesion($_POST)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Sesiones', 'UPDATE',
                $idSesion, 'completar_sesion', null, 'Volumen Ejecutado: ' . $_POST['volumen_ejecutado'] . 'm'
            );
            echo json_encode(['status' => 'success', 'message' => '¡Sesión completada y guardada exitosamente!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar el cierre de la sesión de entrenamiento.']);
        }
        exit;
    }
}