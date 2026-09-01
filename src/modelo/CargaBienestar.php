<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class CargaBienestar extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    // =================================================================
    // 1. CAMPOS PERMITIDOS (incluyen soft delete)
    // =================================================================
    private array $datos = [];
    private array $camposPermitidos = [
        'id_rpe', 'id_atleta', 'fecha', 'rpe', 'horas_sueno', 'calidad_sueno',
        'sensacion_muscular', 'estres_percibido', 'observaciones',
        'metros_nadados', 'duracion_minutos', 'srpe',
        'deleted_at', 'justificacion_softdelete'
    ];

    // =================================================================
    // 2. HIDRATACIÓN Y VALIDACIÓN INTERNA
    // =================================================================
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
        // Por defecto, el registro se crea como activo (no eliminado)
        if (!isset($this->datos['deleted_at'])) {
            $this->datos['deleted_at'] = null;
        }
    }

    private function validarAtributosInternos(bool $paraActualizacion = false): bool {
        $this->resetearErrores();

        // Obligatorios
        if (!$paraActualizacion || isset($this->datos['id_atleta'])) {
            $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        }
        if (!$paraActualizacion || isset($this->datos['fecha'])) {
            $this->requerido((string)($this->datos['fecha'] ?? ''), 'fecha');
        }
        if (!$paraActualizacion || isset($this->datos['rpe'])) {
            $this->requerido((string)($this->datos['rpe'] ?? ''), 'rpe');
        }

        // RPE entre 1 y 10
        $rpe = $this->datos['rpe'] ?? null;
        if ($rpe !== null && $rpe !== '') {
            if (!is_numeric($rpe) || $rpe < 1 || $rpe > 10) {
                $this->agregarError('rpe', 'Debe ser un número entre 1 y 10.');
            }
        }

        // Fecha no futura
        if (!empty($this->datos['fecha']) && $this->datos['fecha'] > date('Y-m-d')) {
            $this->agregarError('fecha', 'La fecha no puede ser futura.');
        }

        // Duración y metros (positivos)
        if (!empty($this->datos['duracion_minutos']) && (!is_numeric($this->datos['duracion_minutos']) || $this->datos['duracion_minutos'] < 0)) {
            $this->agregarError('duracion_minutos', 'La duración debe ser un número positivo.');
        }
        if (!empty($this->datos['metros_nadados']) && (!is_numeric($this->datos['metros_nadados']) || $this->datos['metros_nadados'] < 0)) {
            $this->agregarError('metros_nadados', 'Los metros nadados deben ser un número positivo.');
        }

        // Campos de bienestar (1-10)
        $bienestarCampos = ['calidad_sueno', 'sensacion_muscular', 'estres_percibido'];
        foreach ($bienestarCampos as $campo) {
            $valor = $this->datos[$campo] ?? null;
            if (!empty($valor) && (!is_numeric($valor) || $valor < 1 || $valor > 10)) {
                $this->agregarError($campo, 'Debe ser un número entre 1 y 10.');
            }
        }

        // Horas de sueño (0-24)
        if (!empty($this->datos['horas_sueno']) && (!is_numeric($this->datos['horas_sueno']) || $this->datos['horas_sueno'] < 0 || $this->datos['horas_sueno'] > 24)) {
            $this->agregarError('horas_sueno', 'Las horas de sueño deben estar entre 0 y 24.');
        }

        return empty($this->obtenerErrores());
    }

    // =================================================================
    // 3. OPERACIONES DE LECTURA
    // =================================================================

    /**
     * Lista registros RPE con filtros (similar a listarLesiones)
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int $atleta_id
     * @param bool $modoPapelera true = solo eliminados, false = solo activos
     * @return array
     */
    public function listarRPE(string $fechaInicio = '', string $fechaFin = '', int $atleta_id = 0, bool $modoPapelera = false): array {
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

            // Filtro por estado (activo/papelera)
            if ($modoPapelera) {
                $sql .= " AND r.deleted_at IS NOT NULL";
            } else {
                $sql .= " AND r.deleted_at IS NULL";
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

    /**
     * Obtiene un registro RPE por ID (incluye datos del atleta)
     */
    public function obtenerRPEPorId(int $id_rpe): ?array {
        try {
            $sql = "SELECT r.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta
                    FROM registro_rpe r
                    JOIN atletas a ON r.id_atleta = a.id_atleta
                    WHERE r.id_rpe = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id_rpe, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (PDOException $e) {
            error_log("Error obtenerRPEPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el RPE promedio de los últimos N días (solo activos)
     */
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

   /**
     * Detecta inconsistencias cruzando RPE = 1 con récords en marcas el mismo día.
     */
    public function auditarInconsistenciasBiologicas(int $id_atleta): array {
        try {
            // Unimos el módulo de salud con el módulo de rendimiento (RF-06)
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

    // =================================================================
    // 4. OPERACIONES DE ESCRITURA (ACID)
    // =================================================================

    /**
     * Registra un nuevo RPE (calcula sRPE automáticamente si hay duración)
     */
    public function registrarRPE(array $payload): bool|array {
        $this->setAtributos($payload);
        if (!$this->validarAtributosInternos(false)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // Verificar duplicado (mismo atleta y fecha, activo)
            $checkSql = "SELECT id_rpe FROM registro_rpe 
                         WHERE id_atleta = :atleta AND fecha = :fecha AND deleted_at IS NULL";
            $check = $this->pdo->prepare($checkSql);
            $check->execute([':atleta' => $this->datos['id_atleta'], ':fecha' => $this->datos['fecha']]);
            if ($check->fetch()) {
                $this->agregarError('fecha', 'Ya existe un registro RPE activo para este atleta en esta fecha.');
                $this->pdo->rollBack();
                return false;
            }

            $srpe = null;
            if (isset($this->datos['duracion_minutos']) && $this->datos['duracion_minutos'] !== '' && 
            isset($this->datos['rpe']) && $this->datos['rpe'] !== '') {
            $srpe = (int)$this->datos['rpe'] * (int)$this->datos['duracion_minutos'];
            }

            $this->datos['srpe'] = $srpe;

            $sql = "INSERT INTO registro_rpe (
                        id_atleta, fecha, rpe, horas_sueno, calidad_sueno,
                        sensacion_muscular, estres_percibido, observaciones,
                        metros_nadados, duracion_minutos, srpe, fecha_creacion
                    ) VALUES (
                        :id_atleta, :fecha, :rpe, :horas_sueno, :calidad_sueno,
                        :sensacion, :estres, :observaciones,
                        :metros, :duracion, :srpe, NOW()
                    )";
            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'     => ['id_atleta', PDO::PARAM_INT],
                ':fecha'         => ['fecha', PDO::PARAM_STR],
                ':rpe'           => ['rpe', PDO::PARAM_INT],
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
            $id_insertado = $this->pdo->lastInsertId();
            $this->generarRecomendacionCarga($this->datos['id_atleta']);
            $this->pdo->commit();
            
            return ['exito' => true, 'id_rpe' => $id_insertado, 'mensaje' => 'Registro RPE guardado correctamente.'];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error registrarRPE: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al guardar el registro.');
            return false;
        }
    }

    /**
     * Actualiza un registro RPE existente (solo si está activo)
     */
    public function actualizarRPE(array $payload, int $id_rpe): bool {
        $this->setAtributos($payload);
        $this->datos['id_rpe'] = $id_rpe;
        if (!$this->validarAtributosInternos(true)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // Verificar que el registro exista y esté activo
            $checkSql = "SELECT id_rpe FROM registro_rpe WHERE id_rpe = :id AND deleted_at IS NULL";
            $check = $this->pdo->prepare($checkSql);
            $check->execute([':id' => $id_rpe]);
            if (!$check->fetch()) {
                $this->agregarError('id_rpe', 'Registro no encontrado o ya está anulado.');
                $this->pdo->rollBack();
                return false;
            }

            // Verificar duplicado (mismo atleta y fecha, diferente ID)
            $dupSql = "SELECT id_rpe FROM registro_rpe 
                       WHERE id_atleta = :atleta AND fecha = :fecha 
                         AND id_rpe != :id AND deleted_at IS NULL";
            $dup = $this->pdo->prepare($dupSql);
            $dup->execute([
                ':atleta' => $this->datos['id_atleta'],
                ':fecha'  => $this->datos['fecha'],
                ':id'     => $id_rpe
            ]);
            if ($dup->fetch()) {
                $this->agregarError('fecha', 'Ya existe otro registro RPE activo para este atleta en esta fecha.');
                $this->pdo->rollBack();
                return false;
            }

            $srpe = null;
            if (!empty($this->datos['duracion_minutos']) && !empty($this->datos['rpe'])) {
                $srpe = (int)$this->datos['rpe'] * (int)$this->datos['duracion_minutos'];
            }

            $this->datos['srpe'] = $srpe;

            $sql = "UPDATE registro_rpe SET
                        id_atleta = :id_atleta,
                        fecha = :fecha,
                        rpe = :rpe,
                        horas_sueno = :horas_sueno,
                        calidad_sueno = :calidad_sueno,
                        sensacion_muscular = :sensacion,
                        estres_percibido = :estres,
                        observaciones = :observaciones,
                        metros_nadados = :metros,
                        duracion_minutos = :duracion,
                        srpe = :srpe
                    WHERE id_rpe = :id";
            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'     => ['id_atleta', PDO::PARAM_INT],
                ':fecha'         => ['fecha', PDO::PARAM_STR],
                ':rpe'           => ['rpe', PDO::PARAM_INT],
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
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('actualizacion', 'No se realizaron cambios o el registro no existe.');
                return false;
            }

            $this->generarRecomendacionCarga($this->datos['id_atleta']);
            $this->pdo->commit();
            
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error actualizarRPE: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al actualizar el registro.');
            return false;
        }
    }

    // =================================================================
    // 5. SOFT DELETE, REACTIVACIÓN Y ELIMINACIÓN FÍSICA
    // =================================================================

    /**
     * Soft delete: mueve el registro a la papelera (deleted_at = NOW()) y guarda justificación.
     */
    public function anularRPE(int $id_rpe, string $motivo): bool {
        try {
            $sql = "UPDATE registro_rpe 
                    SET deleted_at = NOW(), justificacion_softdelete = :motivo 
                    WHERE id_rpe = :id AND deleted_at IS NULL";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':motivo', trim($motivo), PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_rpe, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error anularRPE: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reactiva un registro previamente anulado (borra deleted_at y justificación).
     */
    public function reactivarRPE(int $id_rpe): bool {
        try {
            $sql = "UPDATE registro_rpe 
                    SET deleted_at = NULL, justificacion_softdelete = NULL 
                    WHERE id_rpe = :id AND deleted_at IS NOT NULL";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id_rpe, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error reactivarRPE: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminación física permanente (solo registros en papelera, deleted_at IS NOT NULL)
     */
    public function eliminarFisicoRPE(int $id_rpe): bool {
        try {
            $sql = "DELETE FROM registro_rpe WHERE id_rpe = :id AND deleted_at IS NOT NULL";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id_rpe, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error eliminarFisicoRPE: " . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // 6. MÉTODOS AUXILIARES (compatibilidad con frontend)
    // =================================================================

    /**
     * Alias de registrarRPE para mantener compatibilidad con frontend existente.
     */
    public function guardarRPE(array $datos): bool {
        $resultado = $this->registrarRPE($datos);
        return is_array($resultado) ? $resultado['exito'] : $resultado;
    }

    /**
     * Alias de actualizarRPE con parámetros planos (para compatibilidad).
     */
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


/**
 * Calcula el sRPE total de los últimos N días (solo activos)
 */
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

/**
 * Promedio RPE últimos 3 días (ya lo tenías como obtenerRpePromedioUltimosDias)
 * Puedes reutilizarlo.
 */

/**
 * Promedio de horas de sueño últimos 3 días
 */
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

/**
 * Evalúa la carga del atleta y genera una recomendación si es necesario
 * (escribiendo en la tabla recomendaciones_carga)
 */
public function generarRecomendacionCarga(int $id_atleta): void {
    // 1. Extraer métricas
    $srpeSemanal = $this->calcularSRPEUltimosDias($id_atleta, 7);
    $rpePromedio = $this->obtenerRpePromedioUltimosDias($id_atleta, 3);
    $suenoPromedio = $this->calcularSuenoPromedioUltimosDias($id_atleta, 3);

    $tipo = null;
    $mensaje = null;

    // 2. Procesar (reglas de negocio)
    if ($srpeSemanal !== null && $srpeSemanal > 600) {
        $tipo = 'SOBRECARGA';
        $mensaje = "Carga subjetiva semanal muy alta ({$srpeSemanal} sRPE). Se recomienda reducir volumen en un 20% y priorizar sesiones regenerativas.";
    } elseif ($rpePromedio !== null && $suenoPromedio !== null && $rpePromedio > 7 && $suenoPromedio < 6) {
        $tipo = 'RECUPERACION';
        $mensaje = "RPE elevado (promedio {$rpePromedio}) con sueño insuficiente (promedio {$suenoPromedio}h). Evaluar fatiga y considerar descanso activo.";
    }

    // 3. Si hay recomendación, escribir en la nueva tabla
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

    /**
     * Obtiene recomendaciones no leídas de los atletas asignados a un entrenador
     */
    public function obtenerRecomendacionesPorEntrenador(int $id_usuario): array {
        try {
            /* $sql = "SELECT r.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta
                    FROM recomendaciones_carga r
                    JOIN atletas a ON r.id_atleta = a.id_atleta
                    JOIN entrenador_asignacion ea ON ea.id_atleta = a.id_atleta
                    WHERE ea.id_usuario = :id_usuario
                    AND r.leida = FALSE
                    ORDER BY r.fecha DESC"; */
                   /*  $sql = "SELECT 
                        r.*, 
                        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta
                    FROM recomendaciones_carga r
                    INNER JOIN atletas a ON r.id_atleta = a.id_atleta
                    INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                    INNER JOIN grupos g ON ga.id_grupo = g.id_grupo
                    WHERE g.id_usuario = :id_usuario
                      AND r.leida = FALSE
                    ORDER BY r.fecha DESC"; */

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

    /**
 * Marca una recomendación como leída
 */
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


}