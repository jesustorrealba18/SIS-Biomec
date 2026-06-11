<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class ObservacionTecnica extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    private array $datos = [];

    private array $camposPermitidos = [
        'id_atleta', 'id_sesion', 'id_aspecto_tecnico',
        'calificacion', 'observacion_texto',
        'id_observacion', 'accion'
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
    }

    private function validarAtributosInternos(bool $paraActualizacion = false): bool {
        $this->resetearErrores();

        if (!$paraActualizacion || isset($this->datos['id_atleta'])) {
            $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
            $this->soloNumeros((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        }

        if (!$paraActualizacion || isset($this->datos['id_aspecto_tecnico'])) {
            $this->requerido((string)($this->datos['id_aspecto_tecnico'] ?? ''), 'id_aspecto_tecnico');
            $this->soloNumeros((string)($this->datos['id_aspecto_tecnico'] ?? ''), 'id_aspecto_tecnico');
        }

        if (!$paraActualizacion || isset($this->datos['calificacion'])) {
            $this->requerido((string)($this->datos['calificacion'] ?? ''), 'calificacion');
            if (!empty($this->datos['calificacion'])) {
                $this->enEnum((string)$this->datos['calificacion'], 'calificacion', ['1', '2', '3', '4', '5']);
            }
        }

        if (!empty($this->datos['id_sesion'])) {
            $this->soloNumeros((string)$this->datos['id_sesion'], 'id_sesion');
        }

        if (!empty($this->datos['observacion_texto'])) {
            $this->longitud($this->datos['observacion_texto'], 'observacion_texto', 0, 500);
        }

        return empty($this->obtenerErrores());
    }

    public function registrarObservacion(array $payload): bool {
        $this->setAtributos($payload);
        if (!$this->validarAtributosInternos(false)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO observaciones_tecnicas (
                        id_atleta, id_sesion, id_aspecto_tecnico,
                        calificacion, observacion_texto, id_usuario
                    ) VALUES (
                        :id_atleta, :id_sesion, :id_aspecto_tecnico,
                        :calificacion, :observacion_texto, :id_usuario
                    )";

            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'          => ['id_atleta', PDO::PARAM_INT],
                ':id_sesion'          => ['id_sesion', PDO::PARAM_INT],
                ':id_aspecto_tecnico' => ['id_aspecto_tecnico', PDO::PARAM_INT],
                ':calificacion'       => ['calificacion', PDO::PARAM_INT],
                ':observacion_texto'  => ['observacion_texto', PDO::PARAM_STR],
                ':id_usuario'         => ['id_usuario_local', PDO::PARAM_INT]
            ];

            $id_usuario = $payload['id_usuario'] ?? ($_SESSION['id'] ?? 0);
            $this->autoBind($stmt, $mapa, $this->datos, ['id_usuario_local' => (int)$id_usuario]);
            $stmt->execute();

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en registrarObservacion: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al registrar la observacion.');
            return false;
        }
    }

    public function actualizarObservacion(array $payload, int $id_observacion): bool {
        $this->setAtributos($payload);
        if (!$this->validarAtributosInternos(true)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE observaciones_tecnicas SET
                        id_atleta = :id_atleta,
                        id_sesion = :id_sesion,
                        id_aspecto_tecnico = :id_aspecto_tecnico,
                        calificacion = :calificacion,
                        observacion_texto = :observacion_texto
                    WHERE id_observacion = :id_observacion";

            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'          => ['id_atleta', PDO::PARAM_INT],
                ':id_sesion'          => ['id_sesion', PDO::PARAM_INT],
                ':id_aspecto_tecnico' => ['id_aspecto_tecnico', PDO::PARAM_INT],
                ':calificacion'       => ['calificacion', PDO::PARAM_INT],
                ':observacion_texto'  => ['observacion_texto', PDO::PARAM_STR],
                ':id_observacion'     => [$id_observacion, PDO::PARAM_INT]
            ];

            $this->autoBind($stmt, $mapa, $this->datos);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('actualizacion', 'No se encontro la observacion a actualizar.');
                return false;
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en actualizarObservacion: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al actualizar la observacion.');
            return false;
        }
    }

    public function eliminarObservacion(int $id): bool {
        try {
            $sql = "DELETE FROM observaciones_tecnicas WHERE id_observacion = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminarObservacion: " . $e->getMessage());
            return false;
        }
    }

    public function listarObservaciones(int $id_atleta = 0, int $id_sesion = 0, int $id_aspecto = 0): array {
        try {
            $sql = "SELECT ot.id_observacion, ot.id_atleta, ot.id_sesion, ot.id_aspecto_tecnico,
                           ot.calificacion, ot.observacion_texto, ot.fecha_registro,
                           CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, a.cedula,
                           at.nombre AS nombre_aspecto, at.descripcion AS desc_aspecto,
                           s.fecha AS fecha_sesion, s.tipo_sesion
                    FROM observaciones_tecnicas ot
                    INNER JOIN atletas a ON ot.id_atleta = a.id_atleta
                    INNER JOIN aspectos_tecnicos at ON ot.id_aspecto_tecnico = at.id_aspecto
                    LEFT JOIN sesiones s ON ot.id_sesion = s.id_sesion
                    WHERE 1=1";

            $params = [];

            if ($id_atleta > 0) {
                $sql .= " AND ot.id_atleta = :id_atleta";
                $params[':id_atleta'] = [$id_atleta, PDO::PARAM_INT];
            }
            if ($id_sesion > 0) {
                $sql .= " AND ot.id_sesion = :id_sesion";
                $params[':id_sesion'] = [$id_sesion, PDO::PARAM_INT];
            }
            if ($id_aspecto > 0) {
                $sql .= " AND ot.id_aspecto_tecnico = :id_aspecto";
                $params[':id_aspecto'] = [$id_aspecto, PDO::PARAM_INT];
            }

            $sql .= " ORDER BY ot.fecha_registro DESC";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $marcador => $config) {
                $stmt->bindValue($marcador, $config[0], $config[1]);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarObservaciones: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetallePorId(int $id): ?array {
        try {
            $sql = "SELECT ot.*, 
                           CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, a.cedula,
                           at.nombre AS nombre_aspecto, at.descripcion AS desc_aspecto,
                           s.fecha AS fecha_sesion, s.tipo_sesion
                    FROM observaciones_tecnicas ot
                    INNER JOIN atletas a ON ot.id_atleta = a.id_atleta
                    INNER JOIN aspectos_tecnicos at ON ot.id_aspecto_tecnico = at.id_aspecto
                    LEFT JOIN sesiones s ON ot.id_sesion = s.id_sesion
                    WHERE ot.id_observacion = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$detalle) return null;

            $sqlHistorico = "SELECT ot.calificacion, ot.fecha_registro, at.nombre AS nombre_aspecto
                              FROM observaciones_tecnicas ot
                              INNER JOIN aspectos_tecnicos at ON ot.id_aspecto_tecnico = at.id_aspecto
                              WHERE ot.id_atleta = :id_atleta AND ot.id_aspecto_tecnico = :id_aspecto
                              ORDER BY ot.fecha_registro ASC";
            $stmtH = $this->pdo->prepare($sqlHistorico);
            $stmtH->bindValue(':id_atleta', (int)$detalle['id_atleta'], PDO::PARAM_INT);
            $stmtH->bindValue(':id_aspecto', (int)$detalle['id_aspecto_tecnico'], PDO::PARAM_INT);
            $stmtH->execute();
            $detalle['historial_aspecto'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

            return $detalle;
        } catch (PDOException $e) {
            error_log("Error en obtenerDetallePorId: " . $e->getMessage());
            return null;
        }
    }

    public function listarAspectosTecnicos(): array {
        try {
            $sql = "SELECT * FROM aspectos_tecnicos WHERE activo = 1 ORDER BY id_aspecto ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarAspectosTecnicos: " . $e->getMessage());
            return [];
        }
    }

    public function resumenPorAspecto(int $id_atleta): array {
        try {
            $sql = "SELECT at.id_aspecto, at.nombre, at.descripcion,
                           COUNT(ot.id_observacion) AS total_evaluaciones,
                           ROUND(AVG(ot.calificacion), 1) AS promedio,
                           MAX(ot.calificacion) AS maximo,
                           MIN(ot.calificacion) AS minimo,
                           MAX(ot.fecha_registro) AS ultima_evaluacion
                    FROM aspectos_tecnicos at
                    LEFT JOIN observaciones_tecnicos ot
                        ON at.id_aspecto = ot.id_aspecto_tecnico AND ot.id_atleta = :id_atleta
                    WHERE at.activo = 1
                    GROUP BY at.id_aspecto, at.nombre, at.descripcion
                    ORDER BY promedio DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en resumenPorAspecto: " . $e->getMessage());
            return [];
        }
    }
}
