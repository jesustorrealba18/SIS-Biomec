<?php

ob_start();

session_start();

if (empty($_SESSION['id'])) {
    ob_end_clean();
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\modelo\Periodizacion;
use GrupoProyecto\SisBiomec\modelo\Evento;

$objPeriodizacion = new Periodizacion();

// =====================================================================
// RUTAS GET
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarMacrociclos') {
        header('Content-Type: application/json');
        $id_temporada = !empty($_GET['id_temporada']) ? (int)$_GET['id_temporada'] : null;
        $id_grupo = !empty($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : null;
        $estado = !empty($_GET['estado']) ? $_GET['estado'] : null;
        ob_end_clean();
        echo json_encode($objPeriodizacion->listarMacrociclos($id_temporada, $id_grupo, $estado));
        exit;
    }

    if ($accion === 'obtenerDetalle') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        ob_end_clean();
        echo json_encode($objPeriodizacion->obtenerDetalleMacrociclo($id));
        exit;
    }

    if ($accion === 'obtenerTemporadas') {
        header('Content-Type: application/json');
        ob_end_clean();
        echo json_encode($objPeriodizacion->obtenerTemporadas());
        exit;
    }

    if ($accion === 'obtenerGrupos') {
        header('Content-Type: application/json');
        ob_end_clean();
        echo json_encode($objPeriodizacion->obtenerGrupos());
        exit;
    }

    if ($accion === 'obtenerEventosObjetivo') {
        header('Content-Type: application/json');
        $objEvento = new Evento();
        ob_end_clean();
        echo json_encode($objEvento->obtenerEventosComoObjetivo());
        exit;
    }

    if ($accion === 'obtenerFases') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        ob_end_clean();
        echo json_encode($objPeriodizacion->obtenerFases($id));
        exit;
    }

    if ($accion === 'obtenerMesociclos') {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        ob_end_clean();
        echo json_encode($objPeriodizacion->obtenerMesociclos($id));
        exit;
    }

    require_once 'vista/periodizacion.php';
    exit;
}

// =====================================================================
// RUTAS POST
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['id'])) {
        ob_end_clean();
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Sesion expirada']);
        exit;
    }

    header('Content-Type: application/json');
    $accionPost = $_POST['accion'] ?? $_GET['accion'] ?? '';

    if ($accionPost === 'guardar') {
        $errores = $objPeriodizacion->validarDatosMacrociclo($_POST);

        if (!empty($errores)) {
            ob_end_clean();
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        if ($objPeriodizacion->registrarMacrociclo($_POST)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Periodizacion', 'INSERT', null,
                'macrociclo', null, $_POST['nombre'] ?? 'Nuevo macrociclo'
            );
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => 'Macrociclo registrado exitosamente.']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Ocurrio un error al guardar el macrociclo.']);
        }
        exit;
    }

    if ($accionPost === 'editar') {
        $errores = $objPeriodizacion->validarDatosMacrociclo($_POST);

        if (!empty($errores)) {
            ob_end_clean();
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $idMacrociclo = (int)($_POST['id_macrociclo'] ?? 0);

        if ($objPeriodizacion->editarMacrociclo($_POST)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Periodizacion', 'UPDATE',
                $idMacrociclo, 'macrociclo', null, $_POST['nombre'] ?? ''
            );
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => 'Macrociclo actualizado exitosamente.']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Ocurrio un error al actualizar el macrociclo.']);
        }
        exit;
    }

    if ($accionPost === 'generarPeriodizacion') {
        $id_macrociclo = (int)($_POST['id_macrociclo'] ?? 0);

        if ($id_macrociclo === 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID de macrociclo no especificado.']);
            exit;
        }

        $config = [];
        $camposConfig = ['pct_acumulacion', 'pct_transmutacion', 'pct_realizacion', 'pct_deload', 'frecuencia_deload'];
        foreach ($camposConfig as $campo) {
            if (isset($_POST[$campo]) && is_numeric($_POST[$campo])) {
                $config[$campo] = (int)$_POST[$campo];
            }
        }

        $resultado = $objPeriodizacion->generarPlanPeriodizacion($id_macrociclo, $config);

        if ($resultado['exito']) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Periodizacion', 'UPDATE',
                $id_macrociclo, 'plan_atr', null, "Generado: {$resultado['total_semanas']} semanas"
            );
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => $resultado['mensaje'], 'data' => $resultado]);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => $resultado['mensaje']]);
        }
        exit;
    }

    if ($accionPost === 'actualizarEstado') {
        $id = (int)($_POST['id_macrociclo'] ?? 0);
        $nuevoEstado = $_POST['nuevo_estado'] ?? '';

        if ($objPeriodizacion->actualizarEstadoMacrociclo($id, $nuevoEstado)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Periodizacion', 'UPDATE',
                $id, 'estado', null, $nuevoEstado
            );
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado a ' . $nuevoEstado . '.']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Transicion de estado no permitida.']);
        }
        exit;
    }

    if ($accionPost === 'guardarMesociclo') {
        $errores = $objPeriodizacion->validarDatosMesociclo($_POST);

        if (!empty($errores)) {
            ob_end_clean();
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $id_meso = (int)($_POST['id_mesociclo'] ?? 0);

        if ($id_meso > 0) {
            if ($objPeriodizacion->editarMesociclo($_POST)) {
                Bitacora::registrar(
                    $_SESSION['id'], 'Modulo Periodizacion', 'UPDATE',
                    $id_meso, 'mesociclo', null, $_POST['nombre']
                );
                ob_end_clean();
                echo json_encode(['status' => 'success', 'message' => 'Mesociclo actualizado exitosamente.']);
            } else {
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el mesociclo.']);
            }
        } else {
            if ($objPeriodizacion->registrarMesociclo($_POST)) {
                Bitacora::registrar(
                    $_SESSION['id'], 'Modulo Periodizacion', 'INSERT',
                    (int)$_POST['id_macrociclo'], 'mesociclo', null, $_POST['nombre']
                );
                ob_end_clean();
                echo json_encode(['status' => 'success', 'message' => 'Mesociclo registrado exitosamente.']);
            } else {
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'Error al guardar el mesociclo.']);
            }
        }
        exit;
    }

    if ($accionPost === 'eliminarMesociclo') {
        $id_meso = (int)($_POST['id_mesociclo'] ?? 0);

        if ($objPeriodizacion->eliminarMesociclo($id_meso)) {
            Bitacora::registrar(
                $_SESSION['id'], 'Modulo Periodizacion', 'DELETE',
                $id_meso, 'mesociclo', null, 'Eliminado'
            );
            ob_end_clean();
            echo json_encode(['status' => 'success']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el mesociclo.']);
        }
        exit;
    }
}
