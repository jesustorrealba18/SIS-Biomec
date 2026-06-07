<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Sesion extends Conexion {

    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function validarDatos(array $datos, bool $esEdicion = false): array {
        $this->resetearErrores();

        $this->requerido($datos['id_grupo'] ?? '', 'id_grupo');
        $this->soloNumeros($datos['id_grupo'] ?? '', 'id_grupo');

        if (!empty($datos['id_microciclo'])) {
            $this->soloNumeros($datos['id_microciclo'], 'id_microciclo');
        }

        $this->requerido($datos['fecha'] ?? '', 'fecha');
        $this->fechaValida($datos['fecha'] ?? '', 'fecha');
        
        if (!$esEdicion && !empty($datos['fecha'])) {
            $hoy = date('Y-m-d');
            if ($datos['fecha'] < $hoy) {
                $this->agregarError('fecha', 'No se permiten crear sesiones para fechas pasadas.');
            }
        }

        $this->requerido($datos['tipo_sesion'] ?? '', 'tipo_sesion');
        $this->enEnum($datos['tipo_sesion'] ?? '', 'tipo_sesion', [
            'Técnica', 'Resistencia', 'Velocidad', 'Recuperación', 'Fuerza', 'Flexibilidad', 'Competencia'
        ]);

        $this->longitud($datos['calentamiento'] ?? '', 'calentamiento', 0, 1000);
        $this->longitud($datos['vuelta_calma'] ?? '', 'vuelta_calma', 0, 1000);
        $this->longitud($datos['vuelta_planificado'] ?? '', 'vuelta_planificado', 0, 1000);
        $this->longitud($datos['vuelta_ejecutado'] ?? '', 'vuelta_ejecutado', 0, 1000);
        $this->longitud($datos['duracion_minutos'] ?? '', 'duracion_minutos', 0, 1000);
        $this->longitud($datos['observaciones'] ?? '', 'observaciones', 0, 1000);

        return $this->obtenerErrores();
    }

    public function validarDatosSerie(array $datos): array {
        $this->resetearErrores();

        $this->requerido($datos['bloque'] ?? '', 'bloque');
        $this->enEnum($datos['bloque'] ?? '', 'bloque', ['Calentamiento', 'Principal', 'VueltaCalma']);

        if (empty($datos['id_drill'])) {
            $this->requerido($datos['ejercicio_descripcion'] ?? '', 'ejercicio_descripcion');
        } else {
            $this->soloNumeros($datos['id_drill'], 'id_drill');
        }

        $this->requerido($datos['repeticiones'] ?? '', 'repeticiones');
        $this->soloNumeros($datos['repeticiones'] ?? '', 'repeticiones');
        if (isset($datos['repeticiones']) && (int)$datos['repeticiones'] < 1) {
            $this->agregarError('repeticiones', 'Las repeticiones deben ser mínimo 1.');
        }

        $this->requerido($datos['distancia_m'] ?? '', 'distancia_m');
        $this->soloNumeros($datos['distancia_m'] ?? '', 'distancia_m');
        if (isset($datos['distancia_m']) && (int)$datos['distancia_m'] < 25) {
            $this->agregarError('distancia_m', 'La distancia debe ser mínimo de 25 metros.');
        }

        $this->requerido($datos['descanso_seg'] ?? '', 'descanso_seg');
        $this->soloNumeros($datos['descanso_seg'] ?? '', 'descanso_seg');

        $this->requerido($datos['zona_intensidad'] ?? '', 'zona_intensidad');
        $this->enEnum($datos['zona_intensidad'] ?? '', 'zona_intensidad', ['Z1', 'Z2', 'Z3', 'Z4', 'Z5']);

        return $this->obtenerErrores();
    }

    public function cambiarEstadoSesion(int $id_sesion, string $nuevo_estado): bool {
        $conex = $this->pdo;
        try {
            $sqlActual = "SELECT estado FROM sesiones_entrenamiento WHERE id_sesion = :id";
            $stmtActual = $conex->prepare($sqlActual);
            $stmtActual->execute([':id' => $id_sesion]);
            $row = $stmtActual->fetch(PDO::FETCH_ASSOC);

            if (!$row) return false;
            $estadoActual = $row['estado'];

            $transiciones = [
                'Planificada' => ['En Progreso', 'Cancelada'],
                'En Progreso' => ['Completada', 'Cancelada'],
                'Cancelada'   => ['Planificada']
            ];

            if (!isset($transiciones[$estadoActual]) || !in_array($nuevo_estado, $transiciones[$estadoActual])) {
                return false;
            }

            $sql = "UPDATE sesiones_entrenamiento SET estado = :nuevo_estado, fecha_modificacion = NOW() WHERE id_sesion = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':nuevo_estado' => $nuevo_estado,
                ':id' => $id_sesion
            ]);
        } catch (PDOException $e) {
            error_log("Error en transición de estado de sesión: " . $e->getMessage());
            return false;
        }
    }

    public function registrarSesion(array $datos): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sqlFase = "SELECT id_fase FROM fases_atr WHERE :fecha BETWEEN fecha_inicio AND fecha_fin LIMIT 1";
            $stmtFase = $conex->prepare($sqlFase);
            $stmtFase->execute([':fecha' => $datos['fecha']]);
            $idFaseActual = $stmtFase->fetchColumn() ?: null;

            $sql = "INSERT INTO sesiones
                        (id_grupo, id_microciclo, fecha, tipo_sesion, id_fase_actual, calentamiento, vuelta_calma, vuelta_planificado, vuelta_ejecutado, duracion_minutos, observaciones, estado)
                    VALUES 
                        (:id_grupo, :id_microciclo, :fecha, :tipo_sesion, :id_fase_actual, :calentamiento, :vuelta_calma, :vuelta_planificado, :vuelta_ejecutado, :duracion_minutos, :observaciones, :estado)";

            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':id_grupo'        => $datos['id_grupo'],
                ':id_microciclo'   => !empty($datos['id_microciclo']) ? $datos['id_microciclo'] : null,
                ':fecha'           => $datos['fecha'],
                ':tipo_sesion'     => $datos['tipo_sesion'],
                ':id_fase_actual'  => $idFaseActual,
                ':calentamiento'   => $datos['calentamiento'] ?? null,
                ':vuelta_calma'    => $datos['vuelta_calma'] ?? null,
                ':vuelta_planificado' => $datos['vuelta_planificado'] ?? null,
                ':vuelta_ejecutado'   => $datos['vuelta_ejecutado'] ?? null,
                ':duracion_minuto'    => $datos['duracion_minuto'] ?? null,
                ':observaciones'   => $datos['observaciones'] ?? null,
                ':estado'          => $datos['estado'] ?? 'Planificada'
            ]);

            $id_sesion = (int)$conex->lastInsertId();

            if (!empty($datos['series']) && is_array($datos['series'])) {
                $this->guardarSeriesLote($id_sesion, $datos['series'], $conex);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error al registrar sesión de entrenamiento: " . $e->getMessage());
            return false;
        }
    }

    public function editarSesion(array $datos): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sql = "UPDATE secciones SET
                        id_grupo = :id_grupo, id_microciclo = :id_microciclo, fecha = :fecha,
                        tipo_sesion = :tipo_sesion, calentamiento = :calentamiento, 
                        vuelta_calma = :vuelta_calma, vuelta_planifiaco = :vuelta_planificado, vuelta_ejecutado = :duracion_minutos, observaciones = :observaciones,
                        fecha_modificacion = NOW()
                    WHERE id_sesion = :id_sesion";

            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':id_grupo'      => $datos['id_grupo'],
                ':id_microciclo' => !empty($datos['id_microciclo']) ? $datos['id_microciclo'] : null,
                ':fecha'         => $datos['fecha'],
                ':tipo_sesion'   => $datos['tipo_sesion'],
                ':calentamiento' => $datos['calentamiento'] ?? null,
                ':vuelta_calma'  => $datos['vuelta_calma'] ?? null,
                ':vuelta_planificado'  => $datos['vuelta_planificado'] ?? null,
                ':vuelta_ejecutado'  => $datos['vuelta_ejecutado'] ?? null,
                ':duracion_minutos'  => $datos['duracion_minutos'] ?? null,
                ':observaciones' => $datos['observaciones'] ?? null,
                ':id_sesion'     => (int)$datos['id_sesion']
            ]);

            $sqlDel = "DELETE FROM series_sesion WHERE id_sesion = :id_sesion";
            $stmtDel = $conex->prepare($sqlDel);
            $stmtDel->execute([':id_sesion' => (int)$datos['id_sesion']]);

            if (!empty($datos['series']) && is_array($datos['series'])) {
                $this->guardarSeriesLote((int)$datos['datos_sesion'], $datos['series'], $conex);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error al editar sesión de entrenamiento: " . $e->getMessage());
            return false;
        }
    }

    public function completarSesion(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE sesiones SET
                        volumen_ejecutado = :volumen_ejecutado,
                        observaciones = :observaciones,
                        estado = 'Completada',
                        fecha_modificacion = NOW()
                    WHERE id_sesion = :id_sesion";
            
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':volumen_ejecutado' => (int)$datos['volumen_ejecutado'],
                ':observaciones'     => $datos['observaciones'] ?? null,
                ':id_sesion'         => (int)$datos['id_sesion']
            ]);
        } catch (PDOException $e) {
            error_log("Error al completar la sesión en el sistema: " . $e->getMessage());
            return false;
        }
    }

    private function guardarSeriesLote(int $id_sesion, array $series, PDO $conex): void {
        $sqlSerie = "INSERT INTO series_sesion 
                        (id_sesion, orden_ejecucion, bloque, id_drill, ejercicio_descripcion, repeticiones, distancia_m, descanso_seg, zona_intensidad, ritmo_objetivo)
                     VALUES 
                        (:id_sesion, :orden, :bloque, :id_drill, :descripcion, :repeticiones, :distancia, :descanso, :zona, :ritmo)";
        
        $stmtSerie = $conex->prepare($sqlSerie);
        $orden = 1;

        foreach ($series as $serie) {
            $stmtSerie->execute([
                ':id_sesion'    => $id_sesion,
                ':orden'        => $orden++,
                ':bloque'       => $serie['bloque'],
                ':id_drill'     => !empty($serie['id_drill']) ? (int)$serie['id_drill'] : null,
                ':descripcion'  => empty($serie['id_drill']) ? $serie['ejercicio_descripcion'] : null,
                ':repeticiones' => (int)$serie['repeticiones'],
                ':distancia'    => (int)$serie['distancia_m'],
                ':descanso'     => (int)$serie['descanso_seg'],
                ':zona'         => $serie['zona_intensidad'],
                ':ritmo'        => $serie['ritmo_objetivo'] ?? null
            ]);
        }
    }

    public function listarSesionesPorEntrenador(int $id_entrenador): array {
        $conex = $this->pdo;
        try {

            $sql = "SELECT s.*, g.nombre as nombre_grupo,
                           COALESCE(SUM(ser.repeticiones * ser.distancia_m), 0) as volumen_planificado
                    FROM sesiones s
                    INNER JOIN grupos g ON s.id_grupo = g.id_grupo
                    LEFT JOIN series_sesion ser ON s.id_sesion = ser.id_sesion
                    WHERE g.id_entrenador = :id_entrenador
                    GROUP BY s.id_sesion
                    ORDER BY s.fecha DESC, s.id_sesion DESC";
                    
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id_entrenador' => $id_entrenador]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerDetalleSesionCompleta(int $id_sesion): ?array {
        $conex = $this->pdo;
        try {
            $sqlBase = "SELECT s.*, g.nombre as nombre_grupo 
                        FROM sesiones s
                        INNER JOIN grupos g ON s.id_grupo = g.id_grupo
                        WHERE s.id_sesion = :id";
            $stmtBase = $conex->prepare($sqlBase);
            $stmtBase->execute([':id' => $id_sesion]);
            $sesion = $stmtBase->fetch(PDO::FETCH_ASSOC);

            if (!$sesion) return null;

            $sqlSeries = "SELECT ss.*, d.nombre as drill_nombre 
                          FROM series_sesion ss
                          LEFT JOIN drills d ON ss.id_drill = d.id_drill
                          WHERE ss.id_sesion = :id
                          ORDER BY ss.orden_ejecucion ASC";
            $stmtSeries = $conex->prepare($sqlSeries);
            $stmtSeries->execute([':id' => $id_sesion]);
            $seriesRaw = $stmtSeries->fetchAll(PDO::FETCH_ASSOC);

            $volumenCalentamiento = 0;
            $volumenPrincipal = 0;
            $volumenVueltaCalma = 0;
            $seriesProcesadas = [];

            foreach ($seriesRaw as $serie) {
                $volumenSerie = (int)$serie['repeticiones'] * (int)$serie['distancia_m'];
                $serie['volumen_serie'] = $volumenSerie;

                if ($serie['bloque'] === 'Calentamiento') {
                    $volumenCalentamiento += $volumenSerie;
                } elseif ($serie['bloque'] === 'Principal') {
                    $volumenPrincipal += $volumenSerie;
                } elseif ($serie['bloque'] === 'VueltaCalma') {
                    $volumenVueltaCalma += $volumenSerie;
                }

                $seriesProcesadas[] = $serie;
            }

            $sesion['series'] = $seriesProcesadas;
            $sesion['volumen_calentamiento'] = $volumenCalentamiento;
            $sesion['volumen_principal'] = $volumenPrincipal;
            $sesion['volumen_vuelta_calma'] = $volumenVueltaCalma;
            $sesion['volumen_total_planificado'] = $volumenCalentamiento + $volumenPrincipal + $volumenVueltaCalma;

            return $sesion;
        } catch (PDOException $e) {
            error_log("Error al recuperar el detalle completo de la sesión: " . $e->getMessage());
            return null;
        }
    }
}