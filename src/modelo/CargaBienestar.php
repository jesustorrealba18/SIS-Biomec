<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;
use Exception;

class CargaBienestar extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    // =====================================================================
    // 1. ENCAPSULAMIENTO ESTRICTO
    // =====================================================================
    private array $datos = [];

    private array $camposPermitidos = [
        'id_evento', 'id_atleta', 'tipo_evento', 'descripcion',
        'rpe', 'calidad_sueno', 'nivel_fatiga', 'fecha',
        'estado', 'id_usuario_registra', 'justificacion_cambio',
        'duracion_sesion_min'   // opcional para calcular carga
    ];

    // =====================================================================
    // 2. HIDRATACIÓN Y VALIDACIÓN INTERNA
    // =====================================================================
    private function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo]) && $payload[$campo] !== '') {
                $this->datos[$campo] = $payload[$campo];
            } else {
                $this->datos[$campo] = null;
            }
        }
    }

    /**
     * Valida reglas de negocio:
     * - RPE, calidad_sueno, nivel_fatiga deben estar entre 1 y 10.
     * - Fecha no puede ser futura.
     * - Campos requeridos.
     */
    private function validarAtributosInternos(bool $edicion = false): bool {
        $this->resetearErrores();

        // Campos siempre requeridos
        $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        $this->requerido((string)($this->datos['tipo_evento'] ?? ''), 'tipo_evento');
        $this->requerido((string)($this->datos['fecha'] ?? ''), 'fecha');

        // En registro, todos los numéricos son requeridos; en edición pueden venir solo los editables
        if (!$edicion) {
            $this->requerido((string)($this->datos['rpe'] ?? ''), 'rpe');
            $this->requerido((string)($this->datos['calidad_sueno'] ?? ''), 'calidad_sueno');
            $this->requerido((string)($this->datos['nivel_fatiga'] ?? ''), 'nivel_fatiga');
            $this->requerido((string)($this->datos['id_usuario_registra'] ?? ''), 'id_usuario_registra');
        }

        // Validaciones de rango (si el campo está presente)
        if (isset($this->datos['rpe']) && $this->datos['rpe'] !== null) {
            $rpe = (int)$this->datos['rpe'];
            if ($rpe < 1 || $rpe > 10) {
                $this->agregarError('rpe', 'El RPE debe estar entre 1 y 10.');
            }
        }
        if (isset($this->datos['calidad_sueno']) && $this->datos['calidad_sueno'] !== null) {
            $calidad = (int)$this->datos['calidad_sueno'];
            if ($calidad < 1 || $calidad > 10) {
                $this->agregarError('calidad_sueno', 'La calidad de sueño debe estar entre 1 y 10.');
            }
        }
        if (isset($this->datos['nivel_fatiga']) && $this->datos['nivel_fatiga'] !== null) {
            $fatiga = (int)$this->datos['nivel_fatiga'];
            if ($fatiga < 1 || $fatiga > 10) {
                $this->agregarError('nivel_fatiga', 'El nivel de fatiga debe estar entre 1 y 10.');
            }
        }

        // Fecha no futura
        if (!empty($this->datos['fecha']) && $this->datos['fecha'] > date('Y-m-d')) {
            $this->agregarError('fecha', 'La fecha no puede ser futura.');
        }

        return empty($this->obtenerErrores());
    }

    /**
     * Calcula la carga de sesión (RPE × duración en minutos)
     * y la retorna, o null si falta duración.
     */
    private function calcularCargaSesion(): ?float {
        if (empty($this->datos['rpe']) || empty($this->datos['duracion_sesion_min'])) {
            return null;
        }
        return (float)$this->datos['rpe'] * (float)$this->datos['duracion_sesion_min'];
    }

    // =====================================================================
    // 3. OPERACIONES CRUD TRANSACCIONALES
    // =====================================================================

    /**
     * Registra un nuevo evento de carga/bienestar.
     */
    public function registrarEvento(array $payload): bool {
        $this->setAtributos($payload);

        if (!$this->validarAtributosInternos(false)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO eventos_salud 
                    (id_atleta, tipo_evento, descripcion, rpe, calidad_sueno, nivel_fatiga, fecha, estado, id_usuario_registra, duracion_sesion_min, carga_sesion) 
                    VALUES 
                    (:id_atleta, :tipo_evento, :descripcion, :rpe, :calidad_sueno, :nivel_fatiga, :fecha, 'Activo', :id_usuario_registra, :duracion, :carga)";

            $stmt = $this->pdo->prepare($sql);

            $carga = $this->calcularCargaSesion();

            $mapa = [
                ':id_atleta'           => ['id_atleta', PDO::PARAM_INT],
                ':tipo_evento'         => ['tipo_evento', PDO::PARAM_STR],
                ':descripcion'         => ['descripcion', PDO::PARAM_STR],
                ':rpe'                 => ['rpe', PDO::PARAM_INT],
                ':calidad_sueno'       => ['calidad_sueno', PDO::PARAM_INT],
                ':nivel_fatiga'        => ['nivel_fatiga', PDO::PARAM_INT],
                ':fecha'               => ['fecha', PDO::PARAM_STR],
                ':id_usuario_registra' => ['id_usuario_registra', PDO::PARAM_INT],
                ':duracion'            => ['duracion_sesion_min', PDO::PARAM_STR],
                ':carga'               => ['carga_local', PDO::PARAM_STR]
            ];

            $this->autoBind($stmt, $mapa, $this->datos, ['carga_local' => $carga]);
            $stmt->execute();

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en registrarEvento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Edita un evento existente (solo campos RPE, calidad_sueno, nivel_fatiga, descripcion)
     * con justificación obligatoria.
     */
    public function editarEvento(array $payload): bool {
        if (empty($payload['justificacion_cambio'])) {
            throw new Exception("La justificación de la edición es obligatoria.");
        }

        $this->setAtributos($payload);

        // Validación parcial: solo los campos que vienen en la edición
        if (!$this->validarAtributosInternos(true)) {
            return false;
        }

        if (empty($this->datos['id_evento'])) {
            throw new Exception("ID de evento no proporcionado.");
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE eventos_salud 
                    SET rpe = :rpe,
                        calidad_sueno = :calidad_sueno,
                        nivel_fatiga = :nivel_fatiga,
                        descripcion = :descripcion,
                        duracion_sesion_min = :duracion,
                        carga_sesion = :carga
                    WHERE id_evento = :id_evento AND estado = 'Activo'";

            $stmt = $this->pdo->prepare($sql);

            $carga = $this->calcularCargaSesion();

            $mapa = [
                ':rpe'          => ['rpe', PDO::PARAM_INT],
                ':calidad_sueno'=> ['calidad_sueno', PDO::PARAM_INT],
                ':nivel_fatiga' => ['nivel_fatiga', PDO::PARAM_INT],
                ':descripcion'  => ['descripcion', PDO::PARAM_STR],
                ':duracion'     => ['duracion_sesion_min', PDO::PARAM_STR],
                ':carga'        => ['carga_local', PDO::PARAM_STR],
                ':id_evento'    => ['id_evento', PDO::PARAM_INT]
            ];

            $this->autoBind($stmt, $mapa, $this->datos, ['carga_local' => $carga]);
            $stmt->execute();

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en editarEvento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Anula (soft delete) un evento, cambiando estado a 'Anulado'.
     * Requiere justificación.
     */
    public function anularEvento(int $idEvento, string $justificacion): bool {
        if (empty($justificacion)) {
            throw new Exception("La justificación para la anulación es obligatoria.");
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE eventos_salud SET estado = 'Anulado' WHERE id_evento = :id_evento";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_evento', $idEvento, PDO::PARAM_INT);
            $resultado = $stmt->execute();

            // Aquí podrías registrar la justificación en una tabla de auditoría,
            // pero normalmente la bitácora se maneja desde el controlador.
            // Dejamos espacio para que el controlador llame a Bitacora::registrar.

            $this->pdo->commit();
            return $resultado;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en anularEvento: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // 4. MÉTODOS DE LECTURA (READ)
    // =====================================================================

    /**
     * Obtiene un evento por su ID.
     */
    public function obtenerPorId(int $idEvento): array {
        $sql = "SELECT * FROM eventos_salud WHERE id_evento = :id_evento LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_evento', $idEvento, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: [];
    }

    /**
     * Obtiene el historial completo de un atleta (solo eventos activos).
     */
    public function obtenerHistorialAtleta(int $idAtleta): array {
        $sql = "SELECT * FROM eventos_salud 
                WHERE id_atleta = :id_atleta AND estado = 'Activo' 
                ORDER BY fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_atleta', $idAtleta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el historial con carga de sesión y métricas agregadas (para gráficas).
     */
    public function obtenerHistorialConMetricas(int $idAtleta, string $fechaInicio = '', string $fechaFin = ''): array {
        $sql = "SELECT fecha, rpe, calidad_sueno, nivel_fatiga, carga_sesion, tipo_evento 
                FROM eventos_salud 
                WHERE id_atleta = :id_atleta AND estado = 'Activo'";
        if ($fechaInicio && $fechaFin) {
            $sql .= " AND fecha BETWEEN :inicio AND :fin";
        }
        $sql .= " ORDER BY fecha ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_atleta', $idAtleta, PDO::PARAM_INT);
        if ($fechaInicio && $fechaFin) {
            $stmt->bindValue(':inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindValue(':fin', $fechaFin, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}