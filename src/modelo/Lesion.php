<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Lesion extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    // =====================================================================
    // 1. ENCAPSULAMIENTO ESTRICTO
    // =====================================================================
    private array $datos = [];

    // Lista blanca de campos permitidos (coincide con las columnas de la tabla)
    private array $camposPermitidos = [
        'id_atleta', 'fecha_lesion', 'tipo_lesion', 'zona_corporal', 
        'gravedad', 'diagnostico', 'tratamiento', 'dias_reposo_estimados', 
        'observaciones', 'id_lesion', 'accion'
    ];

    // =====================================================================
    // 2. HIDRATACIÓN Y VALIDACIÓN INTERNA
    // =====================================================================
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
    }

    private function validarAtributosInternos(bool $paraActualizacion = false): bool {
        $this->resetearErrores();

        // Validaciones comunes
        if (!$paraActualizacion || isset($this->datos['id_atleta'])) {
            $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        }
        if (!$paraActualizacion || isset($this->datos['fecha_lesion'])) {
            $this->requerido((string)($this->datos['fecha_lesion'] ?? ''), 'fecha_lesion');
        }
        if (!$paraActualizacion || isset($this->datos['tipo_lesion'])) {
            $this->requerido((string)($this->datos['tipo_lesion'] ?? ''), 'tipo_lesion');
        }
        if (!$paraActualizacion || isset($this->datos['gravedad'])) {
            $this->requerido((string)($this->datos['gravedad'] ?? ''), 'gravedad');
        }
        if (!$paraActualizacion || isset($this->datos['diagnostico'])) {
            $this->requerido((string)($this->datos['diagnostico'] ?? ''), 'diagnostico');
        }

        // Validación de fecha futura
        if (!empty($this->datos['fecha_lesion']) && $this->datos['fecha_lesion'] > date('Y-m-d')) {
            $this->agregarError('fecha_lesion', 'La fecha de la lesión no puede ser futura.');
        }

        // Validar que la gravedad sea uno de los valores permitidos
        if (!empty($this->datos['gravedad']) && !in_array($this->datos['gravedad'], ['Leve', 'Moderada', 'Grave'])) {
            $this->agregarError('gravedad', 'Gravedad no válida.');
        }

        return empty($this->obtenerErrores());
    }

    // =====================================================================
    // 3. OPERACIONES DE LECTURA (READ)
    // =====================================================================

    /**
     * Lista lesiones con filtros dinámicos (estado, atleta, tipo, gravedad)
     */
    public function listarLesiones(string $estado = 'Activo', int $id_atleta = 0, string $tipo = '', string $gravedad = ''): array {
        try {
            $sql = "SELECT l.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta 
                    FROM lesiones l
                    INNER JOIN atletas a ON l.id_atleta = a.id_atleta
                    WHERE l.estado = :estado";
            $params = [':estado' => $estado];

            if ($id_atleta > 0) {
                $sql .= " AND l.id_atleta = :id_atleta";
                $params[':id_atleta'] = $id_atleta;
            }
            if (!empty($tipo)) {
                $sql .= " AND l.tipo_lesion = :tipo";
                $params[':tipo'] = $tipo;
            }
            if (!empty($gravedad)) {
                $sql .= " AND l.gravedad = :gravedad";
                $params[':gravedad'] = $gravedad;
            }

            $sql .= " ORDER BY l.fecha_lesion DESC";
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
    }

    /**
     * Obtiene el detalle completo de una lesión por ID, incluyendo datos del atleta
     * y (opcionalmente) histórico de RPE para la gráfica.
     */
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
            // SIMULACIÓN / CONSULTA DE RPE PARA LA GRÁFICA
            // Si tienes una tabla 'cargas_entrenamiento' o 'rpe_sesiones',
            // aquí puedes consultar los últimos 30 días y construir el histórico.
            // Mientras tanto, generamos datos de ejemplo para que la gráfica funcione.
            // =========================================================
            $rpeHistorico = [];
            $rpeFechas = [];
            // Ejemplo: últimos 7 días con valores aleatorios entre 1 y 9
            for ($i = 6; $i >= 0; $i--) {
                $fecha = date('Y-m-d', strtotime("-$i days"));
                $rpeFechas[] = $fecha;
                $rpeHistorico[] = rand(1, 9); // Simula RPE
            }
            $detalle['rpe_historico'] = $rpeHistorico;
            $detalle['rpe_fechas'] = $rpeFechas;

            return $detalle;
        } catch (PDOException $e) {
            error_log("Error en obtenerDetallePorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene solo el historial clínico de un atleta (sin filtros de estado, todos los activos)
     * Se mantiene por compatibilidad con el controlador anterior.
     */
    public function obtenerHistorial(int $id_atleta): array {
        try {
            $sql = "SELECT id_lesion, fecha_lesion, tipo_lesion, zona_corporal, gravedad, diagnostico, estado 
                    FROM lesiones 
                    WHERE id_atleta = :id_atleta AND estado = 'Activo'
                    ORDER BY fecha_lesion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerHistorial: " . $e->getMessage());
            return [];
        }
    }

    // =====================================================================
    // 4. OPERACIONES DE ESCRITURA (ACID)
    // =====================================================================

    /**
     * Registra una nueva lesión (INSERT)
     */
    public function registrarLesion(array $payload): bool|array {
        $this->setAtributos($payload);
        if (!$this->validarAtributosInternos(false)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            $sql = "INSERT INTO lesiones (
                        id_atleta, fecha_lesion, tipo_lesion, zona_corporal, 
                        gravedad, diagnostico, tratamiento, dias_reposo_estimados, 
                        estado, observaciones
                    ) VALUES (
                        :id_atleta, :fecha_lesion, :tipo_lesion, :zona_corporal, 
                        :gravedad, :diagnostico, :tratamiento, :dias_reposo_estimados, 
                        'Activo', :observaciones
                    )";
            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'             => ['id_atleta', PDO::PARAM_INT],
                ':fecha_lesion'          => ['fecha_lesion', PDO::PARAM_STR],
                ':tipo_lesion'           => ['tipo_lesion', PDO::PARAM_STR],
                ':zona_corporal'         => ['zona_corporal', PDO::PARAM_STR],
                ':gravedad'              => ['gravedad', PDO::PARAM_STR],
                ':diagnostico'           => ['diagnostico', PDO::PARAM_STR],
                ':tratamiento'           => ['tratamiento', PDO::PARAM_STR],
                ':dias_reposo_estimados' => ['dias_reposo_estimados', PDO::PARAM_INT],
                ':observaciones'         => ['observaciones', PDO::PARAM_STR]
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
     * Actualiza una lesión existente (UPDATE)
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
                        fecha_lesion = :fecha_lesion,
                        tipo_lesion = :tipo_lesion,
                        zona_corporal = :zona_corporal,
                        gravedad = :gravedad,
                        diagnostico = :diagnostico,
                        tratamiento = :tratamiento,
                        dias_reposo_estimados = :dias_reposo_estimados,
                        observaciones = :observaciones
                    WHERE id_lesion = :id_lesion AND estado = 'Activo'";
            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'             => ['id_atleta', PDO::PARAM_INT],
                ':fecha_lesion'          => ['fecha_lesion', PDO::PARAM_STR],
                ':tipo_lesion'           => ['tipo_lesion', PDO::PARAM_STR],
                ':zona_corporal'         => ['zona_corporal', PDO::PARAM_STR],
                ':gravedad'              => ['gravedad', PDO::PARAM_STR],
                ':diagnostico'           => ['diagnostico', PDO::PARAM_STR],
                ':tratamiento'           => ['tratamiento', PDO::PARAM_STR],
                ':dias_reposo_estimados' => ['dias_reposo_estimados', PDO::PARAM_INT],
                ':observaciones'         => ['observaciones', PDO::PARAM_STR],
                ':id_lesion'             => [$id_lesion, PDO::PARAM_INT]
            ];
            $this->autoBind($stmt, $mapa, $this->datos);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('actualizacion', 'No se encontró la lesión o ya está anulada.');
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

    /**
     * Anulación lógica (cambia estado a 'Anulado' y guarda motivo en observaciones)
     */
    public function anularLesion(int $id_lesion, string $motivo): bool|array {
        try {
            $this->pdo->beginTransaction();
            $sql = "UPDATE lesiones 
                    SET estado = 'Anulado', 
                        observaciones = CONCAT(COALESCE(observaciones, ''), '\n[ANULADO]: ', :motivo) 
                    WHERE id_lesion = :id_lesion AND estado != 'Anulado'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->bindValue(':id_lesion', $id_lesion, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('anulacion', 'El registro no existe o ya fue anulado.');
                return false;
            }
            $this->pdo->commit();
            return ['exito' => true, 'mensaje' => 'Lesión anulada correctamente.'];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en anularLesion: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al anular la lesión.');
            return false;
        }
    }

    /**
     * Eliminación física permanente (solo para registros ya anulados)
     */
    public function eliminarFisico(int $id_lesion): bool {
        try {
            $this->pdo->beginTransaction();
            // Verificar que esté anulado antes de borrar
            $sqlCheck = "SELECT estado FROM lesiones WHERE id_lesion = :id";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->bindValue(':id', $id_lesion, PDO::PARAM_INT);
            $stmtCheck->execute();
            $estado = $stmtCheck->fetchColumn();
            if ($estado !== 'Anulado') {
                $this->pdo->rollBack();
                $this->agregarError('eliminar', 'Solo se pueden eliminar físicamente registros previamente anulados.');
                return false;
            }

            $sqlDelete = "DELETE FROM lesiones WHERE id_lesion = :id";
            $stmtDel = $this->pdo->prepare($sqlDelete);
            $stmtDel->bindValue(':id', $id_lesion, PDO::PARAM_INT);
            $stmtDel->execute();
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en eliminarFisico: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al eliminar físicamente.');
            return false;
        }
    }
}