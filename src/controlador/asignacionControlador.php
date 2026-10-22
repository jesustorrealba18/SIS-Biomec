<?php

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\modelo\Asignacion;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objAsignacion = new Asignacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['accion'])) {
        if ($_POST['accion'] === 'eliminar') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            if ($objAsignacion->desactivarAsignacion($id)) {
                echo json_encode(['status' => 'success', 'message' => 'Asignación desactivada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo desactivar la asignación.']);
            }
            exit;
        }

        if ($_POST['accion'] === 'eliminarFisico') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;

            $objAsignacion->setIdEliminar($id);
            if ($objAsignacion->eliminarAsignacion()) {
                echo json_encode(['status' => 'success', 'message' => 'Asignación eliminada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la asignación.']);
            }
            exit;
        }

        if ($_POST['accion'] === 'reactivar') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;

            $asignacion = $objAsignacion->obtenerAsignacionPorId($id);
            if ($asignacion && !empty($asignacion['fecha_vigencia_fin']) && $asignacion['fecha_vigencia_fin'] < date('Y-m-d')) {
                echo json_encode(['status' => 'error', 'message' => 'No se puede reactivar: la fecha de vigencia fin ya pasó.']);
                exit;
            }

            if ($objAsignacion->reactivarAsignacion($id)) {
                echo json_encode(['status' => 'success', 'message' => 'Asignación reactivada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo reactivar la asignación.']);
            }
            exit;
        }

        if ($_POST['accion'] === 'completar') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;

            if ($objAsignacion->completarAsignacion($id)) {
                $asignacionCompleta = $objAsignacion->obtenerAsignacionPorId($id);
                if ($asignacionCompleta) {
                    $objAsignacion->notificarFinAsignacion($asignacionCompleta);
                }
                echo json_encode(['status' => 'success', 'message' => 'Asignación completada.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo completar la asignación.']);
            }
            exit;
        }

        if ($_POST['accion'] === 'cambiarEstado') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
            if ($objAsignacion->cambiarEstadoAsignacion($id, $estado)) {
                echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado.']);
            }
            exit;
        }
    }

    Autorizacion::exigir('asignacion', 'gestionar');

    $_POST['activa'] = isset($_POST['activa']) ? 1 : 0;

    if (isset($_POST['fecha_vigente_inicio']) && !isset($_POST['fecha_vigencia_inicio'])) {
        $_POST['fecha_vigencia_inicio'] = $_POST['fecha_vigente_inicio'];
    }
    if (isset($_POST['fecha_vigente_fin']) && !isset($_POST['fecha_vigencia_fin'])) {
        $_POST['fecha_vigencia_fin'] = $_POST['fecha_vigente_fin'];
    }

    $carrilesSeleccionados = [];
    if (!empty($_POST['carriles_seleccionados'])) {
        $decoded = json_decode($_POST['carriles_seleccionados'], true);
        if (is_array($decoded)) {
            $carrilesSeleccionados = array_map('intval', $decoded);
        }
    }

    $idOriginal = !empty($_POST['id_asignacion']) ? (string)$_POST['id_asignacion'] : null;

    if (!$idOriginal && count($carrilesSeleccionados) > 1) {
        $resultados = [];
        $erroresAcumulados = [];

        foreach ($carrilesSeleccionados as $idCarril) {
            $_POST['id_carril'] = $idCarril;
            $objAsignacion->setDatos($_POST);
            $errs = $objAsignacion->validarDatos();

            if (!empty($errs)) {
                foreach ($errs as $campo => $msg) {
                    $erroresAcumulados[] = "Carril {$idCarril}: {$msg}";
                }
                continue;
            }

            if ($objAsignacion->registrarAsignacion()) {
                $resultados[] = $idCarril;
            } else {
                $erroresAcumulados[] = "Carril {$idCarril}: error al guardar en BD";
            }
        }

        if (!empty($erroresAcumulados)) {
            echo json_encode([
                'status' => 'warning',
                'errores' => ['general' => implode('<br>', $erroresAcumulados)]
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Se crearon ' . count($resultados) . ' asignaciones correctamente.'
        ]);
        exit;
    }

    if (!empty($carrilesSeleccionados)) {
        $_POST['id_carril'] = $carrilesSeleccionados[0];
    }

    $objAsignacion->setDatos($_POST);

    $errores = $objAsignacion->validarDatos($idOriginal);

    if (!empty($errores)) {
        echo json_encode(['status' => 'warning', 'errores' => $errores]);
        exit;
    }

    try {
        if ($idOriginal) {
            $resultado = $objAsignacion->editarAsignacion();
            $idAsignacion = (int)$idOriginal;
        } else {
            $resultado = $objAsignacion->registrarAsignacion();
            $idAsignacion = $objAsignacion->obtenerUltimoIdAsignacion();
        }

        if ($resultado) {
            if (isset($_POST['activa']) && $_POST['activa'] == 1 && $idAsignacion) {
                $asignacionCompleta = $objAsignacion->obtenerAsignacionPorId($idAsignacion);
                if ($asignacionCompleta) {
                    $objAsignacion->notificarAsignacionGrupo($asignacionCompleta);
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Asignación guardada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en base de datos al guardar.']);
        }
    } catch (Exception $e) {
        error_log("EXCEPCIÓN en asignacion: " . $e->getMessage());
        error_log($e->getTraceAsString());
        echo json_encode(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarAsignaciones') {
        header('Content-Type: application/json');
        $estadoInput = $_GET['estado'] ?? 'Activo';
        $estadoInt = ($estadoInput === 'Activo') ? 1 : 0;
        echo json_encode($objAsignacion->listarAsignaciones($estadoInt));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarCarriles') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarCarrilesActivos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarHorarios') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarHorariosActivos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarGruposParaSelect') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarTodosLosGrupos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerAsignacion' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->obtenerAsignacionPorId((int)$_GET['id']));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleCarril' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->obtenerCarrilPorId((int)$_GET['id']));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleBloque' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->obtenerHorarioPorId((int)$_GET['id']));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleGrupo' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->obtenerGrupoPorId((int)$_GET['id']));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarCompletadas') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarAsignacionesCompletadas());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'verificarVencidas') {
        header('Content-Type: application/json');
        $cantidad = $objAsignacion->verificarAsignacionesVencidas();
        echo json_encode([
            'status' => 'success',
            'message' => "Se completaron $cantidad asignaciones vencidas."
        ]);
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'carrilesDisponibles') {
        header('Content-Type: application/json');
        $dia = $_GET['dia'] ?? null;
        $horaInicio = $_GET['hora_inicio'] ?? null;
        $horaFin = $_GET['hora_fin'] ?? null;
        echo json_encode($objAsignacion->obtenerCarrilesDisponibles($dia, $horaInicio, $horaFin));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'estadoCarrilesPorBloque') {
        header('Content-Type: application/json');
        $idBloque = (int)($_GET['id_bloque'] ?? 0);
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        echo json_encode($objAsignacion->obtenerEstadoCarrilesPorBloque($idBloque, $fechaInicio, $fechaFin));
        exit;
    }

    require_once 'vista/asignacion.php';
}