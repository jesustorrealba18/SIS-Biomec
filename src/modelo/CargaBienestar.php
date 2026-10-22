<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class CargaBienestar extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

      public function __construct() {
        parent::__construct('sis_natacion');
    }
    
    private array $datos = [];
    private array $camposPermitidos = [
        'id_rpe', 'id_atleta', 'fecha', 'rpe', 'horas_sueno', 'calidad_sueno',
        'sensacion_muscular', 'estres_percibido', 'observaciones',
        'metros_nadados', 'duracion_minutos', 'srpe',
        'deleted_at', 'justificacion_softdelete','id_sesion'
    ];

   
    private function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo])) {
                if (is_array($payload[$campo])) {
                    $this->datos[$campo] = $payload[$campo];
                } elseif ($payload[$campo] !== '') {
                    $this->datos[$campo] = trim($payload[$campo]);
                } else {
                    $this->datos[$campo] = null;
                }
            } else {
                $this->datos[$campo] = null;
            }
        }
        if (!isset($this->datos['deleted_at'])) {
            $this->datos['deleted_at'] = null;
        }
    }

    private function validarAtributosInternos(bool $paraActualizacion = false): bool {
        $this->resetearErrores();

        if (!$paraActualizacion || isset($this->datos['id_atleta'])) {
            $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        }
        if (!$paraActualizacion || isset($this->datos['fecha'])) {
            $this->requerido((string)($this->datos['fecha'] ?? ''), 'fecha');
        }
        if (!$paraActualizacion || isset($this->datos['rpe'])) {
            $this->requerido((string)($this->datos['rpe'] ?? ''), 'rpe');
        }

        $rpe = $this->datos['rpe'] ?? null;
        if ($rpe !== null && $rpe !== '') {
            if (!is_numeric($rpe) || $rpe < 1 || $rpe > 10) {
                $this->agregarError('rpe', 'Debe ser un número entre 1 y 10.');
            }
        }


        if (!empty($this->datos['fecha'])) {
            $fechaMedicion = $this->datos['fecha'];
            $hoy = date('Y-m-d');
            $haceUnMes = date('Y-m-d', strtotime('-1 month'));

            if ($fechaMedicion > $hoy) {
                $this->agregarError('fecha', 'La fecha no puede ser futura.');
            } elseif ($fechaMedicion < $haceUnMes) {
                $this->agregarError('fecha', 'El sistema solo permite registrar evaluaciones con hasta 1 mes de antigüedad.');
            }
        }

        if (!empty($this->datos['duracion_minutos']) && (!is_numeric($this->datos['duracion_minutos']) || $this->datos['duracion_minutos'] < 0)) {
            $this->agregarError('duracion_minutos', 'La duración debe ser un número positivo.');
        }
        if (!empty($this->datos['metros_nadados']) && (!is_numeric($this->datos['metros_nadados']) || $this->datos['metros_nadados'] < 0)) {
            $this->agregarError('metros_nadados', 'Los metros nadados deben ser un número positivo.');
        }

        $bienestarCampos = ['calidad_sueno', 'sensacion_muscular', 'estres_percibido'];
        foreach ($bienestarCampos as $campo) {
            $valor = $this->datos[$campo] ?? null;
            if (!empty($valor) && (!is_numeric($valor) || $valor < 1 || $valor > 10)) {
                $this->agregarError($campo, 'Debe ser un número entre 1 y 10.');
            }
        }

        if (!empty($this->datos['horas_sueno']) && (!is_numeric($this->datos['horas_sueno']) || $this->datos['horas_sueno'] < 0 || $this->datos['horas_sueno'] > 24)) {
            $this->agregarError('horas_sueno', 'Las horas de sueño deben estar entre 0 y 24.');
        }

        return empty($this->obtenerErrores());
    }

   
    public function listarRPE(
    string $fechaInicio = '',
    string $fechaFin = '',
    int $atleta_id = 0,
    bool $modoPapelera = false,
    int $id_usuario = 0,
    string $rol = ''
): array {
    try {
        $sql = "SELECT r.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta,
                CASE 
                    WHEN r.rpe = 1 AND EXISTS (
                        SELECT 1 FROM marcas m
                        WHERE m.id_atleta = r.id_atleta
                          AND m.fecha = r.fecha
                          AND m.es_pb = 1
                    ) THEN 1 ELSE 0 
                END AS inconsistencia
                FROM registro_rpe r
                JOIN atletas a ON r.id_atleta = a.id_atleta
                WHERE 1=1";
        $params = [];

        if ($modoPapelera) {
            $sql .= " AND r.deleted_at IS NOT NULL";
        } else {
            $sql .= " AND r.deleted_at IS NULL";
        }

        if (strpos($rol, 'Atleta') !== false) {
            $sql .= " AND a.id_usuario = :id_usuario_seguridad";
            $params[':id_usuario_seguridad'] = $id_usuario;
        } elseif (strpos($rol, 'Representante') !== false) {
            $sql .= " AND a.id_atleta IN (
                        SELECT ar.id_atleta 
                        FROM atleta_representante ar 
                        JOIN representantes r ON ar.id_representante = r.id_representante 
                        WHERE r.id_usuario = :id_usuario_seguridad
                    )";
            $params[':id_usuario_seguridad'] = $id_usuario;
        }

        if ($atleta_id > 0) {
            $sql .= " AND r.id_atleta = :atleta";
            $params[':atleta'] = $atleta_id;
        }
        if (!empty($fechaInicio)) {
            $sql .= " AND r.fecha >= :fecha_ini";
            $params[':fecha_ini'] = $fechaInicio;
        }
        if (!empty($fechaFin)) {
            $sql .= " AND r.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fechaFin;
        }

        $sql .= " ORDER BY r.fecha DESC, a.apellidos ASC";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error listarRPE: " . $e->getMessage());
        return [];
    }
}

    

        public function obtenerRPEPorId(int $id_rpe, int $id_usuario = 0, string $rol = ''): ?array {
    try {
        $sql = "SELECT r.*, CONCAT(a.nombres,' ',a.apellidos) AS nombre_atleta
                FROM registro_rpe r
                JOIN atletas a ON r.id_atleta = a.id_atleta
                WHERE r.id_rpe = :id";
        $params = [':id' => $id_rpe];

        if (strpos($rol, 'Atleta') !== false && $id_usuario > 0) {
            $sql .= " AND a.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        } elseif (strpos($rol, 'Representante') !== false && $id_usuario > 0) {
            $sql .= " AND a.id_atleta IN (
                        SELECT ar.id_atleta FROM atleta_representante ar
                        JOIN representantes rep ON ar.id_representante = rep.id_representante
                        WHERE rep.id_usuario = :id_usuario
                      )";
            $params[':id_usuario'] = $id_usuario;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    } catch (PDOException $e) {
        error_log("Error obtenerRPEPorId: " . $e->getMessage());
        return null;
    }
}

   
    public function obtenerRpePromedioUltimosDias(int $id_atleta, int $dias = 3): ?float {
        try {
            $sql = "SELECT AVG(rpe) as promedio
                    FROM registro_rpe
                    WHERE id_atleta = :id_atleta
                      AND deleted_at IS NULL
                      AND fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado && $resultado['promedio'] !== null ? (float)$resultado['promedio'] : null;
        } catch (PDOException $e) {
            error_log("Error obtenerRpePromedioUltimosDias: " . $e->getMessage());
            return null;
        }
    }

   
    public function auditarInconsistenciasBiologicas(int $id_atleta): array {
        try {
            $sql = "SELECT r.id_registro, r.fecha_registro, m.prueba 
                    FROM registro_rpe r
                    INNER JOIN marcas m ON r.id_atleta = m.id_atleta AND r.fecha_registro = m.fecha_competencia
                    WHERE r.id_atleta = :id_atleta 
                    AND r.rpe = 1 
                    AND m.es_record = 1 
                    AND r.activo = 1";
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error auditando inconsistencias: " . $e->getMessage());
            return [];
        }
    }


public function registrarRPE(array $payload, int $id_usuario_logueado = 0, string $rol_logueado = ''): bool|array {
    $esAtleta = strpos($rol_logueado, 'Atleta') !== false;

    if ($esAtleta) {
        $id_atleta_real = $this->obtenerIdAtletaPorUsuario($id_usuario_logueado);
        if (!$id_atleta_real) {
            $this->agregarError('id_atleta', 'Usuario sin atleta vinculado. Contacta al administrador.');
            return false;
        }
        $payload['id_atleta'] = $id_atleta_real;   
    }

    if (!empty($payload['id_sesion'])) {
        $sesion = $this->obtenerSesionConAsistencia(
            (int)$payload['id_sesion'],
            (int)$payload['id_atleta']
        );
        if (!$sesion) {
            $this->agregarError('id_sesion',
                'La sesión no existe, no está completada o no tienes asistencia registrada.');
            return false;
        }
        $payload['fecha'] = $sesion['fecha'];  
    }


    $this->setAtributos($payload);

    if (!$this->validarAtributosInternos(false)) return false;

    if ($this->existeRegistroActivo(
        (int)$this->datos['id_atleta'],
        (int)($this->datos['id_sesion'] ?? 0)
    )) {
        $this->agregarError('id_sesion', 'Ya registraste tu RPE para esta sesión específica.');
        return false;
    }

    return $this->ejecutarRegistrarRPE();
}

private function obtenerSesionConAsistencia(int $id_sesion, int $id_atleta): ?array {
    try {
        $sql = "SELECT s.fecha, s.tipo_sesion, s.volumen_ejecutado, s.duracion_minutos
                FROM sesiones s
                INNER JOIN asistencia a ON a.id_sesion = s.id_sesion AND a.id_atleta = :atleta
                WHERE s.id_sesion = :sesion
                  AND s.estado = 'Completada'
                  AND a.estado IN ('Presente', 'Tardanza')
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sesion' => $id_sesion, ':atleta' => $id_atleta]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    } catch (PDOException $e) {
        error_log("Error obtenerSesionConAsistencia: " . $e->getMessage());
        return null;
    }
}


public function actualizarRPE(array $payload, int $id_rpe, int $id_usuario_logueado = 0, string $rol_logueado = ''): bool {
    $esAtleta = strpos($rol_logueado, 'Atleta') !== false;

    if ($esAtleta) {
        $id_atleta_real = $this->obtenerIdAtletaPorUsuario($id_usuario_logueado);
        if (!$id_atleta_real) {
            $this->agregarError('id_atleta', 'Usuario sin atleta vinculado.');
            return false;
        }
        $payload['id_atleta'] = $id_atleta_real;

        $registro = $this->obtenerRPEPorId($id_rpe);
        if (!$registro) {
            $this->agregarError('id_rpe', 'Registro no encontrado.');
            return false;
        }
        if ((int)$registro['id_atleta'] !== $id_atleta_real) {
            $this->agregarError('id_rpe', 'No tienes permiso para editar este registro.');
            return false;
        }
    }

     $registroActual = $this->obtenerRPEPorId($id_rpe);
    if ($registroActual) {
        if (empty($payload['id_sesion']) && !empty($registroActual['id_sesion'])) {
            $payload['id_sesion'] = $registroActual['id_sesion'];   // ← restaura el que había
        }
        if (!empty($payload['id_sesion'])) {
            $sesion = $this->obtenerSesionConAsistencia(
                (int)$payload['id_sesion'],
                (int)($payload['id_atleta'] ?? $registroActual['id_atleta'])
            );
            if ($sesion) {
                $payload['fecha'] = $sesion['fecha'];
            }
        }
    }


    $this->setAtributos($payload);
    $this->datos['id_rpe'] = $id_rpe;

    if (!$this->validarAtributosInternos(true)) return false;

    if (!$this->existeRegistroPorId($id_rpe)) {
        $this->agregarError('id_rpe', 'Registro no encontrado o ya está anulado.');
        return false;
    }

    if ($this->existeRegistroActivo(
        (int)$this->datos['id_atleta'],
        (int)($this->datos['id_sesion'] ?? 0),
        $id_rpe
    )) {
        $this->agregarError('id_sesion', 'Ya existe otro registro RPE activo para este atleta en esta sesión.');
        return false;
    }

    return $this->ejecutarActualizarRPE();
}

    

public function anularRPE(int $id_rpe, string $motivo, int $id_usuario = 0, string $rol = ''): bool {
    if (!$this->existeRegistroPorId($id_rpe)) {
        $this->agregarError('id_rpe', 'El registro no existe o ya está en la papelera.');
        return false;
    }
    if (!$this->esDuenoDelRegistro($id_rpe, $id_usuario, $rol)) {
        $this->agregarError('id_rpe', 'No tienes permiso sobre este registro.');
        return false;
    }
    return $this->ejecutarAnularRPE($id_rpe, trim($motivo));
}

public function reactivarRPE(int $id_rpe, int $id_usuario = 0, string $rol = ''): bool {
    if (!$this->existeRegistroEnPapelera($id_rpe)) {
        $this->agregarError('id_rpe', 'El registro no se encuentra en la papelera.');
        return false;
    }
    if (!$this->esDuenoDelRegistro($id_rpe, $id_usuario, $rol)) {
        $this->agregarError('id_rpe', 'No tienes permiso sobre este registro.');
        return false;
    }
    return $this->ejecutarReactivarRPE($id_rpe);
}

public function eliminarFisicoRPE(int $id_rpe, int $id_usuario = 0, string $rol = ''): bool {
    if (!$this->existeRegistroEnPapelera($id_rpe)) {
        $this->agregarError('id_rpe', 'Solo se pueden eliminar físicamente los registros de la papelera.');
        return false;
    }
    if (!$this->esDuenoDelRegistro($id_rpe, $id_usuario, $rol)) {
        $this->agregarError('id_rpe', 'No tienes permiso sobre este registro.');
        return false;
    }
    return $this->ejecutarEliminarFisicoRPE($id_rpe);
}

private function esDuenoDelRegistro(int $id_rpe, int $id_usuario, string $rol): bool {
    if (strpos($rol, 'Atleta') === false && strpos($rol, 'Representante') === false) {
        return true;
    }

    $reg = $this->obtenerRPEPorId($id_rpe);   
    if (!$reg) return false;

    if (strpos($rol, 'Atleta') !== false) {
        $id_real = $this->obtenerIdAtletaPorUsuario($id_usuario);
        return $id_real && (int)$reg['id_atleta'] === $id_real;
    }

    if (strpos($rol, 'Representante') !== false) {
        $sql = "SELECT 1 FROM atleta_representante ar
                JOIN representantes rep ON ar.id_representante = rep.id_representante
                WHERE rep.id_usuario = :u AND ar.id_atleta = :a LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':u' => $id_usuario, ':a' => $reg['id_atleta']]);
        return (bool) $stmt->fetch();
    }
    return false;
}

    
    private function ejecutarRegistrarRPE(): bool|array {
        try {
            $this->pdo->beginTransaction();

            $srpe = null;
            if (!empty($this->datos['duracion_minutos']) && !empty($this->datos['rpe'])) {
                $srpe = (int)$this->datos['rpe'] * (int)$this->datos['duracion_minutos'];
            }
            $this->datos['srpe'] = $srpe;

            $sql = "INSERT INTO registro_rpe (
                        id_atleta, fecha, rpe, id_sesion, horas_sueno, calidad_sueno,
                        sensacion_muscular, estres_percibido, observaciones,
                        metros_nadados, duracion_minutos, srpe, fecha_creacion
                    ) VALUES (
                        :id_atleta, :fecha, :rpe, :id_sesion, :horas_sueno, :calidad_sueno,
                        :sensacion, :estres, :observaciones,
                        :metros, :duracion, :srpe, NOW()
                    )";
            
            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'     => ['id_atleta', PDO::PARAM_INT],
                ':fecha'         => ['fecha', PDO::PARAM_STR],
                ':rpe'           => ['rpe', PDO::PARAM_INT],
                ':id_sesion'           => ['id_sesion', PDO::PARAM_INT],
                ':horas_sueno'   => ['horas_sueno', PDO::PARAM_STR],
                ':calidad_sueno' => ['calidad_sueno', PDO::PARAM_INT],
                ':sensacion'     => ['sensacion_muscular', PDO::PARAM_INT],
                ':estres'        => ['estres_percibido', PDO::PARAM_INT],
                ':observaciones' => ['observaciones', PDO::PARAM_STR],
                ':metros'        => ['metros_nadados', PDO::PARAM_INT],
                ':duracion'      => ['duracion_minutos', PDO::PARAM_INT],
                ':srpe'          => ['srpe', PDO::PARAM_INT]
            ];
            $this->autoBind($stmt, $mapa, $this->datos);
            $stmt->execute();
            
            $id_insertado = (int) $this->pdo->lastInsertId();
            
            $this->generarRecomendacionCarga((int)$this->datos['id_atleta']);
            $this->pdo->commit();
            
            return ['exito' => true, 'id_rpe' => $id_insertado, 'mensaje' => 'Registro RPE guardado correctamente.'];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error registrarRPE SQL: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al guardar el registro.');
            return false;
        }
    }

    private function ejecutarActualizarRPE(): bool {
        try {
            $this->pdo->beginTransaction();

            $srpe = null;
            if (!empty($this->datos['duracion_minutos']) && !empty($this->datos['rpe'])) {
                $srpe = (int)$this->datos['rpe'] * (int)$this->datos['duracion_minutos'];
            }
            $this->datos['srpe'] = $srpe;

            $sql = "UPDATE registro_rpe SET
                        id_atleta = :id_atleta, fecha = :fecha, rpe = :rpe,id_sesion = :id_sesion,
                        horas_sueno = :horas_sueno, calidad_sueno = :calidad_sueno,
                        sensacion_muscular = :sensacion, estres_percibido = :estres,
                        observaciones = :observaciones, metros_nadados = :metros,
                        duracion_minutos = :duracion, srpe = :srpe
                    WHERE id_rpe = :id";
                    
            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'     => ['id_atleta', PDO::PARAM_INT],
                ':fecha'         => ['fecha', PDO::PARAM_STR],
                ':rpe'           => ['rpe', PDO::PARAM_INT],
                ':id_sesion'           => ['id_sesion', PDO::PARAM_INT],
                ':horas_sueno'   => ['horas_sueno', PDO::PARAM_STR],
                ':calidad_sueno' => ['calidad_sueno', PDO::PARAM_INT],
                ':sensacion'     => ['sensacion_muscular', PDO::PARAM_INT],
                ':estres'        => ['estres_percibido', PDO::PARAM_INT],
                ':observaciones' => ['observaciones', PDO::PARAM_STR],
                ':metros'        => ['metros_nadados', PDO::PARAM_INT],
                ':duracion'      => ['duracion_minutos', PDO::PARAM_INT],
                ':srpe'          => ['srpe', PDO::PARAM_INT],
                ':id'            => ['id_rpe', PDO::PARAM_INT]
            ];
            $this->autoBind($stmt, $mapa, $this->datos);
            $stmt->execute();
            
            $this->generarRecomendacionCarga((int)$this->datos['id_atleta']);
            $this->pdo->commit();
            
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error actualizarRPE SQL: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al actualizar el registro.');
            return false;
        }
    }

    private function ejecutarAnularRPE(int $id_rpe, string $motivo): bool {
        try {
            $sql = "UPDATE registro_rpe 
                    SET deleted_at = NOW(), justificacion_softdelete = :motivo 
                    WHERE id_rpe = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_rpe, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error ejecutarAnularRPE SQL: " . $e->getMessage());
            $this->agregarError('bd', 'Error de base de datos al anular.');
            return false;
        }
    }

    private function ejecutarReactivarRPE(int $id_rpe): bool {
        try {
            $sql = "UPDATE registro_rpe 
                    SET deleted_at = NULL, justificacion_softdelete = NULL 
                    WHERE id_rpe = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id_rpe, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error ejecutarReactivarRPE SQL: " . $e->getMessage());
            $this->agregarError('bd', 'Error de base de datos al reactivar.');
            return false;
        }
    }

    private function ejecutarEliminarFisicoRPE(int $id_rpe): bool {
        try {
            $sql = "DELETE FROM registro_rpe WHERE id_rpe = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id_rpe, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error ejecutarEliminarFisicoRPE SQL: " . $e->getMessage());
            $this->agregarError('bd', 'Error de base de datos al eliminar.');
            return false;
        }
    }

   
private function existeRegistroActivo(int $id_atleta, int $id_sesion, int $id_excluir = 0): bool {
    if ($id_sesion <= 0) return false; 

    $sql = "SELECT id_rpe FROM registro_rpe 
            WHERE id_atleta = :atleta 
            AND id_sesion = :id_sesion 
            AND id_rpe != :id_excluir 
            AND deleted_at IS NULL";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':atleta' => $id_atleta, ':id_sesion' => $id_sesion, ':id_excluir' => $id_excluir]);
    return (bool) $stmt->fetch();
}

    private function existeRegistroPorId(int $id_rpe): bool {
        $sql = "SELECT id_rpe FROM registro_rpe WHERE id_rpe = :id AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id_rpe]);
        return (bool) $stmt->fetch();
    }

    private function existeRegistroEnPapelera(int $id_rpe): bool {
        $sql = "SELECT id_rpe FROM registro_rpe WHERE id_rpe = :id AND deleted_at IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id_rpe]);
        return (bool) $stmt->fetch();
    }

   
    public function guardarRPE(array $datos): bool {
        $resultado = $this->registrarRPE($datos);
        return is_array($resultado) ? $resultado['exito'] : $resultado;
    }

   
    public function actualizarRPEPlano(array $datos): bool {
        $id = $datos['id_rpe'] ?? 0;
        return $this->actualizarRPE($datos, $id);
    }

    public function listarInconsistencias(): array {
        try {
            $sql = "SELECT 
                        r.id_rpe,
                        r.fecha,
                        r.rpe,
                        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta,
                        m.estilo,
                        m.distancia_m,
                        m.tiempo_final_seg AS marca_segundos
                    FROM registro_rpe r
                    INNER JOIN atletas a ON r.id_atleta = a.id_atleta
                    INNER JOIN marcas m ON m.id_atleta = r.id_atleta AND DATE(m.fecha) = r.fecha
                    WHERE r.rpe = 1 
                      AND m.es_pb = 1
                      AND r.deleted_at IS NULL
                    ORDER BY r.fecha DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarInconsistencias: " . $e->getMessage());
            return [];
        }
    }

   
    public function calcularSRPEUltimosDias(int $id_atleta, int $dias = 7): ?int {
        $sql = "SELECT SUM(srpe) AS total
                FROM registro_rpe
                WHERE id_atleta = :id_atleta
                  AND deleted_at IS NULL
                  AND fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_atleta' => $id_atleta, ':dias' => $dias]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res && $res['total'] !== null ? (int)$res['total'] : null;
    }

   
    public function calcularSuenoPromedioUltimosDias(int $id_atleta, int $dias = 3): ?float {
        $sql = "SELECT AVG(horas_sueno) AS promedio
                FROM registro_rpe
                WHERE id_atleta = :id_atleta
                  AND deleted_at IS NULL
                  AND fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                  AND horas_sueno IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_atleta' => $id_atleta, ':dias' => $dias]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res && $res['promedio'] !== null ? (float)$res['promedio'] : null;
    }

   
    public function generarRecomendacionCarga(int $id_atleta): void {
        $srpeSemanal = $this->calcularSRPEUltimosDias($id_atleta, 7);
        $rpePromedio = $this->obtenerRpePromedioUltimosDias($id_atleta, 3);
        $suenoPromedio = $this->calcularSuenoPromedioUltimosDias($id_atleta, 3);

        $tipo = null;
        $mensaje = null;

        if ($srpeSemanal !== null && $srpeSemanal > 600) {
            $tipo = 'SOBRECARGA';
            $mensaje = "Carga subjetiva semanal muy alta ({$srpeSemanal} sRPE). Se recomienda reducir volumen en un 20% y priorizar sesiones regenerativas.";
        } elseif ($rpePromedio !== null && $suenoPromedio !== null && $rpePromedio > 7 && $suenoPromedio < 6) {
            $tipo = 'RECUPERACION';
            $mensaje = "RPE elevado (promedio {$rpePromedio}) con sueño insuficiente (promedio {$suenoPromedio}h). Evaluar fatiga y considerar descanso activo.";
        }

        if ($mensaje) {
            $sql = "INSERT INTO recomendaciones_carga (id_atleta, tipo, mensaje) VALUES (:id_atleta, :tipo, :mensaje)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_atleta' => $id_atleta,
                ':tipo'      => $tipo,
                ':mensaje'   => $mensaje
            ]);
        }
    }

    
    public function obtenerRecomendacionesPorEntrenador(int $id_usuario): array {
        try {
            $sql = "SELECT DISTINCT
                            r.*, 
                            CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta
                        FROM recomendaciones_carga r
                        INNER JOIN atletas a ON r.id_atleta = a.id_atleta
                        INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                        INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                        INNER JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                        WHERE e.id_usuario = :id_usuario
                          AND r.leida = 0
                        ORDER BY r.fecha DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtenerRecomendacionesPorEntrenador: " . $e->getMessage());
            return [];
        }
    }

    
    public function marcarRecomendacionLeida(int $id_recomendacion): bool {
        try {
            $sql = "UPDATE recomendaciones_carga SET leida = TRUE WHERE id_recomendacion = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id_recomendacion]);
        } catch (PDOException $e) {
            error_log("Error marcarRecomendacionLeida: " . $e->getMessage());
            return false;
        }
    }



public function obtenerSesionesPendientesSeguro(
    int $id_atleta_solicitado,
    int $id_usuario_logueado,
    bool $esAtleta             
): array {
    $id_atleta_final = $id_atleta_solicitado;

    if ($esAtleta) {
        $id_real = $this->obtenerIdAtletaPorUsuario($id_usuario_logueado);
        if (!$id_real) {
            error_log("RPE: usuario {$id_usuario_logueado} es Atleta pero no tiene atleta vinculado");
            return [];
        }
        $id_atleta_final = $id_real;
    }

    if ($id_atleta_final <= 0) return [];

    return $this->obtenerSesionesPendientes($id_atleta_final);
}

   

private function obtenerSesionesPendientes(int $id_atleta): array {
    try {
        $sql = "SELECT 
                    s.id_sesion, 
                    s.fecha, 
                    s.tipo_sesion, 
                    s.volumen_ejecutado, 
                    s.duracion_minutos,
                    s.observaciones AS obs_sesion
                FROM sesiones s
                INNER JOIN asistencia asis 
                    ON asis.id_sesion = s.id_sesion
                WHERE asis.id_atleta = :id_atleta
                  AND s.estado = 'Completada'
                  AND asis.estado IN ('Presente', 'Tardanza')
                  AND NOT EXISTS (
                      SELECT 1 FROM registro_rpe rpe
                      WHERE rpe.id_atleta  = asis.id_atleta
                        AND rpe.id_sesion  = s.id_sesion
                        AND rpe.deleted_at IS NULL
                  )
                ORDER BY s.fecha DESC 
                LIMIT 15";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_atleta' => $id_atleta]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // error_log(">>> RPE: sesiones atleta=$id_atleta → " . count($res));
        return $res;
    } catch (PDOException $e) {
        error_log(">>> RPE ERROR obtenerSesionesPendientes: " . $e->getMessage());
        return [];
    }
}

private function obtenerIdAtletaPorUsuario(int $id_usuario): ?int {
    $sql = "SELECT id_atleta FROM atletas WHERE id_usuario = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $id_usuario]);
    $res = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $res ? (int)$res['id_atleta'] : null;
}


}