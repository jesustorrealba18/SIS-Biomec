<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use Exception;

class CargaBienestar extends Conexion
{
    use ValidacionesTrait;
    use AutoBinderTrait;

    private array $datos = [];

    /**
     * Lista blanca de campos permitidos.
     * Incluye los campos reales de la tabla + los nuevos para soft delete.
     */
    private array $camposPermitidos = [
        'id_rpe',                // PK real de la tabla
        'id_atleta',
        'id_sesion',
        'fecha',
        'rpe',
        'horas_sueno',
        'calidad_sueno',
        'sensacion_muscular',
        'estres_percibido',
        'observaciones',
        'metros_nadados',
        'duracion_minutos',
        'srpe',
        'fecha_creacion',
        // Columnas para soft delete
        'estado',
        'motivo_anulacion',
        'id_usuario_registra'
    ];

    /**
     * Hidrata el objeto a partir de un payload, aplicando tipado y limpieza.
     */
    private function setAtributos(array $payload): void
    {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo]) && $payload[$campo] !== '') {
                // Tipado fuerte según el campo
                if (in_array($campo, [
                    'id_rpe', 'id_atleta', 'id_sesion', 'rpe', 'calidad_sueno',
                    'sensacion_muscular', 'estres_percibido', 'metros_nadados',
                    'duracion_minutos', 'srpe', 'id_usuario_registra'
                ])) {
                    $this->datos[$campo] = (int)$payload[$campo];
                } elseif ($campo === 'horas_sueno') {
                    $this->datos[$campo] = (float)$payload[$campo];
                } else {
                    // Campos de texto: sanitización básica
                    $this->datos[$campo] = htmlspecialchars(strip_tags($payload[$campo]), ENT_QUOTES, 'UTF-8');
                }
            } else {
                // Valores por defecto
                if ($campo === 'estado') {
                    $this->datos[$campo] = 'Activo';
                } elseif ($campo === 'fecha_creacion') {
                    $this->datos[$campo] = date('Y-m-d H:i:s');
                } else {
                    $this->datos[$campo] = null;
                }
            }
        }
    }

    /**
     * Validaciones de negocio antes de guardar.
     */
    private function validarAtributosInternos(): bool
    {
        $this->resetearErrores();

        // Campos obligatorios
        $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        $this->requerido((string)($this->datos['fecha'] ?? ''), 'fecha');
        $this->requerido((string)($this->datos['rpe'] ?? ''), 'rpe');

        // RPE (1-10)
        if (isset($this->datos['rpe']) && ($this->datos['rpe'] < 1 || $this->datos['rpe'] > 10)) {
            $this->agregarError('rpe', 'El RPE debe estar en la escala de 1 a 10.');
        }

        // Calidad de sueño (1-5) opcional
        if (!empty($this->datos['calidad_sueno']) && ($this->datos['calidad_sueno'] < 1 || $this->datos['calidad_sueno'] > 5)) {
            $this->agregarError('calidad_sueno', 'La calidad del sueño debe ser 1 (pésimo) a 5 (excelente).');
        }

        // Sensación muscular (1-10) opcional
        if (!empty($this->datos['sensacion_muscular']) && ($this->datos['sensacion_muscular'] < 1 || $this->datos['sensacion_muscular'] > 10)) {
            $this->agregarError('sensacion_muscular', 'La sensación muscular debe ser 1 (muy mal) a 10 (muy bien).');
        }

        // Estrés percibido (1-10) opcional
        if (!empty($this->datos['estres_percibido']) && ($this->datos['estres_percibido'] < 1 || $this->datos['estres_percibido'] > 10)) {
            $this->agregarError('estres_percibido', 'El estrés percibido debe ser 1 (muy bajo) a 10 (muy alto).');
        }

        // Si es una actualización, el id_rpe debe ser válido
        if (!empty($this->datos['id_rpe']) && $this->datos['id_rpe'] <= 0) {
            $this->agregarError('id_rpe', 'Identificador de registro inválido.');
        }

        return empty($this->obtenerErrores());
    }

    /**
     * Guarda (inserta o actualiza) un registro de RPE.
     * Si se proporciona id_rpe, actualiza (solo si está Activo); si no, inserta.
     */
    public function guardar(array $payload): array
    {
        try {
            $this->pdo->beginTransaction();

            $this->setAtributos($payload);

            if (!$this->validarAtributosInternos()) {
                $this->pdo->rollBack();
                return ['exito' => false, 'mensaje' => 'Errores de validación', 'errores' => $this->obtenerErrores()];
            }

            $esNuevo = empty($this->datos['id_rpe']);

            if ($esNuevo) {
                // INSERT
                $sql = "INSERT INTO registro_rpe 
                        (id_atleta, id_sesion, fecha, rpe, horas_sueno, calidad_sueno,
                         sensacion_muscular, estres_percibido, observaciones,
                         metros_nadados, duracion_minutos, srpe, fecha_creacion,
                         estado, id_usuario_registra)
                        VALUES 
                        (:id_atleta, :id_sesion, :fecha, :rpe, :horas_sueno, :calidad_sueno,
                         :sensacion_muscular, :estres_percibido, :observaciones,
                         :metros_nadados, :duracion_minutos, :srpe, :fecha_creacion,
                         :estado, :id_usuario_registra)";

                $stmt = $this->pdo->prepare($sql);
                $this->autoBind($stmt, $this->getMapaParametros(), $this->datos);
                $stmt->execute();

                $idGenerado = $this->pdo->lastInsertId();
                $mensaje = "Registro de carga (RPE) guardado exitosamente.";
            } else {
                // UPDATE: solo si el registro está Activo
                $sql = "UPDATE registro_rpe SET
                            id_atleta = :id_atleta,
                            id_sesion = :id_sesion,
                            fecha = :fecha,
                            rpe = :rpe,
                            horas_sueno = :horas_sueno,
                            calidad_sueno = :calidad_sueno,
                            sensacion_muscular = :sensacion_muscular,
                            estres_percibido = :estres_percibido,
                            observaciones = :observaciones,
                            metros_nadados = :metros_nadados,
                            duracion_minutos = :duracion_minutos,
                            srpe = :srpe,
                            estado = :estado,
                            id_usuario_registra = :id_usuario_registra
                        WHERE id_rpe = :id_rpe AND estado = 'Activo'";

                $stmt = $this->pdo->prepare($sql);
                $mapaUpdate = $this->getMapaParametros();
                // No actualizamos fecha_creacion
                unset($mapaUpdate[':fecha_creacion']);
                $this->autoBind($stmt, $mapaUpdate, $this->datos);
                $stmt->execute();

                if ($stmt->rowCount() === 0) {
                    throw new Exception("No se pudo actualizar. El registro no existe o no está activo.");
                }

                $idGenerado = $this->datos['id_rpe'];
                $mensaje = "Registro de carga actualizado correctamente.";
            }

            $this->pdo->commit();
            return ['exito' => true, 'mensaje' => $mensaje, 'id' => $idGenerado];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error en guardar() CargaBienestar: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage()];
        }
    }

    /**
     * Anula (soft delete) un registro, cambiando estado a 'Anulado' y guardando motivo.
     */
    public function anularRegistro(int $id_rpe, string $motivo, int $id_usuario): array
    {
        try {
            $this->pdo->beginTransaction();

            if (empty(trim($motivo))) {
                throw new Exception("Debe proporcionar una justificación para anular.");
            }

            $sql = "UPDATE registro_rpe 
                    SET estado = 'Anulado', 
                        motivo_anulacion = :motivo,
                        id_usuario_registra = :id_usuario
                    WHERE id_rpe = :id_rpe AND estado = 'Activo'";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':motivo', trim($motivo), PDO::PARAM_STR);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_rpe', $id_rpe, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new Exception("Registro no encontrado o ya anulado.");
            }

            $this->pdo->commit();
            return ['exito' => true, 'mensaje' => 'Registro anulado correctamente.'];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error en anularRegistro: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }

    /**
     * Reactiva un registro anulado (cambia estado a 'Activo' y limpia motivo).
     */
    public function reactivarRegistro(int $id_rpe, int $id_usuario): array
    {
        try {
            $sql = "UPDATE registro_rpe 
                    SET estado = 'Activo', 
                        motivo_anulacion = NULL,
                        id_usuario_registra = :id_usuario
                    WHERE id_rpe = :id_rpe AND estado = 'Anulado'";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_rpe', $id_rpe, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['exito' => false, 'mensaje' => 'Registro no encontrado o ya está activo.'];
            }
            return ['exito' => true, 'mensaje' => 'Registro reactivado correctamente.'];
        } catch (Exception $e) {
            error_log("Error en reactivarRegistro: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al reactivar: ' . $e->getMessage()];
        }
    }

    /**
     * Lista registros filtrando por estado, atleta y rango de fechas.
     */
    public function listar(string $estado = 'Activo', ?int $id_atleta = null, ?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        try {
            $sql = "SELECT r.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta
                    FROM registro_rpe r
                    INNER JOIN atletas a ON r.id_atleta = a.id_atleta
                    WHERE r.estado = :estado";
            $params = [':estado' => $estado];

            if ($id_atleta) {
                $sql .= " AND r.id_atleta = :id_atleta";
                $params[':id_atleta'] = $id_atleta;
            }
            if ($fechaDesde) {
                $sql .= " AND r.fecha >= :fecha_desde";
                $params[':fecha_desde'] = $fechaDesde;
            }
            if ($fechaHasta) {
                $sql .= " AND r.fecha <= :fecha_hasta";
                $params[':fecha_hasta'] = $fechaHasta;
            }

            $sql .= " ORDER BY r.fecha DESC, r.id_rpe DESC";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en listar() CargaBienestar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un registro específico por su ID (sin importar estado).
     */
    public function obtenerPorId(int $id_rpe): ?array
    {
        try {
            $sql = "SELECT r.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta
                    FROM registro_rpe r
                    INNER JOIN atletas a ON r.id_atleta = a.id_atleta
                    WHERE r.id_rpe = :id_rpe";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_rpe', $id_rpe, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper: construye el mapa de parámetros para autoBind.
     */
    private function getMapaParametros(): array
    {
        return [
            ':id_rpe'                => ['id_rpe', PDO::PARAM_INT],
            ':id_atleta'             => ['id_atleta', PDO::PARAM_INT],
            ':id_sesion'             => ['id_sesion', PDO::PARAM_INT],
            ':fecha'                 => ['fecha', PDO::PARAM_STR],
            ':rpe'                   => ['rpe', PDO::PARAM_INT],
            ':horas_sueno'           => ['horas_sueno', PDO::PARAM_STR],
            ':calidad_sueno'         => ['calidad_sueno', PDO::PARAM_INT],
            ':sensacion_muscular'    => ['sensacion_muscular', PDO::PARAM_INT],
            ':estres_percibido'      => ['estres_percibido', PDO::PARAM_INT],
            ':observaciones'         => ['observaciones', PDO::PARAM_STR],
            ':metros_nadados'        => ['metros_nadados', PDO::PARAM_INT],
            ':duracion_minutos'      => ['duracion_minutos', PDO::PARAM_INT],
            ':srpe'                  => ['srpe', PDO::PARAM_INT],
            ':fecha_creacion'        => ['fecha_creacion', PDO::PARAM_STR],
            ':estado'                => ['estado', PDO::PARAM_STR],
            ':motivo_anulacion'      => ['motivo_anulacion', PDO::PARAM_STR],
            ':id_usuario_registra'   => ['id_usuario_registra', PDO::PARAM_INT]
        ];
    }
}