<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Lesion extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    // =================================================================
    // 1. CAMPOS PERMITIDOS (incluyen los dos estados y motivo)
    // =================================================================
    private array $datos = [];
    private array $camposPermitidos = [
        'id_lesion', 'id_atleta', 'zona_anatomica', 'lado', 'tipo',
        'nivel_molestia', 'diagnostico', 'tratamiento', 'fecha_inicio',
        'fecha_estimada_recup', 'estado', 'profesional', 'observaciones',
        'activo', 'motivo_eliminacion'
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
        // Por defecto, el registro se crea como activo (visible)
        if (!isset($this->datos['activo'])) {
            $this->datos['activo'] = 1;
        }
    }

    private function validarAtributosInternos(bool $paraActualizacion = false): bool {
        $this->resetearErrores();

        // Validaciones de campos obligatorios (excepto en actualización parcial)
        if (!$paraActualizacion || isset($this->datos['id_atleta'])) {
            $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        }
        if (!$paraActualizacion || isset($this->datos['fecha_inicio'])) {
            $this->requerido((string)($this->datos['fecha_inicio'] ?? ''), 'fecha_inicio');
        }
        if (!$paraActualizacion || isset($this->datos['zona_anatomica'])) {
            $this->requerido((string)($this->datos['zona_anatomica'] ?? ''), 'zona_anatomica');
        }
        if (!$paraActualizacion || isset($this->datos['tipo'])) {
            $this->requerido((string)($this->datos['tipo'] ?? ''), 'tipo');
        }

        // Nivel de molestia (1-10)
        $valor = $this->datos['nivel_molestia'] ?? null;
        if ($valor === null || $valor === '') {
            $this->agregarError('nivel_molestia', 'El nivel de molestia es obligatorio.');
        } elseif (!is_numeric($valor) || $valor < 1 || $valor > 10) {
            $this->agregarError('nivel_molestia', 'Debe ser un número entre 1 y 10.');
        }

        // Fechas
        if (!empty($this->datos['fecha_inicio']) && $this->datos['fecha_inicio'] > date('Y-m-d')) {
            $this->agregarError('fecha_inicio', 'La fecha de inicio no puede ser futura.');
        }
        if (!empty($this->datos['fecha_estimada_recup']) && !empty($this->datos['fecha_inicio'])
            && $this->datos['fecha_estimada_recup'] < $this->datos['fecha_inicio']) {
            $this->agregarError('fecha_estimada_recup', 'No puede ser anterior a la fecha de inicio.');
        }

        // Enums
        $zonasValidas = ['Hombro','Rodilla','Espalda','Codo','Tobillo','Cervical','Lumbar','Muslo','Gemelo','Pie','Otra'];
        if (!empty($this->datos['zona_anatomica']) && !in_array($this->datos['zona_anatomica'], $zonasValidas)) {
            $this->agregarError('zona_anatomica', 'Zona anatómica no válida.');
        }

        $ladosValidos = ['Izquierdo','Derecho','Bilateral'];
        if (!empty($this->datos['lado']) && !in_array($this->datos['lado'], $ladosValidos)) {
            $this->agregarError('lado', 'Lado no válido.');
        }

        $tiposValidos = ['Sobreuso','Aguda','Recidiva'];
        if (!empty($this->datos['tipo']) && !in_array($this->datos['tipo'], $tiposValidos)) {
            $this->agregarError('tipo', 'Tipo de lesión no válido.');
        }

        $estadosClinicos = ['Activa','EnRehabilitacion','Recuperada','Cronica'];
        if (!empty($this->datos['estado']) && !in_array($this->datos['estado'], $estadosClinicos)) {
            $this->agregarError('estado', 'Estado clínico no válido.');
        }

        return empty($this->obtenerErrores());
    }

    // =================================================================
    // 3. OPERACIONES DE LECTURA (solo registros activos por defecto)
    // =================================================================

    /**
     * Lista lesiones con filtros. Por defecto solo registros activos (activo=1).
     * Si $incluirInactivos = true, muestra también los de papelera.
     */
   /*  public function listarLesiones(string $estadoClinico = '', int $id_atleta = 0, string $tipo = '', string $zona = '', bool $incluirInactivos = false): array {
        try {
            $sql = "SELECT l.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta 
                    FROM lesiones l
                    INNER JOIN atletas a ON l.id_atleta = a.id_atleta
                    WHERE 1=1";
            $params = [];

            if (!$incluirInactivos) {
                $sql .= " AND l.activo = 1";
            }

            if (!empty($estadoClinico)) {
                $sql .= " AND l.estado = :estado";
                $params[':estado'] = $estadoClinico;
            }
            if ($id_atleta > 0) {
                $sql .= " AND l.id_atleta = :id_atleta";
                $params[':id_atleta'] = $id_atleta;
            }
            if (!empty($tipo)) {
                $sql .= " AND l.tipo = :tipo";
                $params[':tipo'] = $tipo;
            }
            if (!empty($zona)) {
                $sql .= " AND l.zona_anatomica = :zona";
                $params[':zona'] = $zona;
            }

            $sql .= " ORDER BY l.fecha_inicio DESC";
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarLesiones: " . $e->getMessage());
            return [];
        }
    } */

    public function listarLesiones(string $estadoClinico = '', int $id_atleta = 0, string $tipo = '', string $zona = '', bool $modoPapelera = false): array {
        try {
            $sql = "SELECT l.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta 
                    FROM lesiones l
                    INNER JOIN atletas a ON l.id_atleta = a.id_atleta
                    WHERE 1=1";
            $params = [];

            // =======================================================
            // LÓGICA CORREGIDA PARA EL MODO PAPELERA (Eliminado Lógico)
            // =======================================================
            if ($modoPapelera) {
                // Si estamos en papelera, traemos SOLO los inactivos
                $sql .= " AND l.activo = 0";
            } else {
                // Modo normal, traemos SOLO los activos
                $sql .= " AND l.activo = 1";
            }

            if (!empty($estadoClinico)) {
                $sql .= " AND l.estado = :estado";
                $params[':estado'] = $estadoClinico;
            }
            if ($id_atleta > 0) {
                $sql .= " AND l.id_atleta = :id_atleta";
                $params[':id_atleta'] = $id_atleta;
            }
            if (!empty($tipo)) {
                $sql .= " AND l.tipo = :tipo";
                $params[':tipo'] = $tipo;
            }
            if (!empty($zona)) {
                $sql .= " AND l.zona_anatomica = :zona";
                $params[':zona'] = $zona;
            }

            $sql .= " ORDER BY l.fecha_inicio DESC";
            
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            // Registro de error fundamental para las pruebas de caja blanca
            error_log("Error en listarLesiones: " . $e->getMessage());
            return [];
        }
    }

 

public function obtenerDetallePorId(int $id_lesion): ?array {
    try {
        $sql = "SELECT l.*, a.nombres, a.apellidos, a.cedula
                FROM lesiones l
                INNER JOIN atletas a ON l.id_atleta = a.id_atleta
                WHERE l.id_lesion = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id_lesion, PDO::PARAM_INT);
        $stmt->execute();
        $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$detalle) return null;

        // =========================================================
        // CONSULTA REAL DE RPE DESDE LA TABLA registro_rpe
        // =========================================================
        $fechaInicio = $detalle['fecha_inicio'];
        // Rango de 15 días antes y 15 días después (para la gráfica)
        $fechaInicioRango = date('Y-m-d', strtotime($fechaInicio . ' -15 days'));
        $fechaFinRango   = date('Y-m-d', strtotime($fechaInicio . ' +15 days'));

        $sqlRPE = "SELECT fecha, rpe 
                   FROM registro_rpe 
                   WHERE id_atleta = :id_atleta 
                     AND fecha BETWEEN :fecha_inicio AND :fecha_fin
                     AND deleted_at IS NULL   -- solo registros activos (no anulados)
                   ORDER BY fecha ASC";
        $stmtRPE = $this->pdo->prepare($sqlRPE);
        $stmtRPE->bindValue(':id_atleta', $detalle['id_atleta'], PDO::PARAM_INT);
        $stmtRPE->bindValue(':fecha_inicio', $fechaInicioRango);
        $stmtRPE->bindValue(':fecha_fin', $fechaFinRango);
        $stmtRPE->execute();
        $rpeData = $stmtRPE->fetchAll(PDO::FETCH_ASSOC);

        $detalle['rpe_fechas'] = array_column($rpeData, 'fecha');
        $detalle['rpe_historico'] = array_column($rpeData, 'rpe');

        // =========================================================
        // CÁLCULO DEL PROMEDIO RPE ÚLTIMOS 3 DÍAS (para la alerta)
        // =========================================================
        $promedio = $this->obtenerPromedioRPEPrevio($detalle['id_atleta'], $detalle['fecha_inicio']);
        $detalle['rpe_promedio_3_dias'] = round($promedio, 1);

        // =========================================================
        // REGLA DE NEGOCIO: ALERTA DE RIESGO
        // =========================================================
        $alerta = false;
        // Solo si la lesión está activa (no anulada) y el promedio supera 8.5
        if ($detalle['activo'] == 1 && $promedio > 8.5) {
            $diagnostico = strtolower($detalle['diagnostico'] ?? '');
            // Buscamos "molestia leve" en el diagnóstico (puedes ajustar la palabra clave)
            if (strpos($diagnostico, 'molestia leve') !== false) {
                $alerta = true;
            }
        }
        $detalle['alerta_riesgo'] = $alerta;

        return $detalle;
    } catch (PDOException $e) {
        error_log("Error en obtenerDetallePorId: " . $e->getMessage());
        return null;
    }
}

    /**
     * Historial clínico de un atleta (solo registros activos)
     */
    public function obtenerHistorial(int $id_atleta): array {
        try {
            $sql = "SELECT id_lesion, fecha_inicio, zona_anatomica, lado, tipo, nivel_molestia, diagnostico, estado 
                    FROM lesiones 
                    WHERE id_atleta = :id_atleta AND activo = 1
                    ORDER BY fecha_inicio DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerHistorial: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Calcula el promedio de RPE de los últimos 3 días antes de una lesión.
     * Garantiza aislamiento (Isolation) en la lectura.
     */
    public function obtenerPromedioRPEPrevio(int $id_atleta, string $fecha_lesion): float {
        try {
            $sql = "SELECT COALESCE(AVG(rpe), 0) as promedio_rpe 
                    FROM registro_rpe 
                    WHERE id_atleta = :id_atleta 
                    AND activo = 1
                    AND fecha_registro BETWEEN DATE_SUB(:fecha_lesion, INTERVAL 3 DAY) AND :fecha_lesion2";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->bindValue(':fecha_lesion', $fecha_lesion, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_lesion2', $fecha_lesion, PDO::PARAM_STR);
            $stmt->execute();
            
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error calculando RPE previo: " . $e->getMessage());
            return 0.0;
        }
    }

    // =================================================================
    // 4. OPERACIONES DE ESCRITURA (ACID)
    // =================================================================

    /**
     * Registra una nueva lesión (activo = 1 por defecto)
     */
    public function registrarLesion(array $payload): bool|array {
        $this->setAtributos($payload);
        if (!$this->validarAtributosInternos(false)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            $sql = "INSERT INTO lesiones (
                        id_atleta, zona_anatomica, lado, tipo, nivel_molestia,
                        diagnostico, tratamiento, fecha_inicio, fecha_estimada_recup,
                        estado, profesional, observaciones, activo
                    ) VALUES (
                        :id_atleta, :zona_anatomica, :lado, :tipo, :nivel_molestia,
                        :diagnostico, :tratamiento, :fecha_inicio, :fecha_estimada_recup,
                        COALESCE(:estado, 'Activa'), :profesional, :observaciones, 1
                    )";
            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'            => ['id_atleta', PDO::PARAM_INT],
                ':zona_anatomica'       => ['zona_anatomica', PDO::PARAM_STR],
                ':lado'                 => ['lado', PDO::PARAM_STR],
                ':tipo'                 => ['tipo', PDO::PARAM_STR],
                ':nivel_molestia'       => ['nivel_molestia', PDO::PARAM_INT],
                ':diagnostico'          => ['diagnostico', PDO::PARAM_STR],
                ':tratamiento'          => ['tratamiento', PDO::PARAM_STR],
                ':fecha_inicio'         => ['fecha_inicio', PDO::PARAM_STR],
                ':fecha_estimada_recup' => ['fecha_estimada_recup', PDO::PARAM_STR],
                ':estado'               => ['estado', PDO::PARAM_STR],
                ':profesional'          => ['profesional', PDO::PARAM_STR],
                ':observaciones'        => ['observaciones', PDO::PARAM_STR]
            ];
            $this->autoBind($stmt, $mapa, $this->datos);
            $stmt->execute();
            $id_insertado = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return ['exito' => true, 'id_lesion' => $id_insertado, 'mensaje' => 'Lesión registrada correctamente.'];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en registrarLesion: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al registrar la lesión.');
            return false;
        }
    }

    /**
     * Actualiza una lesión existente (solo si está activa)
     */
    public function actualizarLesion(array $payload, int $id_lesion): bool {
        $this->setAtributos($payload);
        if (!$this->validarAtributosInternos(true)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            $sql = "UPDATE lesiones SET
                        id_atleta = :id_atleta,
                        zona_anatomica = :zona_anatomica,
                        lado = :lado,
                        tipo = :tipo,
                        nivel_molestia = :nivel_molestia,
                        diagnostico = :diagnostico,
                        tratamiento = :tratamiento,
                        fecha_inicio = :fecha_inicio,
                        fecha_estimada_recup = :fecha_estimada_recup,
                        estado = :estado,
                        profesional = :profesional,
                        observaciones = :observaciones
                    WHERE id_lesion = :id_lesion AND activo = 1";
            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'            => ['id_atleta', PDO::PARAM_INT],
                ':zona_anatomica'       => ['zona_anatomica', PDO::PARAM_STR],
                ':lado'                 => ['lado', PDO::PARAM_STR],
                ':tipo'                 => ['tipo', PDO::PARAM_STR],
                ':nivel_molestia'       => ['nivel_molestia', PDO::PARAM_INT],
                ':diagnostico'          => ['diagnostico', PDO::PARAM_STR],
                ':tratamiento'          => ['tratamiento', PDO::PARAM_STR],
                ':fecha_inicio'         => ['fecha_inicio', PDO::PARAM_STR],
                ':fecha_estimada_recup' => ['fecha_estimada_recup', PDO::PARAM_STR],
                ':estado'               => ['estado', PDO::PARAM_STR],
                ':profesional'          => ['profesional', PDO::PARAM_STR],
                ':observaciones'        => ['observaciones', PDO::PARAM_STR],
                ':id_lesion'            => ['id_lesion', PDO::PARAM_INT]
            ];
            $this->autoBind($stmt, $mapa, $this->datos);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('actualizacion', 'No se encontró la lesión activa o no se realizaron cambios.');
                return false;
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en actualizarLesion: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al actualizar la lesión.');
            return false;
        }
    }

    // =================================================================
    // 5. SOFT DELETE, REACTIVACIÓN Y ELIMINACIÓN FÍSICA
    // =================================================================

    /**
     * Soft Delete: mueve el registro a la papelera (activo = 0) y guarda el motivo.
     */
    public function eliminarLesionLogicamente(int $id_lesion, string $motivo): bool {
        try {
            $sql = "UPDATE lesiones 
                    SET activo = 0, motivo_eliminacion = :motivo 
                    WHERE id_lesion = :id_lesion AND activo = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':motivo', trim($motivo), PDO::PARAM_STR);
            $stmt->bindValue(':id_lesion', $id_lesion, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en eliminarLesionLogicamente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reactiva un registro previamente eliminado lógicamente (activo = 1, limpia motivo).
     */
    public function reactivarLesion(int $id_lesion): bool {
        try {
            $sql = "UPDATE lesiones 
                    SET activo = 1, motivo_eliminacion = NULL 
                    WHERE id_lesion = :id AND activo = 0";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id_lesion, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en reactivarLesion: " . $e->getMessage());
            return false;
        }
    }

    /**
 * Eliminación física permanente (solo para registros en papelera, activo = 0)
 */
public function eliminarfisico(int $id_lesion): bool {
    try {
        $sql = "DELETE FROM lesiones WHERE id_lesion = :id AND activo = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id_lesion, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $this->agregarError('eliminar', 'No se encontró el registro o no está en papelera (activo=0).');
            return false;
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error en eliminarFisico: " . $e->getMessage());
        $this->agregarError('bd', 'Error interno al eliminar físicamente.');
        return false;
    }
}

    /**
     * Método de compatibilidad con el frontend existente (si usaba 'anularLesion').
     * Llama al soft delete.
     */
    public function anularLesion(int $id_lesion, string $motivo): bool|array {
        if ($this->eliminarLesionLogicamente($id_lesion, $motivo)) {
            return ['exito' => true, 'mensaje' => 'Lesión movida a la papelera correctamente.'];
        }
        return false;
    }
}