<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Periodizacion extends Conexion {

    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    private const TRANSICIONES = [
        'Planificado'  => ['En Progreso', 'Finalizado'],
        'En Progreso'  => ['Finalizado', 'Planificado'],
        'Finalizado'   => ['Planificado']
    ];

    private const COLORES_FASE = [
        'Acumulacion'   => '#10b981',
        'Transmutacion' => '#f59e0b',
        'Realizacion'   => '#ef4444',
        'Deload'        => '#6b7280'
    ];

    private const CONFIG_POR_DEFECTO = [
        'pct_acumulacion'   => 55,
        'pct_transmutacion' => 28,
        'pct_realizacion'   => 12,
        'pct_deload'        => 5,
        'frecuencia_deload' => 4
    ];

    // =====================================================================
    // VALIDACIONES
    // =====================================================================

    public function validarDatosMacrociclo(array $datos): array {
        $this->resetearErrores();

        $this->requerido($datos['id_temporada'] ?? '', 'id_temporada');
        $this->soloNumeros($datos['id_temporada'] ?? '', 'id_temporada');

        $this->requerido($datos['id_grupo'] ?? '', 'id_grupo');
        $this->soloNumeros($datos['id_grupo'] ?? '', 'id_grupo');

        $this->requerido($datos['fecha_inicio'] ?? '', 'fecha_inicio');
        $this->fechaValida($datos['fecha_inicio'] ?? '', 'fecha_inicio');

        $this->requerido($datos['fecha_fin'] ?? '', 'fecha_fin');
        $this->fechaValida($datos['fecha_fin'] ?? '', 'fecha_fin');

        if (!empty($datos['fecha_inicio']) && !empty($datos['fecha_fin'])) {
            if ($datos['fecha_fin'] <= $datos['fecha_inicio']) {
                $this->agregarError('fecha_fin', 'La fecha de fin debe ser posterior a la fecha de inicio.');
            }

            $diffDias = (strtotime($datos['fecha_fin']) - strtotime($datos['fecha_inicio'])) / 86400;
            if ($diffDias < 21) {
                $this->agregarError('fecha_fin', 'El macrociclo debe tener al menos 3 semanas (21 dias).');
            }
        }

        $this->longitud($datos['nombre'] ?? '', 'nombre', 0, 100);

        if (!empty($datos['id_evento_objetivo'])) {
            $this->soloNumeros($datos['id_evento_objetivo'], 'id_evento_objetivo');
        }

        return $this->obtenerErrores();
    }

    public function validarDatosMesociclo(array $datos): array {
        $this->resetearErrores();

        $this->requerido($datos['id_macrociclo'] ?? '', 'id_macrociclo');
        $this->soloNumeros($datos['id_macrociclo'] ?? '', 'id_macrociclo');

        $this->requerido($datos['id_fase'] ?? '', 'id_fase');
        $this->soloNumeros($datos['id_fase'] ?? '', 'id_fase');

        $this->requerido($datos['nombre'] ?? '', 'nombre');
        $this->longitud($datos['nombre'] ?? '', 'nombre', 2, 100);

        return $this->obtenerErrores();
    }

    // =====================================================================
    // MAQUINA DE ESTADOS
    // =====================================================================

    public function actualizarEstadoMacrociclo(int $id, string $nuevo_estado): bool {
        $conex = $this->pdo;
        try {
            $sqlActual = "SELECT estado FROM macrociclos WHERE id_macrociclo = :id";
            $stmtActual = $conex->prepare($sqlActual);
            $stmtActual->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtActual->execute();
            $row = $stmtActual->fetch(PDO::FETCH_ASSOC);

            if (!$row) return false;

            $estadoActual = $row['estado'];

            if (!isset(self::TRANSICIONES[$estadoActual]) || !in_array($nuevo_estado, self::TRANSICIONES[$estadoActual])) {
                return false;
            }

            $sql = "UPDATE macrociclos SET estado = :nuevo_estado WHERE id_macrociclo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':nuevo_estado', $nuevo_estado, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en transicion de estado de macrociclo: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // CRUD MACROCICLOS
    // =====================================================================

    public function registrarMacrociclo(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "INSERT INTO macrociclos (id_temporada, id_grupo, nombre, fecha_inicio, fecha_fin, id_evento_objetivo, estado)
                    VALUES (:id_temporada, :id_grupo, :nombre, :fecha_inicio, :fecha_fin, :id_evento, :estado)";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id_temporada', (int)$datos['id_temporada'], PDO::PARAM_INT);
            $stmt->bindValue(':id_grupo', (int)$datos['id_grupo'], PDO::PARAM_INT);
            $stmt->bindValue(':nombre', !empty($datos['nombre']) ? trim($datos['nombre']) : null, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_inicio', $datos['fecha_inicio'], PDO::PARAM_STR);
            $stmt->bindValue(':fecha_fin', $datos['fecha_fin'], PDO::PARAM_STR);
            $stmt->bindValue(':id_evento', !empty($datos['id_evento_objetivo']) ? (int)$datos['id_evento_objetivo'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':estado', 'Planificado', PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en registro de macrociclo: " . $e->getMessage());
            return false;
        }
    }

    public function editarMacrociclo(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE macrociclos SET
                        id_temporada = :id_temporada,
                        id_grupo = :id_grupo,
                        nombre = :nombre,
                        fecha_inicio = :fecha_inicio,
                        fecha_fin = :fecha_fin,
                        id_evento_objetivo = :id_evento
                    WHERE id_macrociclo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id_temporada', (int)$datos['id_temporada'], PDO::PARAM_INT);
            $stmt->bindValue(':id_grupo', (int)$datos['id_grupo'], PDO::PARAM_INT);
            $stmt->bindValue(':nombre', !empty($datos['nombre']) ? trim($datos['nombre']) : null, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_inicio', $datos['fecha_inicio'], PDO::PARAM_STR);
            $stmt->bindValue(':fecha_fin', $datos['fecha_fin'], PDO::PARAM_STR);
            $stmt->bindValue(':id_evento', !empty($datos['id_evento_objetivo']) ? (int)$datos['id_evento_objetivo'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':id', (int)$datos['id_macrociclo'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en edicion de macrociclo: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // GENERADOR ATR
    // =====================================================================

    public function generarPlanPeriodizacion(int $id_macrociclo, array $config = []): array {
        $conex = $this->pdo;
        $cfg = array_merge(self::CONFIG_POR_DEFECTO, $config);

        try {
            $conex->beginTransaction();

            $sqlMacro = "SELECT fecha_inicio, fecha_fin FROM macrociclos WHERE id_macrociclo = :id";
            $stmtMacro = $conex->prepare($sqlMacro);
            $stmtMacro->bindValue(':id', $id_macrociclo, PDO::PARAM_INT);
            $stmtMacro->execute();
            $macro = $stmtMacro->fetch(PDO::FETCH_ASSOC);

            if (!$macro) {
                $conex->rollBack();
                return ['exito' => false, 'mensaje' => 'Macrociclo no encontrado.'];
            }

            $fechaInicio = $macro['fecha_inicio'];
            $fechaFin = $macro['fecha_fin'];

            $diffSeg = strtotime($fechaFin) - strtotime($fechaInicio);
            $totalDias = (int)($diffSeg / 86400);
            $totalSemanas = (int)ceil($totalDias / 7);

            if ($totalSemanas < 4) {
                $conex->rollBack();
                return ['exito' => false, 'mensaje' => 'Minimo 4 semanas requeridas para periodizacion.'];
            }

            $sqlDelete = "DELETE FROM fases_periodizacion WHERE id_macrociclo = :id";
            $stmtDel = $conex->prepare($sqlDelete);
            $stmtDel->bindValue(':id', $id_macrociclo, PDO::PARAM_INT);
            $stmtDel->execute();

            $sqlDeleteMeso = "DELETE mesociclos FROM mesociclos INNER JOIN fases_periodizacion fp ON mesociclos.id_fase = fp.id_fase WHERE fp.id_macrociclo = :id";
            $stmtDelMeso = $conex->prepare($sqlDeleteMeso);
            $stmtDelMeso->bindValue(':id', $id_macrociclo, PDO::PARAM_INT);
            $stmtDelMeso->execute();

            $sqlDeleteMicro = "DELETE microciclos FROM microciclos INNER JOIN mesociclos m ON microciclos.id_mesociclo = m.id_mesociclo INNER JOIN fases_periodizacion fp ON m.id_fase = fp.id_fase WHERE fp.id_macrociclo = :id";
            $stmtDelMicro = $conex->prepare($sqlDeleteMicro);
            $stmtDelMicro->bindValue(':id', $id_macrociclo, PDO::PARAM_INT);
            $stmtDelMicro->execute();

            $semanasReal = max(1, (int)round($totalSemanas * $cfg['pct_realizacion'] / 100));
            $semanasTrans = max(1, (int)round($totalSemanas * $cfg['pct_transmutacion'] / 100));
            $frecDeload = max(2, (int)$cfg['frecuencia_deload']);

            $semanasRestantes = $totalSemanas - $semanasReal - $semanasTrans;
            $numDeload = max(0, (int)floor($semanasRestantes / $frecDeload));
            $semanasDeload = $numDeload;
            $semanasAcum = max(1, $semanasRestantes - $semanasDeload);

            if ($semanasAcum < 1) {
                $semanasAcum = 1;
                $semanasDeload = max(0, $semanasRestantes - $semanasAcum);
                $numDeload = $semanasDeload;
            }

            $faseSemanaInicio = 1;
            $bloquesFase = [];

            $faseSemanaInicio += $semanasAcum;
            $bloquesFase[] = [
                'nombre'         => 'Acumulacion',
                'semana_inicio'  => 1,
                'semana_fin'     => $semanasAcum,
                'pct_volumen'    => 75,
                'rango_intensidad' => 'Z1-Z3',
                'color'          => self::COLORES_FASE['Acumulacion']
            ];

            $deloadInsertados = 0;
            $acumSemActual = 1;
            while ($deloadInsertados < $numDeload && $semanasAcum > 1) {
                if ($acumSemActual + $frecDeload - 1 <= $semanasAcum) {
                    $posDeload = $acumSemActual + $frecDeload - 1;
                    $bloquesFase[] = [
                        'nombre'         => 'Deload',
                        'semana_inicio'  => $posDeload,
                        'semana_fin'     => $posDeload,
                        'pct_volumen'    => 40,
                        'rango_intensidad' => 'Z1-Z2',
                        'color'          => self::COLORES_FASE['Deload']
                    ];
                    $deloadInsertados++;
                } else {
                    break;
                }
                $acumSemActual = $acumSemActual + $frecDeload;
            }

            $transInicio = $semanasAcum + 1;
            $bloquesFase[] = [
                'nombre'         => 'Transmutacion',
                'semana_inicio'  => $transInicio,
                'semana_fin'     => $transInicio + $semanasTrans - 1,
                'pct_volumen'    => 50,
                'rango_intensidad' => 'Z3-Z4',
                'color'          => self::COLORES_FASE['Transmutacion']
            ];

            $realInicio = $transInicio + $semanasTrans;
            $bloquesFase[] = [
                'nombre'         => 'Realizacion',
                'semana_inicio'  => $realInicio,
                'semana_fin'     => $totalSemanas,
                'pct_volumen'    => 30,
                'rango_intensidad' => 'Z4-Z5',
                'color'          => self::COLORES_FASE['Realizacion']
            ];

            usort($bloquesFase, function($a, $b) {
                return $a['semana_inicio'] <=> $b['semana_inicio'];
            });

            $sqlFase = "INSERT INTO fases_periodizacion
                        (id_macrociclo, nombre_fase, semana_inicio, semana_fin, fecha_inicio, fecha_fin, porcentaje_volumen, rango_intensidad, color)
                        VALUES (:id_macro, :nombre, :sem_ini, :sem_fin, :fec_ini, :fec_fin, :pct_vol, :rango_int, :color)";
            $stmtFase = $conex->prepare($sqlFase);

            $fasesInsertadas = [];
            foreach ($bloquesFase as $idx => $bloque) {
                $offsetInicio = ($bloque['semana_inicio'] - 1) * 7;
                $offsetFin = $bloque['semana_fin'] * 7 - 1;

                $fecIni = date('Y-m-d', strtotime($fechaInicio . " +{$offsetInicio} days"));
                $fecFin = date('Y-m-d', strtotime($fechaInicio . " +{$offsetFin} days"));

                if ($fecFin > $fechaFin) {
                    $fecFin = $fechaFin;
                }

                $stmtFase->bindValue(':id_macro', $id_macrociclo, PDO::PARAM_INT);
                $stmtFase->bindValue(':nombre', $bloque['nombre'], PDO::PARAM_STR);
                $stmtFase->bindValue(':sem_ini', $bloque['semana_inicio'], PDO::PARAM_INT);
                $stmtFase->bindValue(':sem_fin', $bloque['semana_fin'], PDO::PARAM_INT);
                $stmtFase->bindValue(':fec_ini', $fecIni, PDO::PARAM_STR);
                $stmtFase->bindValue(':fec_fin', $fecFin, PDO::PARAM_STR);
                $stmtFase->bindValue(':pct_vol', $bloque['pct_volumen'], PDO::PARAM_STR);
                $stmtFase->bindValue(':rango_int', $bloque['rango_intensidad'], PDO::PARAM_STR);
                $stmtFase->bindValue(':color', $bloque['color'], PDO::PARAM_STR);
                $stmtFase->execute();

                $bloquesFase[$idx]['id_fase'] = (int)$conex->lastInsertId();
                $bloquesFase[$idx]['fecha_inicio'] = $fecIni;
                $bloquesFase[$idx]['fecha_fin'] = $fecFin;
                $fasesInsertadas[$bloque['nombre']] = $bloquesFase[$idx];
            }

            $sqlMeso = "INSERT INTO mesociclos (id_macrociclo, id_fase, nombre, semana_inicio, semana_fin, objetivo, volumen_objetivo_m)
                        VALUES (:id_macro, :id_fase, :nombre, :sem_ini, :sem_fin, :objetivo, :vol)";
            $stmtMeso = $conex->prepare($sqlMeso);

            $nombresMeso = [
                'Acumulacion'   => 'Acumulacion',
                'Transmutacion' => 'Transmutacion',
                'Realizacion'   => 'Taper / Realizacion',
                'Deload'        => 'Deload'
            ];

            $objetivosMeso = [
                'Acumulacion'   => 'Desarrollar base aerobica y tecnica',
                'Transmutacion' => 'Convertir volumen en velocidad especifica',
                'Realizacion'   => 'Afinar rendimiento para competencia',
                'Deload'        => 'Recuperacion activa y regeneracion'
            ];

            $volMensuales = [
                'Acumulacion'   => 80000,
                'Transmutacion' => 55000,
                'Realizacion'   => 30000,
                'Deload'        => 20000
            ];

            $mesociclosPorFase = [];
            foreach ($bloquesFase as $bloque) {
                $stmtMeso->bindValue(':id_macro', $id_macrociclo, PDO::PARAM_INT);
                $stmtMeso->bindValue(':id_fase', $bloque['id_fase'], PDO::PARAM_INT);
                $stmtMeso->bindValue(':nombre', $nombresMeso[$bloque['nombre']] ?? $bloque['nombre'], PDO::PARAM_STR);
                $stmtMeso->bindValue(':sem_ini', $bloque['semana_inicio'], PDO::PARAM_INT);
                $stmtMeso->bindValue(':sem_fin', $bloque['semana_fin'], PDO::PARAM_INT);
                $stmtMeso->bindValue(':objetivo', $objetivosMeso[$bloque['nombre']] ?? null, PDO::PARAM_STR);
                $stmtMeso->bindValue(':vol', $volMensuales[$bloque['nombre']] ?? null, PDO::PARAM_INT);
                $stmtMeso->execute();

                $idMeso = (int)$conex->lastInsertId();
                $mesociclosPorFase[$bloque['id_fase']] = $idMeso;
            }

            $sqlMicro = "INSERT INTO microciclos (id_mesociclo, numero_semana, fecha_inicio, fecha_fin, volumen_planificado_m)
                         VALUES (:id_meso, :num_sem, :fec_ini, :fec_fin, :vol)";
            $stmtMicro = $conex->prepare($sqlMicro);

            foreach ($bloquesFase as $bloque) {
                $idMeso = $mesociclosPorFase[$bloque['id_fase']] ?? null;
                if (!$idMeso) continue;

                for ($s = $bloque['semana_inicio']; $s <= $bloque['semana_fin']; $s++) {
                    $offIni = ($s - 1) * 7;
                    $offFin = min(($s * 7) - 1, $totalDias);

                    $fIni = date('Y-m-d', strtotime($fechaInicio . " +{$offIni} days"));
                    $fFin = date('Y-m-d', strtotime($fechaInicio . " +{$offFin} days"));

                    if ($fFin > $fechaFin) {
                        $fFin = $fechaFin;
                    }

                    $volSemanal = match ($bloque['nombre']) {
                        'Acumulacion' => 20000,
                        'Transmutacion' => 15000,
                        'Realizacion' => 8000,
                        'Deload' => 6000,
                        default => 15000
                    };

                    $stmtMicro->bindValue(':id_meso', $idMeso, PDO::PARAM_INT);
                    $stmtMicro->bindValue(':num_sem', $s, PDO::PARAM_INT);
                    $stmtMicro->bindValue(':fec_ini', $fIni, PDO::PARAM_STR);
                    $stmtMicro->bindValue(':fec_fin', $fFin, PDO::PARAM_STR);
                    $stmtMicro->bindValue(':vol', $volSemanal, PDO::PARAM_INT);
                    $stmtMicro->execute();
                }
            }

            $pctRealGenerado = $totalSemanas > 0 ? round(($semanasReal / $totalSemanas) * 100, 1) : 0;
            $pctTransGenerado = $totalSemanas > 0 ? round(($semanasTrans / $totalSemanas) * 100, 1) : 0;
            $pctAcumGenerado = $totalSemanas > 0 ? round(($semanasAcum / $totalSemanas) * 100, 1) : 0;

            $advertencias = [];
            if (abs($pctAcumGenerado - $cfg['pct_acumulacion']) > 15) {
                $advertencias[] = "Acumulacion: {$pctAcumGenerado}% (objetivo {$cfg['pct_acumulacion']}%)";
            }
            if (abs($pctTransGenerado - $cfg['pct_transmutacion']) > 15) {
                $advertencias[] = "Transmutacion: {$pctTransGenerado}% (objetivo {$cfg['pct_transmutacion']}%)";
            }
            if (abs($pctRealGenerado - $cfg['pct_realizacion']) > 15) {
                $advertencias[] = "Realizacion: {$pctRealGenerado}% (objetivo {$cfg['pct_realizacion']}%)";
            }

            $conex->commit();

            return [
                'exito'        => true,
                'mensaje'      => 'Plan de periodizacion generado exitosamente.',
                'total_semanas' => $totalSemanas,
                'fases'        => $bloquesFase,
                'porcentajes'  => [
                    'acumulacion_generado'   => $pctAcumGenerado,
                    'transmutacion_generado' => $pctTransGenerado,
                    'realizacion_generado'   => $pctRealGenerado,
                    'deload_generado'        => round(($semanasDeload / $totalSemanas) * 100, 1)
                ],
                'advertencias' => $advertencias
            ];
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en generacion de periodizacion ATR: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al generar el plan de periodizacion.'];
        }
    }

    // =====================================================================
    // CRUD MESOCICLOS
    // =====================================================================

    public function registrarMesociclo(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "INSERT INTO mesociclos (id_macrociclo, id_fase, nombre, semana_inicio, semana_fin, objetivo, volumen_objetivo_m)
                    VALUES (:id_macro, :id_fase, :nombre, :sem_ini, :sem_fin, :objetivo, :vol)";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id_macro', (int)$datos['id_macrociclo'], PDO::PARAM_INT);
            $stmt->bindValue(':id_fase', (int)$datos['id_fase'], PDO::PARAM_INT);
            $stmt->bindValue(':nombre', trim($datos['nombre']), PDO::PARAM_STR);
            $stmt->bindValue(':sem_ini', !empty($datos['semana_inicio']) ? (int)$datos['semana_inicio'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':sem_fin', !empty($datos['semana_fin']) ? (int)$datos['semana_fin'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':objetivo', !empty($datos['objetivo']) ? trim($datos['objetivo']) : null, PDO::PARAM_STR);
            $stmt->bindValue(':vol', !empty($datos['volumen_objetivo_m']) ? (int)$datos['volumen_objetivo_m'] : null, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en registro de mesociclo: " . $e->getMessage());
            return false;
        }
    }

    public function editarMesociclo(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE mesociclos SET
                        id_fase = :id_fase,
                        nombre = :nombre,
                        semana_inicio = :sem_ini,
                        semana_fin = :sem_fin,
                        objetivo = :objetivo,
                        volumen_objetivo_m = :vol
                    WHERE id_mesociclo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id_fase', (int)$datos['id_fase'], PDO::PARAM_INT);
            $stmt->bindValue(':nombre', trim($datos['nombre']), PDO::PARAM_STR);
            $stmt->bindValue(':sem_ini', !empty($datos['semana_inicio']) ? (int)$datos['semana_inicio'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':sem_fin', !empty($datos['semana_fin']) ? (int)$datos['semana_fin'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':objetivo', !empty($datos['objetivo']) ? trim($datos['objetivo']) : null, PDO::PARAM_STR);
            $stmt->bindValue(':vol', !empty($datos['volumen_objetivo_m']) ? (int)$datos['volumen_objetivo_m'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':id', (int)$datos['id_mesociclo'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en edicion de mesociclo: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarMesociclo(int $id): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sqlMicro = "DELETE FROM microciclos WHERE id_mesociclo = :id";
            $stmtMicro = $conex->prepare($sqlMicro);
            $stmtMicro->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtMicro->execute();

            $sql = "DELETE FROM mesociclos WHERE id_mesociclo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en eliminacion de mesociclo: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // CONSULTAS
    // =====================================================================

    public function listarMacrociclos(?int $id_temporada = null, ?int $id_grupo = null, ?string $estado = null): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT m.*,
                           t.nombre as temporada_nombre,
                           g.nombre as grupo_nombre,
                           e.nombre as evento_objetivo_nombre,
                           e.fecha_inicio as evento_fecha,
                           DATEDIFF(m.fecha_fin, m.fecha_inicio) / 7 as total_semanas,
                           (SELECT COUNT(*) FROM fases_periodizacion fp WHERE fp.id_macrociclo = m.id_macrociclo) as total_fases,
                           (SELECT fp.nombre_fase FROM fases_periodizacion fp
                            WHERE fp.id_macrociclo = m.id_macrociclo
                              AND CURDATE() BETWEEN fp.fecha_inicio AND fp.fecha_fin
                            LIMIT 1) as fase_actual
                    FROM macrociclos m
                    INNER JOIN temporadas t ON m.id_temporada = t.id_temporada
                    INNER JOIN grupos_entrenamiento g ON m.id_grupo = g.id_grupo
                    LEFT JOIN eventos e ON m.id_evento_objetivo = e.id_evento
                    WHERE (:id_temp IS NULL OR m.id_temporada = :id_temp_v)
                      AND (:id_grupo IS NULL OR m.id_grupo = :id_grupo_v)
                      AND (:estado_n IS NULL OR m.estado = :estado_v)
                    ORDER BY m.fecha_inicio DESC, m.id_macrociclo DESC";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id_temp', $id_temporada, $id_temporada === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id_temp_v', $id_temporada, $id_temporada === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id_grupo', $id_grupo, $id_grupo === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id_grupo_v', $id_grupo, $id_grupo === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':estado_n', $estado, $estado === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':estado_v', $estado, $estado === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarMacrociclos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetalleMacrociclo(int $id): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT m.*, t.nombre as temporada_nombre, g.nombre as grupo_nombre,
                           e.nombre as evento_objetivo_nombre, e.fecha_inicio as evento_fecha, e.tipo as evento_tipo,
                           DATEDIFF(m.fecha_fin, m.fecha_inicio) / 7 as total_semanas
                    FROM macrociclos m
                    INNER JOIN temporadas t ON m.id_temporada = t.id_temporada
                    INNER JOIN grupos_entrenamiento g ON m.id_grupo = g.id_grupo
                    LEFT JOIN eventos e ON m.id_evento_objetivo = e.id_evento
                    WHERE m.id_macrociclo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $macro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$macro) return null;

            $sqlFases = "SELECT * FROM fases_periodizacion WHERE id_macrociclo = :id ORDER BY semana_inicio ASC";
            $stmtF = $conex->prepare($sqlFases);
            $stmtF->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtF->execute();
            $macro['fases'] = $stmtF->fetchAll(PDO::FETCH_ASSOC);

            $sqlMeso = "SELECT me.*, fp.nombre_fase, fp.color as fase_color
                        FROM mesociclos me
                        INNER JOIN fases_periodizacion fp ON me.id_fase = fp.id_fase
                        WHERE me.id_macrociclo = :id
                        ORDER BY fp.semana_inicio ASC, me.id_mesociclo ASC";
            $stmtM = $conex->prepare($sqlMeso);
            $stmtM->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtM->execute();
            $macro['mesociclos'] = $stmtM->fetchAll(PDO::FETCH_ASSOC);

            foreach ($macro['mesociclos'] as &$meso) {
                $sqlMicro = "SELECT * FROM microciclos WHERE id_mesociclo = :id ORDER BY numero_semana ASC";
                $stmtMi = $conex->prepare($sqlMicro);
                $stmtMi->bindValue(':id', $meso['id_mesociclo'], PDO::PARAM_INT);
                $stmtMi->execute();
                $meso['microciclos'] = $stmtMi->fetchAll(PDO::FETCH_ASSOC);
            }

            return $macro;
        } catch (PDOException $e) {
            error_log("Error en obtenerDetalleMacrociclo: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerTemporadas(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT * FROM temporadas ORDER BY fecha_inicio DESC";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerGrupos(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT * FROM grupos_entrenamiento WHERE activo = 1 ORDER BY nombre ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerFases(int $id_macrociclo): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT * FROM fases_periodizacion WHERE id_macrociclo = :id ORDER BY semana_inicio ASC";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', $id_macrociclo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerMesociclos(int $id_macrociclo): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT me.*, fp.nombre_fase, fp.color as fase_color
                    FROM mesociclos me
                    INNER JOIN fases_periodizacion fp ON me.id_fase = fp.id_fase
                    WHERE me.id_macrociclo = :id
                    ORDER BY fp.semana_inicio ASC";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', $id_macrociclo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
