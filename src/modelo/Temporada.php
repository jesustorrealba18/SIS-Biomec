<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Temporada extends Conexion {

    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function validarDatos(array $datos): array {
        $this->resetearErrores();

        $this->requerido($datos['nombre'] ?? '', 'nombre');
        $this->longitud($datos['nombre'] ?? '', 'nombre', 2, 100);

        $this->requerido($datos['fecha_inicio'] ?? '', 'fecha_inicio');
        $this->fechaValida($datos['fecha_inicio'] ?? '', 'fecha_inicio');

        $this->requerido($datos['fecha_fin'] ?? '', 'fecha_fin');
        $this->fechaValida($datos['fecha_fin'] ?? '', 'fecha_fin');

        if (!empty($datos['fecha_inicio']) && !empty($datos['fecha_fin'])) {
            if ($datos['fecha_fin'] <= $datos['fecha_inicio']) {
                $this->agregarError('fecha_fin', 'La fecha de fin debe ser posterior a la fecha de inicio.');
            }
        }

        return $this->obtenerErrores();
    }

    public function registrarTemporada(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "INSERT INTO temporadas (nombre, fecha_inicio, fecha_fin, activa)
                    VALUES (:nombre, :fecha_inicio, :fecha_fin, :activa)";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':nombre', trim($datos['nombre']), PDO::PARAM_STR);
            $stmt->bindValue(':fecha_inicio', $datos['fecha_inicio'], PDO::PARAM_STR);
            $stmt->bindValue(':fecha_fin', $datos['fecha_fin'], PDO::PARAM_STR);
            $stmt->bindValue(':activa', !empty($datos['activa']) ? 1 : 0, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en registro de temporada: " . $e->getMessage());
            return false;
        }
    }

    public function listarTemporadas(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT t.*,
                           (SELECT COUNT(*) FROM macrociclos m WHERE m.id_temporada = t.id_temporada) as total_macrociclos
                    FROM temporadas t
                    ORDER BY t.fecha_inicio DESC";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarTemporadas: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $id): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT * FROM temporadas WHERE id_temporada = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId temporada: " . $e->getMessage());
            return null;
        }
    }

    public function actualizarTemporada(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE temporadas SET nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin
                    WHERE id_temporada = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':nombre', trim($datos['nombre']), PDO::PARAM_STR);
            $stmt->bindValue(':fecha_inicio', $datos['fecha_inicio'], PDO::PARAM_STR);
            $stmt->bindValue(':fecha_fin', $datos['fecha_fin'], PDO::PARAM_STR);
            $stmt->bindValue(':id', (int)$datos['id_temporada'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en actualizacion de temporada: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarTemporada(int $id): array {
        $conex = $this->pdo;
        try {
            $sqlCount = "SELECT COUNT(*) as total FROM macrociclos WHERE id_temporada = :id";
            $stmtCount = $conex->prepare($sqlCount);
            $stmtCount->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtCount->execute();
            $row = $stmtCount->fetch(PDO::FETCH_ASSOC);

            if ($row && (int)$row['total'] > 0) {
                return ['exito' => false, 'mensaje' => "No se puede eliminar: tiene {$row['total']} macrociclo(s) asociado(s)."];
            }

            $sql = "DELETE FROM temporadas WHERE id_temporada = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return ['exito' => true, 'mensaje' => 'Temporada eliminada exitosamente.'];
        } catch (PDOException $e) {
            error_log("Error en eliminacion de temporada: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al eliminar la temporada.'];
        }
    }

    public function activarTemporada(int $id): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sqlDesactivar = "UPDATE temporadas SET activa = 0 WHERE activa = 1";
            $stmtDes = $conex->prepare($sqlDesactivar);
            $stmtDes->execute();

            $sqlActivar = "UPDATE temporadas SET activa = 1 WHERE id_temporada = :id";
            $stmtAct = $conex->prepare($sqlActivar);
            $stmtAct->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtAct->execute();

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error al activar temporada: " . $e->getMessage());
            return false;
        }
    }
}
