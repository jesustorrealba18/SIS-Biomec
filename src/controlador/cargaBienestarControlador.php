<?php

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\modelo\CargaBienestar;
use GrupoProyecto\SisBiomec\modelo\Atleta;


if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}



$objCarga = new CargaBienestar();
$id_usuario = $_SESSION['id'];
$rol_usuario = '';
if (Autorizacion::tieneRol('Atleta'))          $rol_usuario = 'Atleta';
elseif (Autorizacion::tieneRol('Representante')) $rol_usuario = 'Representante';
elseif (Autorizacion::tieneRol('Entrenador'))  $rol_usuario = 'Entrenador';
elseif (Autorizacion::tieneRol('Medico'))      $rol_usuario = 'Medico';
elseif (Autorizacion::tieneRol('Administrador')) $rol_usuario = 'Administrador';


function esIntentoDeVulneracion(string $mensaje): bool {
    $pistas = [
        'permiso',           // "No tienes permiso..."
        'no autorizado',
        'ajeno',
        'sin atleta',        // "Usuario sin atleta vinculado"
        'no vinculado',      // idem
        'asistencia',        // "no tienes asistencia registrada"
        'no está completada', // parte del mensaje de sesión
    ];
    foreach ($pistas as $p) {
        if (stripos($mensaje, $p) !== false) return true;
    }
    return false;
}

// =====================================================================
// RUTAS GET (Listados, detalles y utilerías)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        echo json_encode((new Atleta())->listar());
        exit;
    }


    if ($accion === 'listarRPE') {
    header('Content-Type: application/json');
    $fechaInicio  = trim($_GET['fechaInicio'] ?? '');
    $fechaFin     = trim($_GET['fechaFin'] ?? '');
    $id_atleta    = (int)($_GET['id_atleta'] ?? 0);
    $modo         = $_GET['modo'] ?? 'activos';
    $modoPapelera = ($modo === 'papelera');

    $registros = $objCarga->listarRPE(
        $fechaInicio, $fechaFin, $id_atleta, $modoPapelera,
        $id_usuario, $rol_usuario     
    );
    echo json_encode($registros);
    exit;
    }

    

    if ($accion === 'obtenerRPE') {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        $registro = $objCarga->obtenerRPEPorId($id, $id_usuario, $rol_usuario);

        /* if (!$registro) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'No autorizado o no existe.']);
            exit;
        }
        echo json_encode($registro);
        exit; */

          if (!$registro) {
        // Dejar rastro si es atleta/representante intentando leer RPE ajeno
            if ($id > 0 && in_array($rol_usuario, ['Atleta', 'Representante'], true)) {
                Bitacora::registrar(
                    $id_usuario, 'RegistroRPE', 'UPDATE', $id,
                    'Acceso denegado a RPE', null,
                    "Intento de leer RPE ajeno (rol: {$rol_usuario})"
                );
            }
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'No autorizado o no existe.']);
            exit;
        }

    echo json_encode($registro);
    exit;
    }

    if ($accion === 'rpePromedioReciente') {
        header('Content-Type: application/json');
        $id_atleta = (int)($_GET['id_atleta'] ?? 0);
        $dias = (int)($_GET['dias'] ?? 3);
        if ($id_atleta > 0) {
            $promedio = $objCarga->obtenerRpePromedioUltimosDias($id_atleta, $dias);
            echo json_encode(['promedio' => $promedio]);
        } else {
            echo json_encode(['promedio' => null, 'error' => 'ID de atleta requerido']);
        }
        exit;
    }

    if ($accion === 'listarInconsistencias') {
        header('Content-Type: application/json');
        Autorizacion::exigir('rpe', 'ver_inconsistencias');
        echo json_encode($objCarga->listarInconsistencias());
        exit;
    }

    if ($accion === 'anularPorInconsistencia') {
    Autorizacion::exigir('rpe', 'eliminar');
    $id_rpe = (int)($_POST['id_rpe'] ?? 0);
    $motivo = "Inconsistencia biológica detectada: RPE (Reposo) incongruente con marcas de rendimiento (Récord) registradas este día.";
    if ($id_rpe <= 0) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }

    $res = $objCarga->anularRPE($id_rpe, $motivo, $id_usuario, $rol_usuario);
    ob_end_clean();
    if ($res) {
        Bitacora::registrar($id_usuario, 'RegistroRPE', 'DELETE', $id_rpe, 'Anulado por inconsistencia biológica', null, $motivo);
        echo json_encode(['status' => 'success', 'message' => 'Registro anulado automáticamente.']);
    } else {
        $err = $objCarga->obtenerErrores();
        $msg = reset($err) ?: 'Error al anular.';

        if (esIntentoDeVulneracion($msg)) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'DELETE', $id_rpe,
                'Intento bloqueado', null, $msg);
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => '⛔ ' . $msg]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $msg]);
        }
    }
    exit;
    }

    if ($accion === 'listarRecomendacionesEntrenador') {
        header('Content-Type: application/json');
         Autorizacion::exigir('rpe', 'ver_recomendaciones');
        $id_usuario = $_SESSION['id'] ?? 0;
        if ($id_usuario <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit;
        }
        $recomendaciones = $objCarga->obtenerRecomendacionesPorEntrenador($id_usuario);
        echo json_encode($recomendaciones);
        exit;
    }

    if ($accion === 'listarSesionesPendientes') {
        header('Content-Type: application/json');
        $id_atleta_req = (int)($_GET['id_atleta'] ?? 0);
        
        $esAtleta = Autorizacion::tieneRol('Atleta');

    $sesiones = $objCarga->obtenerSesionesPendientesSeguro(
        $id_atleta_req,
        $id_usuario,
        $esAtleta            
    );
        echo json_encode($sesiones);
        exit;
    } 

    
    require_once 'vista/cargaBienestar.php';
    exit;
}

// =====================================================================
// RUTAS POST (Transacciones ACID)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
    
    ob_start(); 

    if ($accion === 'registrar') {
        Autorizacion::exigir('rpe', 'registrar');
        $res = $objCarga->registrarRPE($_POST, $id_usuario, $rol_usuario);
        ob_end_clean();
        
        if ($res && is_array($res) && $res['exito']) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'INSERT', $res['id_rpe'], 'Nuevo registro RPE', null, json_encode($_POST));
            echo json_encode(['status' => 'success', 'message' => 'Registro RPE guardado con éxito.']);
        }else {
            $err = $objCarga->obtenerErrores();
            $msg = reset($err) ?: 'Error al guardar el registro.';

            if (stripos($msg, 'asistencia') !== false || esIntentoDeVulneracion($msg)) {
                Bitacora::registrar($id_usuario, 'RegistroRPE', 'INSERT', 0,
                    'Intento bloqueado al registrar', null, $msg);
                // ✅ AÑADIR ESTAS 2 LÍNEAS:
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => '⛔ ' . $msg]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $msg]);
            }
        } /* else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al guardar el registro.']);
        } */
        exit;
    }

    if ($accion === 'actualizar') {
        Autorizacion::exigir('rpe', 'editar');
        $id = (int)($_POST['id_rpe'] ?? 0);
        if ($id <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit;
        }
        $res = $objCarga->actualizarRPE($_POST, $id, $id_usuario, $rol_usuario);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'UPDATE', $id, 'Actualización de registro RPE', null, json_encode($_POST));
            echo json_encode(['status' => 'success', 'message' => 'Registro RPE actualizado.']);
        } else {
            $err = $objCarga->obtenerErrores();
            $msg = reset($err) ?: 'Error al actualizar.';

            if (esIntentoDeVulneracion($msg)) {
                // 🔴 VULNERACIÓN → 403 + bitácora
                Bitacora::registrar($id_usuario, 'RegistroRPE', 'UPDATE', $id, 'Intento bloqueado', null, $msg);
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => '⛔ ' . $msg]);
            } else {
                // 🟡 VALIDACIÓN → 200 con status:'error'
                echo json_encode(['status' => 'error', 'message' => $msg]);
            }
        }/* else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al actualizar.']);
        } */
        exit;
    }

    if ($accion === 'anularRPE') {
        Autorizacion::exigir('rpe', 'eliminar');
        $id = (int)($_POST['id_rpe'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        if ($id <= 0 || strlen($motivo) < 5) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Debe proveer un ID válido y un motivo justificado (mínimo 10 caracteres).']);
            exit;
        }
        $res = $objCarga->anularRPE($id, $motivo, $id_usuario, $rol_usuario);

        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'DELETE', $id, 'Movido a papelera', null, "Motivo: $motivo");
            echo json_encode(['status' => 'success', 'message' => 'Registro movido a la papelera.']);
        }else {
            $err = $objCarga->obtenerErrores();
            $msg = reset($err) ?: 'Error al anular.';

            if (esIntentoDeVulneracion($msg)) {
                Bitacora::registrar($id_usuario, 'RegistroRPE', 'DELETE', $id,
                    'Intento bloqueado', null, $msg);
                // ✅ AÑADIR ESTAS 2 LÍNEAS:
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => '⛔ ' . $msg]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $msg]);
            }
        }/* else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al anular.']);
        } */
        exit;
    }

    if ($accion === 'reactivarRPE') {
        Autorizacion::exigir('rpe', 'reactivar');
        $id = (int)($_POST['id_rpe'] ?? 0);
        if ($id <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit;
        }
        $res = $objCarga->reactivarRPE($id, $id_usuario, $rol_usuario);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'RESTORE', $id, 'Restaurado desde papelera', null, null);
            echo json_encode(['status' => 'success', 'message' => 'Registro reactivado exitosamente.']);
        }else {
            $err = $objCarga->obtenerErrores();
            $msg = reset($err) ?: 'Error al reactivar.';

            if (esIntentoDeVulneracion($msg)) {
                Bitacora::registrar($id_usuario, 'RegistroRPE', 'RESTORE', $id,
                    'Intento bloqueado', null, $msg);
                // ✅ AÑADIR ESTAS 2 LÍNEAS:
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => '⛔ ' . $msg]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $msg]);
            }
        } /* else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al reactivar.']);
        } */
        exit;
    }

    if ($accion === 'eliminarFisicoRPE') {
        Autorizacion::exigir('rpe', 'eliminardb');
        $id = (int)($_POST['id_rpe'] ?? 0);
        if ($id <= 0) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit;
        }
        $res = $objCarga->eliminarFisicoRPE($id, $id_usuario, $rol_usuario);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'RegistroRPE', 'DELETE', $id, 'Eliminación física', null, 'Registro borrado permanentemente');
            echo json_encode(['status' => 'success', 'message' => 'Registro eliminado físicamente del sistema.']);
        }else {
            $err = $objCarga->obtenerErrores();
            $msg = reset($err) ?: 'Error: No se pudo eliminar físicamente.';

            if (esIntentoDeVulneracion($msg)) {
                Bitacora::registrar($id_usuario, 'RegistroRPE', 'DELETE', $id,
                    'Intento bloqueado', null, $msg);
                // ✅ AÑADIR ESTAS 2 LÍNEAS:
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => '⛔ ' . $msg]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $msg]);
            }
        } /* else {
            $err = $objCarga->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error: No se pudo eliminar físicamente.']);
        } */
        exit;
    }

if ($accion === 'marcarRecomendacionLeida') {
    Autorizacion::exigir('rpe', 'ver_recomendaciones');
    $id_recomendacion = (int)($_POST['id_recomendacion'] ?? 0);
    if ($id_recomendacion <= 0) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'ID de recomendación inválido']);
        exit;
    }
    
    $res = $objCarga->marcarRecomendacionLeida($id_recomendacion);
    ob_end_clean();
    
    if ($res) {
        echo json_encode(['status' => 'success', 'message' => 'Recomendación marcada como leída']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar']);
    }
    exit;
}

    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Acción POST no soportada.']);
    exit;
}