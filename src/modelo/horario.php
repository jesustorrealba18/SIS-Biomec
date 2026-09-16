<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class horario extends Conexion {
    use ValidacionesTrait;

    private const CAMPOS_PERMITIDOS = ['id_bloque', 'dia_semana', 'hora_inicio', 'hora_fin',
    ];

    private const DIAS_VALIDOS = [
        'Lunes', 'Martes', 'Miercoles', 'Miércoles', 'Jueves', 'Viernes', 'Sabado', 'Sábado', 'Domingo',
    ];

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = array_intersect_key($datos, array_flip(self::CAMPOS_PERMITIDOS));
    }

    public function setIdEliminar(int $id): void {
        $this->datos['id_bloque'] = $id;
    }

    public function validarDatos(?int $excluirId = null): array {
        $this->resetearErrores();

        $dia_semana  = trim($this->datos['dia_semana'] ?? '');
        $hora_inicio = trim($this->datos['hora_inicio'] ?? '');
        $hora_fin    = trim($this->datos['hora_fin'] ?? '');

        if (!empty($hora_inicio)) {
            $hora_inicio = substr($hora_inicio, 0, 5);
        }
        if (!empty($hora_fin)) {
            $hora_fin = substr($hora_fin, 0, 5);
        }

        $this->requerido($dia_semana, 'dia_semana');
        if (!empty($dia_semana)) {
            $this->enEnum($dia_semana, 'dia_semana', self::DIAS_VALIDOS);
        }

        $this->requerido($hora_inicio, 'hora_inicio');
        if (!empty($hora_inicio) && !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora_inicio)) {
            $this->errores['hora_inicio'] = 'Formato de hora inválido (debe ser HH:MM)';
        }

        $this->requerido($hora_fin, 'hora_fin');
        if (!empty($hora_fin) && !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora_fin)) {
            $this->errores['hora_fin'] = 'Formato de hora inválido (debe ser HH:MM)';
        }

        if (
            empty($this->errores['hora_inicio']) &&
            empty($this->errores['hora_fin']) &&
            !empty($hora_inicio) && !empty($hora_fin) &&
            $hora_inicio >= $hora_fin
        ) {
            $this->errores['hora_fin'] = 'La hora de fin debe ser mayor a la hora de inicio';
        }

        if (empty($this->errores)) {
            if ($this->existeSolapamiento($dia_semana, $hora_inicio, $hora_fin, $excluirId)) {
                $this->errores['horario'] = 'Ya existe un bloque de horario que se cruza con este en el mismo día';
            }
        }

        return $this->obtenerErrores();
    }

    public function registrarHorario(): bool {
        $errores = $this->validarDatos();
        if (!empty($errores)) {
            return false;
        }
        return $this->registrarHorarioP();
    }

    public function editarHorario(): bool {
        $id = (int)($this->datos['id_bloque'] ?? 0);
        if ($id <= 0) {
            return false;
        }
        $errores = $this->validarDatos($id);
        if (!empty($errores)) {
            return false;
        }
        return $this->editarHorarioP();
    }

    public function eliminarHorario(): bool {
        $id = (int)($this->datos['id_bloque'] ?? 0);
        if ($id <= 0) {
            return false;
        }
        return $this->eliminarHorarioP($id);
    }

    public function listarHorario(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_bloque, dia_semana,
                    TIME_FORMAT(hora_inicio, '%H:%i') as hora_inicio,
                    TIME_FORMAT(hora_fin, '%H:%i') as hora_fin
                    FROM bloques_horarios
                    ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'), hora_inicio";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando Horario: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $id_bloque): ?array {
        if ($id_bloque <= 0) {
            return null;
        }
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_bloque, dia_semana,
                    TIME_FORMAT(hora_inicio, '%H:%i') as hora_inicio,
                    TIME_FORMAT(hora_fin, '%H:%i') as hora_fin
                    FROM bloques_horarios WHERE id_bloque = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_bloque]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    private function existeSolapamiento(string $dia, string $inicio, string $fin, ?int $excluirId = null): bool {
        $conex = $this->getConex1();
        try {
            if ($excluirId === null) {
                $sql = "SELECT COUNT(*) FROM bloques_horarios
                        WHERE dia_semana = :dia
                        AND (
                            (hora_inicio <= :inicio AND hora_fin > :inicio)
                            OR (hora_inicio < :fin AND hora_fin >= :fin)
                            OR (hora_inicio >= :inicio AND hora_fin <= :fin)
                        )";
                $params = [
                    ':dia'    => $dia,
                    ':inicio' => $inicio,
                    ':fin'    => $fin,
                ];
            } else {
                $sql = "SELECT COUNT(*) FROM bloques_horarios
                        WHERE dia_semana = :dia
                        AND id_bloque != :id
                        AND (
                            (hora_inicio <= :inicio AND hora_fin > :inicio)
                            OR (hora_inicio < :fin AND hora_fin >= :fin)
                            OR (hora_inicio >= :inicio AND hora_fin <= :fin)
                        )";
                $params = [
                    ':dia'    => $dia,
                    ':id'     => $excluirId,
                    ':inicio' => $inicio,
                    ':fin'    => $fin,
                ];
            }

            $stmt = $conex->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error verificando solapamiento: " . $e->getMessage());
            return false;
        }
    }

    private function registrarHorarioP(): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO bloques_horarios (dia_semana, hora_inicio, hora_fin)
                    VALUES (:dia_semana, :hora_inicio, :hora_fin)";
            $stmt = $conex->prepare($sql);
            $resultado = $stmt->execute([
                ':dia_semana'  => $this->datos['dia_semana'],
                ':hora_inicio' => substr($this->datos['hora_inicio'], 0, 5),
                ':hora_fin'    => substr($this->datos['hora_fin'], 0, 5),
            ]);

            $conex->commit();
            return $resultado;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            error_log("Error BD registrar Bloque: " . $e->getMessage());
            return false;
        }
    }

    private function editarHorarioP(): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $id_bloque = (int)($this->datos['id_bloque'] ?? 0);

            $sql = "UPDATE bloques_horarios SET
                        dia_semana = :dia_semana,
                        hora_inicio = :hora_inicio,
                        hora_fin = :hora_fin
                    WHERE id_bloque = :id_bloque";
            $stmt = $conex->prepare($sql);
            $status = $stmt->execute([
                ':dia_semana'  => $this->datos['dia_semana'],
                ':hora_inicio' => substr($this->datos['hora_inicio'], 0, 5),
                ':hora_fin'    => substr($this->datos['hora_fin'], 0, 5),
                ':id_bloque'   => $id_bloque,
            ]);

            $conex->commit();
            return $status;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            error_log("Error en actualizar Horario: " . $e->getMessage());
            return false;
        }
    }

    private function eliminarHorarioP(int $id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "DELETE FROM bloques_horarios WHERE id_bloque = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar el bloque: " . $e->getMessage());
            return false;
        }
    }
}