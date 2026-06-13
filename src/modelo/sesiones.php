<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Sesiones extends Conexion {

    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function validarDatosSesion(array $datos): array {
        $this->resetearErrores();

        $this->requerido($datos['id_grupo'] ?? '', 'id_grupo');
        $this->soloNumeros($datos['id_grupo'] ?? '', 'id_grupo');

        if (!empty($datos['id_microciclo'])) {
            $this->soloNumeros($datos['id_microciclo'], 'id_microciclo');
        }

        $this->requerido($datos['fecha'] ?? '', 'fecha');
        $this->fechaValida($datos['fecha'] ?? '', 'fecha');
        if (!empty($datos['fecha']) && $datos['fecha'] < date('Y-m-d')) {
            $this->agregarError('fecha', 'No se permite planificar sesiones para fechas pasadas.');
        }

        $this->requerido($datos['tipo_sesion'] ?? '', 'tipo_sesion');
        $this->enEnum($datos['tipo_sesion'] ?? '', 'tipo_sesion', [
            'Tecnica', 'Resistencia', 'Velocidad', 'Recuperacion', 'Fuerza', 'Flexibilidad', 'Competencia'
        ]);
        if (!empty($datos['id_fase_actual'])) {
            $this->soloNumeros($datos['id_fase_actual'], 'id_fase_actual');
        }
        $this->longitud($datos['calentamiento'] ?? '', 'calentamiento', 0, 5000);
        $this->longitud($datos['vuelta_calma'] ?? '', 'vuelta_calma', 0, 5000);
        $this->longitud($datos['observaciones'] ?? '', 'observaciones', 0, 5000);

        return $this->obtenerErrores();
    }

    public function validarDatosSerie(array $datos): array {
        $this->resetearErrores();

        $this->requerido($datos['bloque'] ?? '', 'bloque');
        $this->enEnum($datos['bloque'] ?? '', 'bloque', ['Calentamiento', 'Principal', 'VuletaCalma']);

        if (empty($datos['id_drill'])) {
            $this->requerido($datos['ejercicio_descripcion'] ?? '', 'ejercicio_descripcion');
        }

        $this->requerido($datos['repeticiones'] ?? '', 'repeticiones');
        $this->soloNumeros($datos['repeticiones'] ?? '', 'repeticiones');
        if (isset($datos['repeticiones']) && (int)$datos['repeticiones'] < 1) {
            $this->agregarError('repeticiones', 'Las repeticiones mínimas deben ser 1.');
        }

        $this->requerido($datos['distancia_m'] ?? '', 'distancia_m');
        $this->soloNumeros($datos['distancia_m'] ?? '', 'distancia_m');
        if (isset($datos['distancia_m']) && (int)$datos['distancia_m'] < 25) {
            $this->agregarError('distancia_m', 'La distancia mínima por repetición es de 25 metros.');
        }

        $this->requerido($datos['descanso_seg'] ?? '', 'descanso_seg');
        $this->soloNumeros($datos['descanso_seg'] ?? '', 'descanso_seg');
        if (isset($datos['descanso_seg']) && (int)$datos['descanso_seg'] < 0) {
            $this->agregarError('descanso_seg', 'El descanso no puede ser menor a 0 segundos.');
        }

        $this->requerido($datos['zona_intensidad'] ?? '', 'zona_intensidad');
        $this->enEnum($datos['zona_intensidad'] ?? '', 'zona_intensidad', ['Z1', 'Z2', 'Z3', 'Z4', 'Z5']);

        return $this->obtenerErrores();
    }

    public function registrarSesion(array $datosSesion, array $series): bool {
        error_log("=== INICIO registrarSesion EN MODELO ===");
        error_log("datosSesion: " . print_r($datosSesion, true));
        error_log("series: " . print_r($series, true));
        
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();
            error_log("Transacción iniciada");

            $volumen_calentamiento = 0;
            $volumen_principal = 0;
            $volumen_vuelta_calma = 0;

            foreach ($series as &$serie) {
                $volumen_serie = (int)$serie['repeticiones'] * (int)$serie['distancia_m'];
                $serie['volumen_calculado'] = $volumen_serie;
                error_log("Serie: bloque={$serie['bloque']}, rep={$serie['repeticiones']}, dist={$serie['distancia_m']}, vol={$volumen_serie}");

                if ($serie['bloque'] === 'Calentamiento') {
                    $volumen_calentamiento += $volumen_serie;
                } elseif ($serie['bloque'] === 'Principal') {
                    $volumen_principal += $volumen_serie;
                } elseif ($serie['bloque'] === 'VuletaCalma') {
                    $volumen_vuelta_calma += $volumen_serie;
                }
            }

            $volumen_planificado = $volumen_calentamiento + $volumen_principal + $volumen_vuelta_calma;
            error_log("Volumenes: Cal=$volumen_calentamiento, Prin=$volumen_principal, Vuelta=$volumen_vuelta_calma, Total=$volumen_planificado");

            $id_fase_actual = $datosSesion['id_fase_actual'] ?? null;

            $sql = "INSERT INTO sesiones (
                        id_microciclo, id_grupo, fecha, tipo_sesion, 
                        id_fase_actual, calentamiento, vuelta_calma, 
                        volumen_planificado, observaciones, estado, id_entrenador, duracion_minutos
                    ) VALUES (
                        :id_microciclo, :id_grupo, :fecha, :tipo_sesion, 
                        :id_fase_actual, :calentamiento, :vuelta_calma, 
                        :volumen_planificado, :observaciones, 'Planificada', :id_entrenador, :duracion_minutos
                    )";

            error_log("SQL: " . $sql);
            
            $stmt = $conex->prepare($sql);
            
            $params = [
                ':id_microciclo'      => !empty($datosSesion['id_microciclo']) ? (int)$datosSesion['id_microciclo'] : null,
                ':id_grupo'           => (int)$datosSesion['id_grupo'],
                ':fecha'              => $datosSesion['fecha'],
                ':tipo_sesion'        => $datosSesion['tipo_sesion'],
                ':id_fase_actual'     => $id_fase_actual,
                ':calentamiento'      => $datosSesion['calentamiento'] ?? null,
                ':vuelta_calma'       => $datosSesion['vuelta_calma'] ?? null,
                ':volumen_planificado'=> $volumen_planificado,
                ':observaciones'      => $datosSesion['observaciones'] ?? null,
                ':id_entrenador'      => (int)$datosSesion['id_entrenador'],
                ':duracion_minutos'   => $datosSesion['duracion_minutos'] ?? null
            ];
            
            error_log("Parámetros: " . print_r($params, true));
            
            if (!$stmt->execute($params)) {
                $errorInfo = $stmt->errorInfo();
                error_log("ERROR SQL INSERT: " . print_r($errorInfo, true));
                throw new PDOException("Error en INSERT: " . $errorInfo[2]);
            }

            $id_sesion = (int)$conex->lastInsertId();
            error_log("ID SESION INSERTADA: " . $id_sesion);

            $sqlSerie = "INSERT INTO series_sesion (
                            id_sesion, orden_ejecucion, bloque, id_drill, 
                            ejercicio_descripcion, repeticiones, distancia_m, 
                            descanso_seg, zona_intensidad, ritmo_objetivo
                        ) VALUES (
                            :id_sesion, :orden, :bloque, :id_drill, 
                            :descripcion, :repeticiones, :distancia, 
                            :descanso, :zona, :ritmo
                        )";
            
            $stmtSerie = $conex->prepare($sqlSerie);
            
            $orden = 1;
            foreach ($series as $s) {
                $paramsSerie = [
                    ':id_sesion'    => $id_sesion,
                    ':orden'        => $orden++, 
                    ':bloque'       => $s['bloque'],
                    ':id_drill'     => !empty($s['id_drill']) ? (int)$s['id_drill'] : null,
                    ':descripcion'  => empty($s['id_drill']) ? ($s['ejercicio_descripcion'] ?? null) : null,
                    ':repeticiones' => (int)$s['repeticiones'],
                    ':distancia'    => (int)$s['distancia_m'],
                    ':descanso'     => (int)$s['descanso_seg'],
                    ':zona'         => $s['zona_intensidad'],
                    ':ritmo'        => $s['ritmo_objetivo'] ?? null
                ];
                error_log("Insertando serie: " . print_r($paramsSerie, true));
                $stmtSerie->execute($paramsSerie);
            }

            $conex->commit();
            error_log("=== TRANSACCIÓN EXITOSA ===");
            return true;
            
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("=== EXCEPCION PDO EN MODELO ===");
            error_log("Mensaje: " . $e->getMessage());
            error_log("Código: " . $e->getCode());
            error_log("Archivo: " . $e->getFile());
            error_log("Línea: " . $e->getLine());
            return false;
        }
    }

    public function completarSesion(array $datosCierre): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE sesiones SET 
                        volumen_ejecutado = :volumen_ejecutado,
                        estado = :estado,
                        observaciones = :observaciones,
                        fecha_modificacion = NOW()
                    WHERE id_sesion = :id_sesion";

            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':volumen_ejecutado'=> (int)$datosCierre['volumen_ejecutado'],
                ':estado'           => $datosCierre['estado'] ?? 'Completada',
                ':observaciones'    => $datosCierre['observaciones'] ?? null,
                ':id_sesion'        => (int)$datosCierre['id_sesion']
            ]);
        } catch (PDOException $e) {
            error_log("Error al completar sesión: " . $e->getMessage());
            return false;
        }
    }

    public function editarSesionPlanificada(int $id_sesion, array $datosSesion, array $series): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $volumen_calentamiento = 0;
            $volumen_principal = 0;
            $volumen_vuelta_calma = 0;

            foreach ($series as &$serie) {
                $volumen_serie = (int)$serie['repeticiones'] * (int)$serie['distancia_m'];
                $serie['volumen_calculado'] = $volumen_serie;

                if ($serie['bloque'] === 'Calentamiento') {
                    $volumen_calentamiento += $volumen_serie;
                } elseif ($serie['bloque'] === 'Principal') {
                    $volumen_principal += $volumen_serie;
                } elseif ($serie['bloque'] === 'VuletaCalma') {
                    $volumen_vuelta_calma += $volumen_serie;
                }
            }

            $volumen_planificado = $volumen_calentamiento + $volumen_principal + $volumen_vuelta_calma;
            $id_fase_actual = $datosSesion['id_fase_actual'] ?? null;

            $sql = "UPDATE sesiones SET 
                        id_microciclo = :id_microciclo, 
                        id_grupo = :id_grupo, 
                        fecha = :fecha, 
                        tipo_sesion = :tipo_sesion, 
                        id_fase_actual = :id_fase_actual, 
                        calentamiento = :calentamiento, 
                        vuelta_calma = :vuelta_calma, 
                        volumen_planificado = :volumen_planificado, 
                        observaciones = :observaciones,
                        duracion_minutos = :duracion_minutos,
                        fecha_modificacion = NOW()
                    WHERE id_sesion = :id_sesion";

            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':id_microciclo'      => !empty($datosSesion['id_microciclo']) ? (int)$datosSesion['id_microciclo'] : null,
                ':id_grupo'           => (int)$datosSesion['id_grupo'],
                ':fecha'              => $datosSesion['fecha'],
                ':tipo_sesion'        => $datosSesion['tipo_sesion'],
                ':id_fase_actual'     => $id_fase_actual,
                ':calentamiento'      => $datosSesion['calentamiento'] ?? null,
                ':vuelta_calma'       => $datosSesion['vuelta_calma'] ?? null,
                ':volumen_planificado'=> $volumen_planificado,
                ':observaciones'      => $datosSesion['observaciones'] ?? null,
                ':duracion_minutos'   => $datosSesion['duracion_minutos'] ?? null,
                ':id_sesion'          => $id_sesion
            ]);

            $sqlDelete = "DELETE FROM series_sesion WHERE id_sesion = :id_sesion";
            $stmtDelete = $conex->prepare($sqlDelete);
            $stmtDelete->execute([':id_sesion' => $id_sesion]);

            $sqlSerie = "INSERT INTO series_sesion (
                            id_sesion, orden_ejecucion, bloque, id_drill, 
                            ejercicio_descripcion, repeticiones, distancia_m, 
                            descanso_seg, zona_intensidad, ritmo_objetivo
                        ) VALUES (
                            :id_sesion, :orden, :bloque, :id_drill, 
                            :descripcion, :repeticiones, :distancia, 
                            :descanso, :zona, :ritmo
                        )";
            $stmtSerie = $conex->prepare($sqlSerie);

            $orden = 1;
            foreach ($series as $s) {
                $stmtSerie->execute([
                    ':id_sesion'    => $id_sesion,
                    ':orden'        => $orden++, 
                    ':bloque'       => $s['bloque'],
                    ':id_drill'     => !empty($s['id_drill']) ? (int)$s['id_drill'] : null,
                    ':descripcion'  => empty($s['id_drill']) ? ($s['ejercicio_descripcion'] ?? null) : null,
                    ':repeticiones' => (int)$s['repeticiones'],
                    ':distancia'    => (int)$s['distancia_m'],
                    ':descanso'     => (int)$s['descanso_seg'],
                    ':zona'         => $s['zona_intensidad'],
                    ':ritmo'        => $s['ritmo_objetivo'] ?? null
                ]);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error al editar sesión: " . $e->getMessage());
            return false;
        }
    }

    public function cancelarSesion(int $id_sesion): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE sesiones SET estado = 'Cancelada', fecha_modificacion = NOW() WHERE id_sesion = :id_sesion";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id_sesion' => $id_sesion]);
        } catch (PDOException $e) {
            error_log("Error al cancelar sesión: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDetalleSesion(int $id_sesion): ?array {
        $conex = $this->pdo;
        try {
            $sqlBase = "SELECT s.*, g.nombre as grupo_nombre
                        FROM sesiones s
                        INNER JOIN grupos_entrenamiento g ON s.id_grupo = g.id_grupo
                        WHERE s.id_sesion = :id";
            $stmtBase = $conex->prepare($sqlBase);
            $stmtBase->execute([':id' => $id_sesion]);
            $sesion = $stmtBase->fetch(PDO::FETCH_ASSOC);

            if (!$sesion) return null;

            $sqlSeries = "SELECT sd.*, d.nombre as drill_nombre, d.estilo as drill_estilo
                          FROM series_sesion sd
                          LEFT JOIN drills d ON sd.id_drill = d.id_drill
                          WHERE sd.id_sesion = :id
                          ORDER BY sd.orden_ejecucion ASC";
            $stmtSeries = $conex->prepare($sqlSeries);
            $stmtSeries->execute([':id' => $id_sesion]);
            $sesion['series'] = $stmtSeries->fetchAll(PDO::FETCH_ASSOC);

            return $sesion;
        } catch (PDOException $e) {
            error_log("Error al recuperar detalle: " . $e->getMessage());
            return null;
        }
    }

    public function listarSesionesPorEntrenador(int $id_entrenador, ?int $id_grupo = null, ?string $estado = null): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT s.*, g.nombre as grupo_nombre
                    FROM sesiones s
                    INNER JOIN grupos_entrenamiento g ON s.id_grupo = g.id_grupo
                    WHERE 1=1";
            
            $params = [];
            
            if ($id_grupo) {
                $sql .= " AND s.id_grupo = :id_grupo";
                $params[':id_grupo'] = $id_grupo;
            }
            
            if ($estado) {
                $sql .= " AND s.estado = :estado";
                $params[':estado'] = $estado;
            }
            
            $sql .= " ORDER BY s.fecha DESC, s.id_sesion DESC";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listando sesiones: " . $e->getMessage());
            return [];
        }
    }

    public function listarMicrociclos(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT id_microciclo, nombre FROM microciclos ORDER BY nombre";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}