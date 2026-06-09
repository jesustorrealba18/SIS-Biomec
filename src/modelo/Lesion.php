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
    /**
     * Bolsa de propiedades encapsulada. Único punto de almacenamiento de datos.
     */
    private array $datos = [];

    /**
     * Lista blanca de campos permitidos (Protección contra Asignación Masiva)
     * NOTA: Verifica que estos campos coincidan exactamente con los 'name' 
     * de tu formulario HTML y las columnas de la tabla en sis_natacion2.sql.
     */
    private array $camposPermitidos = [
        'id_atleta', 'fecha_lesion', 'tipo_lesion', 'zona_corporal', 
        'gravedad', 'diagnostico', 'tratamiento', 'dias_reposo_estimados', 
        'observaciones', 'id_lesion', 'accion'
    ];

    // =====================================================================
    // 2. HIDRATACIÓN Y DEPURACIÓN INTERNA (MÉTODOS PRIVADOS)
    // =====================================================================
    /**
     * Mapea el payload externo al arreglo privado filtrando campos basura.
     */
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

    /**
     * Aplica las reglas de negocio sobre el estado del objeto.
     */
    private function validarAtributosInternos(): bool {
        $this->resetearErrores();

        // Validaciones obligatorias (Uso del ValidacionesTrait)
        $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        $this->requerido((string)($this->datos['fecha_lesion'] ?? ''), 'fecha_lesion');
        $this->requerido((string)($this->datos['tipo_lesion'] ?? ''), 'tipo_lesion');
        $this->requerido((string)($this->datos['gravedad'] ?? ''), 'gravedad');
        $this->requerido((string)($this->datos['diagnostico'] ?? ''), 'diagnostico');

        // Regla de integridad de tiempo cronológico
        if (!empty($this->datos['fecha_lesion']) && $this->datos['fecha_lesion'] > date('Y-m-d')) {
            $this->agregarError('fecha_lesion', 'La fecha de la lesión no puede ser una fecha futura.');
        }

        return empty($this->obtenerErrores());
    }

    // =====================================================================
    // 3. OPERACIÓN TRANSACCIONAL (BACKEND / DB)
    // =====================================================================
    /**
     * Registra una nueva lesión cumpliendo propiedades ACID.
     */
    public function registrarLesion(array $payload): bool|array {
        // 1. Hidratación segura
        $this->setAtributos($payload);

        // 2. Autovalidación de integridad
        if (!$this->validarAtributosInternos()) {
            return false;
        }

        try {
            // [ACID] Atomicidad: Inicia la transacción
            $this->pdo->beginTransaction();

            // Asegúrate de que la tabla se llame "lesiones" o ajústalo a "eventos_medicos" según sis_natacion2.sql
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

            // Uso del AutoBinderTrait para blindaje y casteo dinámico de variables
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

            // [ACID] Durabilidad: Consolidar cambios si todo sale bien
            $this->pdo->commit();

            return [
                'exito' => true, 
                'id_lesion' => $id_insertado, 
                'mensaje' => 'Registro clínico guardado exitosamente. Datos listos para el componente inteligente.'
            ];

        } catch (PDOException $e) {
            // [ACID] Coherencia y Aislamiento: Revertir en caso de fallos
            $this->pdo->rollBack();
            error_log("Error transaccional en RF-10 (registrarLesion): " . $e->getMessage());
            $this->agregarError('bd', 'Ocurrió un error interno del servidor al procesar el evento médico.');
            return false;
        }
    }

    /**
     * Anulación lógica para evitar contaminar los cálculos del componente inteligente.
     */
    public function anularLesion(int $id_lesion, string $motivo_anulacion): bool|array {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE lesiones 
                    SET estado = 'Anulado', 
                        observaciones = CONCAT(COALESCE(observaciones, ''), '\n[ANULADO]: ', :motivo) 
                    WHERE id_lesion = :id_lesion AND estado != 'Anulado'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':motivo', $motivo_anulacion, PDO::PARAM_STR);
            $stmt->bindValue(':id_lesion', $id_lesion, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('anulacion', 'El registro no existe o ya ha sido anulado previamente.');
                return false;
            }

            $this->pdo->commit();
            return ['exito' => true, 'mensaje' => 'Registro anulado correctamente (Eliminado Lógico).'];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error transaccional en RF-10 (anularLesion): " . $e->getMessage());
            $this->agregarError('bd', 'Error al intentar anular el registro clínico.');
            return false;
        }
    }

    // =====================================================================
    // 4. CONSULTAS DE LECTURA (READ)
    // =====================================================================
    /**
     * Extrae el historial clínico de un atleta. Útil para graficar o cruzar con RF-11.
     */
    public function obtenerHistorial(int $id_atleta): array {
        try {
            // Filtramos los 'Anulados' para no enviar data corrupta al frontend ni a la IA
            $sql = "SELECT id_lesion, fecha_lesion, tipo_lesion, zona_corporal, gravedad, diagnostico, estado 
                    FROM lesiones 
                    WHERE id_atleta = :id_atleta AND estado = 'Activo'
                    ORDER BY fecha_lesion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en extractor de historial (RF-10): " . $e->getMessage());
            return [];
        }
    }
}