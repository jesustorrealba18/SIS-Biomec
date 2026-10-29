<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;

class horario extends Conexion {
    use ValidacionesTrait;

    private const CAMPOS_PERMITIDOS = ['id_bloque', 'dia_semana', 'hora_inicio', 'hora_fin'];

    private const DIAS_VALIDOS = [
        'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo',
    ];

    private const DIAS_NORMALIZAR = [
        'Miércoles' => 'Miercoles',
        'Sábado'    => 'Sabado',
    ];

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = array_intersect_key($datos, array_flip(self::CAMPOS_PERMITIDOS));
        // Normalizar día
        if (isset($this->datos['dia_semana'])) {
            $this->datos['dia_semana'] = $this->normalizarDia($this->datos['dia_semana']);
        }
    }

    public function setIdEliminar(int $id): void {
        $this->datos['id_bloque'] = $id;
    }

    private function normalizarDia(string $dia): string {
        $dia = trim($dia);
        return self::DIAS_NORMALIZAR[$dia] ?? $dia;
    }

    public function validarDatos(?int $excluirId = null): array {
        $this->resetearErrores();

        $dia_semana  = trim($this->datos['dia_semana'] ?? '');
        $hora_inicio = trim($this->datos['hora_inicio'] ?? '');
        $hora_fin    = trim($this->datos['hora_fin'] ?? '');

        if (!empty($hora_inicio)) $hora_inicio = substr($hora_inicio, 0, 5);
        if (!empty($hora_fin))    $hora_fin    = substr($hora_fin, 0, 5);

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
            strtotime($hora_inicio) >= strtotime($hora_fin)
        ) {
            $this->errores['hora_fin'] = 'La hora de fin debe ser mayor a la hora de inicio';
        }

        if (empty($this->errores)) {
            if ($this->existeSolapamiento($dia_semana, $hora_inicio, $hora_fin, $excluirId)) {
                $this->errores['horario'] = 'Ya existe un bloque activo que se cruza con este en el mismo día';
            }
        }

        return $this->obtenerErrores();
    }

    public function registrarHorario(): bool {
        if (!$this->validarDatos()) return false;

        $idNuevo = $this->registrarHorarioP();
        if ($idNuevo === false) return false;

        Bitacora::registrar($_SESSION['id'] ?? 0, 'horario', 'INSERT', (int)$idNuevo, null, null,
            "{$this->datos['dia_semana']} {$this->datos['hora_inicio']}-{$this->datos['hora_fin']}"
        );

        return true;
    }

    public function editarHorario(): bool {
        $id = (int)($this->datos['id_bloque'] ?? 0);
        if ($id <= 0) return false;

        $anterior = $this->obtenerPorId($id);
        if (!$anterior) return false;

        if (!$this->validarDatos($id)) return false;

        if (!$this->editarHorarioP()) return false;

        $valorAnt = "{$anterior['dia_semana']} {$anterior['hora_inicio']}-{$anterior['hora_fin']}";
        $valorNue = "{$this->datos['dia_semana']} {$this->datos['hora_inicio']}-{$this->datos['hora_fin']}";

        Bitacora::registrar($_SESSION['id'] ?? 0, 'horario', 'UPDATE', $id, 'horario', $valorAnt, $valorNue
        );

        return true;
    }

    public function eliminarHorario(): bool {
        $id = (int)($this->datos['id_bloque'] ?? 0);
        if ($id <= 0) return false;

        $anterior = $this->obtenerPorId($id);
        if (!$anterior) return false;

        if (!$this->cambiarEstado($id, 'Inactivo')) return false;

        Bitacora::registrar($_SESSION['id'] ?? 0, 'horario', 'DESACTIVAR', $id, 'estado', $anterior['estado'] ?? 'Activo', 'Inactivo'
        );

        return true;
    }

    public function reactivarHorario(): bool {
        $id = (int)($this->datos['id_bloque'] ?? 0);
        if ($id <= 0) return false;

        $anterior = $this->obtenerPorId($id);
        if (!$anterior) return false;

        if ($this->existeSolapamiento($anterior['dia_semana'], $anterior['hora_inicio'], $anterior['hora_fin'], $id)) {
            return false;
        }

        if (!$this->cambiarEstado($id, 'Activo')) return false;

        Bitacora::registrar(
            $_SESSION['id'] ?? 0, 'horario', 'REACTIVAR', $id, 'estado', 'Inactivo', 'Activo'
        );

        return true;
    }

    public function listarHorario(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_bloque, dia_semana, estado,
                    TIME_FORMAT(hora_inicio, '%H:%i') as hora_inicio,
                    TIME_FORMAT(hora_fin, '%H:%i') as hora_fin
                    FROM bloques_horarios
                    ORDER BY 
                        FIELD(dia_semana, 'Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'),
                        hora_inicio";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando Horario: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $id_bloque): ?array {
        if ($id_bloque <= 0) return null;
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_bloque, dia_semana, estado,
                    TIME_FORMAT(hora_inicio, '%H:%i') as hora_inicio,
                    TIME_FORMAT(hora_fin, '%H:%i') as hora_fin
                    FROM bloques_horarios WHERE id_bloque = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_bloque]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return $r ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    private function existeSolapamiento(string $dia, string $inicio, string $fin, ?int $excluirId = null): bool {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT COUNT(*) FROM bloques_horarios
                    WHERE dia_semana = :dia
                    AND estado = 'Activo'
                    AND hora_inicio < :fin
                    AND hora_fin > :inicio";
            $params = [':dia' => $dia, ':inicio' => $inicio, ':fin' => $fin];

            if ($excluirId !== null) {
                $sql .= " AND id_bloque != :id";
                $params[':id'] = $excluirId;
            }

            $stmt = $conex->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error verificando solapamiento: " . $e->getMessage());
            return false;
        }
    }

    private function cambiarEstado(int $id, string $estado): bool {
        $conex = $this->getConex1();
        try {
            $stmt = $conex->prepare("UPDATE bloques_horarios SET estado = :estado WHERE id_bloque = :id");
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error cambiando estado: " . $e->getMessage());
            return false;
        }
    }
    
    private function registrarHorarioP() {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();
            $sql = "INSERT INTO bloques_horarios (dia_semana, hora_inicio, hora_fin, estado)
                    VALUES (:dia, :ini, :fin, 'Activo')";
            $stmt = $conex->prepare($sql);
            $ok = $stmt->execute([
                ':dia' => $this->datos['dia_semana'],
                ':ini' => substr($this->datos['hora_inicio'], 0, 5),
                ':fin' => substr($this->datos['hora_fin'], 0, 5),
            ]);
            $id = $ok ? (int)$conex->lastInsertId() : false;
            $conex->commit();
            return $id;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) $conex->rollBack();
            error_log("Error BD registrar Bloque: " . $e->getMessage());
            return false;
        }
    }

    private function editarHorarioP(): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();
            $sql = "UPDATE bloques_horarios SET
                        dia_semana = :dia,
                        hora_inicio = :ini,
                        hora_fin = :fin
                    WHERE id_bloque = :id";
            $stmt = $conex->prepare($sql);
            $ok = $stmt->execute([
                ':dia' => $this->datos['dia_semana'],
                ':ini' => substr($this->datos['hora_inicio'], 0, 5),
                ':fin' => substr($this->datos['hora_fin'], 0, 5),
                ':id'  => (int)$this->datos['id_bloque'],
            ]);
            $conex->commit();
            return $ok;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) $conex->rollBack();
            error_log("Error actualizando Horario: " . $e->getMessage());
            return false;
        }
    }
}