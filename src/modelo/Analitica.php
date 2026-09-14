<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Analitica extends Conexion {

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function obtenerDashboard(): array {
        return [
            'kpis'        => $this->obtenerKPIs(),
            'rendimiento' => $this->obtenerRendimiento(),
            'carga'       => $this->obtenerCargaSemanal(),
            'categorias'  => $this->obtenerDistribucionCategorias(),
            'lesiones'    => $this->obtenerLesionesPorZona(),
            'metas'       => $this->obtenerProgresoMetas()
        ];
    }

    private function tendencia(float $actual, float $anterior): array {
        if ($anterior <= 0) {
            return ['pct' => null, 'dir' => $actual > 0 ? 'up' : 'flat'];
        }
        $pct = round((($actual - $anterior) / $anterior) * 100, 1);
        return ['pct' => $pct, 'dir' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat')];
    }

    private function escalarValor(float $valor): string {
        return $valor >= 1000 ? number_format($valor / 1000, 1) . 'k' : (string)round($valor);
    }

    private function obtenerKPIs(): array {
        try {
            $atletas = (int)$this->pdo->query("SELECT COUNT(*) FROM atletas WHERE estado = 'Activo'")->fetchColumn();

            $volActual = (float)$this->pdo->query("SELECT COALESCE(SUM(volumen_total_m),0) FROM v_carga_semanal WHERE fecha > CURDATE() - INTERVAL 7 DAY")->fetchColumn();
            $volAnterior = (float)$this->pdo->query("SELECT COALESCE(SUM(volumen_total_m),0) FROM v_carga_semanal WHERE fecha BETWEEN CURDATE() - INTERVAL 14 DAY AND CURDATE() - INTERVAL 7 DAY")->fetchColumn();

            $rpeActual = (float)$this->pdo->query("SELECT COALESCE(AVG(rpe),0) FROM registro_rpe WHERE deleted_at IS NULL AND fecha > CURDATE() - INTERVAL 7 DAY")->fetchColumn();
            $rpeAnterior = (float)$this->pdo->query("SELECT COALESCE(AVG(rpe),0) FROM registro_rpe WHERE deleted_at IS NULL AND fecha BETWEEN CURDATE() - INTERVAL 14 DAY AND CURDATE() - INTERVAL 7 DAY")->fetchColumn();

            $asisActual = (float)$this->pdo->query("SELECT COALESCE(SUM(estado='Presente') * 100.0 / NULLIF(COUNT(*),0), 0) FROM asistencia WHERE fecha > CURDATE() - INTERVAL 30 DAY")->fetchColumn();
            $asisAnterior = (float)$this->pdo->query("SELECT COALESCE(SUM(estado='Presente') * 100.0 / NULLIF(COUNT(*),0), 0) FROM asistencia WHERE fecha BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE() - INTERVAL 30 DAY")->fetchColumn();

            $pbActual = (float)$this->pdo->query("SELECT COUNT(*) FROM marcas WHERE es_pb = 1 AND estado = 'Activo' AND fecha > CURDATE() - INTERVAL 30 DAY")->fetchColumn();
            $pbAnterior = (float)$this->pdo->query("SELECT COUNT(*) FROM marcas WHERE es_pb = 1 AND estado = 'Activo' AND fecha BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE() - INTERVAL 30 DAY")->fetchColumn();

            return [
                'atletas' => [
                    'valor' => number_format($atletas),
                    'tendencia' => ['pct' => null, 'dir' => 'flat']
                ],
                'volumen' => [
                    'valor' => $this->escalarValor($volActual),
                    'tendencia' => $this->tendencia($volActual, $volAnterior)
                ],
                'rpe' => [
                    'valor' => number_format($rpeActual, 1),
                    'tendencia' => $this->tendencia($rpeActual, $rpeAnterior)
                ],
                'asistencia' => [
                    'valor' => round($asisActual) . '%',
                    'tendencia' => $this->tendencia($asisActual, $asisAnterior)
                ],
                'pbs' => [
                    'valor' => (string)(int)$pbActual,
                    'tendencia' => ['pct' => $this->tendencia($pbActual, $pbAnterior)['pct'], 'dir' => $pbActual >= $pbAnterior ? 'up' : 'down']
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error KPIs analitica: " . $e->getMessage());
            return [
                'atletas' => ['valor' => '0', 'tendencia' => ['pct' => null, 'dir' => 'flat']],
                'volumen' => ['valor' => '0', 'tendencia' => ['pct' => null, 'dir' => 'flat']],
                'rpe' => ['valor' => '0', 'tendencia' => ['pct' => null, 'dir' => 'flat']],
                'asistencia' => ['valor' => '0%', 'tendencia' => ['pct' => null, 'dir' => 'flat']],
                'pbs' => ['valor' => '0', 'tendencia' => ['pct' => null, 'dir' => 'flat']]
            ];
        }
    }

    private function obtenerRendimiento(): array {
        try {
            $prueba = $this->pdo->query("SELECT estilo, distancia_m, tipo_piscina FROM marcas WHERE estado = 'Activo' GROUP BY estilo, distancia_m, tipo_piscina ORDER BY COUNT(*) DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            if (!$prueba) {
                return ['prueba' => null, 'evolucion' => ['labels' => [], 'valores' => []], 'comparativa' => ['labels' => [], 'valores' => []]];
            }

            $sqlEvolucion = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, ROUND(AVG(tiempo_final_seg), 2) AS promedio
                             FROM marcas
                             WHERE estado = 'Activo' AND estilo = :estilo AND distancia_m = :distancia AND tipo_piscina = :piscina
                               AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                             GROUP BY mes ORDER BY mes";
            $stmt = $this->pdo->prepare($sqlEvolucion);
            $stmt->execute([':estilo' => $prueba['estilo'], ':distancia' => $prueba['distancia_m'], ':piscina' => $prueba['tipo_piscina']]);
            $evolucion = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sqlComparativa = "SELECT CONCAT(a.nombres, ' ', a.apellidos) AS atleta, MIN(m.tiempo_final_seg) AS mejor
                               FROM marcas m
                               INNER JOIN atletas a ON m.id_atleta = a.id_atleta
                               WHERE m.estado = 'Activo' AND m.estilo = :estilo AND m.distancia_m = :distancia AND m.tipo_piscina = :piscina
                               GROUP BY m.id_atleta, atleta
                               ORDER BY mejor ASC LIMIT 5";
            $stmt = $this->pdo->prepare($sqlComparativa);
            $stmt->execute([':estilo' => $prueba['estilo'], ':distancia' => $prueba['distancia_m'], ':piscina' => $prueba['tipo_piscina']]);
            $comparativa = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'prueba' => $prueba['distancia_m'] . 'm ' . $prueba['estilo'] . ' (' . $prueba['tipo_piscina'] . ')',
                'evolucion' => [
                    'labels' => array_column($evolucion, 'mes'),
                    'valores' => array_map('floatval', array_column($evolucion, 'promedio'))
                ],
                'comparativa' => [
                    'labels' => array_column($comparativa, 'atleta'),
                    'valores' => array_map('floatval', array_column($comparativa, 'mejor'))
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error rendimiento analitica: " . $e->getMessage());
            return ['prueba' => null, 'evolucion' => ['labels' => [], 'valores' => []], 'comparativa' => ['labels' => [], 'valores' => []]];
        }
    }

    private function obtenerCargaSemanal(): array {
        try {
            $sqlVolumen = "SELECT YEARWEEK(fecha, 3) AS semana, MIN(fecha) AS inicio, COALESCE(SUM(volumen_total_m),0) AS volumen
                           FROM v_carga_semanal
                           GROUP BY semana ORDER BY semana DESC LIMIT 6";
            $volumen = [];
            foreach ($this->pdo->query($sqlVolumen, PDO::FETCH_ASSOC) as $fila) {
                $volumen[$fila['semana']] = ['inicio' => $fila['inicio'], 'volumen' => (float)$fila['volumen']];
            }

            $sqlRpe = "SELECT YEARWEEK(fecha, 3) AS semana, ROUND(AVG(rpe), 1) AS rpe
                       FROM registro_rpe
                       WHERE deleted_at IS NULL
                       GROUP BY semana ORDER BY semana DESC LIMIT 6";
            $rpes = [];
            foreach ($this->pdo->query($sqlRpe, PDO::FETCH_ASSOC) as $fila) {
                $rpes[$fila['semana']] = (float)$fila['rpe'];
            }

            $semanas = array_reverse(array_keys($volumen));
            $labels = [];
            $vol = [];
            $rpe = [];
            foreach ($semanas as $s) {
                $labels[] = date('d/m', strtotime($volumen[$s]['inicio']));
                $vol[] = round($volumen[$s]['volumen'] / 1000, 1);
                $rpe[] = $rpes[$s] ?? null;
            }

            return ['labels' => $labels, 'volumen_km' => $vol, 'rpe' => $rpe];
        } catch (PDOException $e) {
            error_log("Error carga analitica: " . $e->getMessage());
            return ['labels' => [], 'volumen_km' => [], 'rpe' => []];
        }
    }

    private function obtenerDistribucionCategorias(): array {
        try {
            $sql = "SELECT cf.nombre, COUNT(a.id_atleta) AS total
                    FROM atletas a
                    INNER JOIN categorias_feveda cf ON a.id_categoria = cf.id_categoria
                    WHERE a.estado = 'Activo'
                    GROUP BY cf.id_categoria, cf.nombre
                    ORDER BY total DESC";
            $filas = $this->pdo->query($sql, PDO::FETCH_ASSOC)->fetchAll();
            return [
                'labels' => array_column($filas, 'nombre'),
                'valores' => array_map('intval', array_column($filas, 'total'))
            ];
        } catch (PDOException $e) {
            error_log("Error categorias analitica: " . $e->getMessage());
            return ['labels' => [], 'valores' => []];
        }
    }

    private function obtenerLesionesPorZona(): array {
        try {
            $sql = "SELECT zona_anatomica, COUNT(*) AS total
                    FROM v_lesion_activa
                    WHERE estado IN ('Activa', 'EnRehabilitacion', 'Cronica')
                    GROUP BY zona_anatomica
                    ORDER BY total DESC";
            $filas = $this->pdo->query($sql, PDO::FETCH_ASSOC)->fetchAll();
            return [
                'labels' => array_column($filas, 'zona_anatomica'),
                'valores' => array_map('intval', array_column($filas, 'total'))
            ];
        } catch (PDOException $e) {
            error_log("Error lesiones analitica: " . $e->getMessage());
            return ['labels' => [], 'valores' => []];
        }
    }

    private function obtenerProgresoMetas(): array {
        try {
            $sql = "SELECT CONCAT(a.nombres, ' ', a.apellidos) AS atleta,
                           ROUND(AVG(LEAST(100, m.marca_objetivo_seg / NULLIF(m.pb_actual_seg, 0) * 100)), 1) AS cumplimiento
                    FROM metas_competitivas m
                    INNER JOIN atletas a ON m.id_atleta = a.id_atleta
                    WHERE m.marca_objetivo_seg IS NOT NULL AND m.marca_objetivo_seg > 0
                      AND m.pb_actual_seg IS NOT NULL AND m.pb_actual_seg > 0
                    GROUP BY m.id_atleta, atleta
                    ORDER BY cumplimiento DESC
                    LIMIT 5";
            $filas = $this->pdo->query($sql, PDO::FETCH_ASSOC)->fetchAll();
            return [
                'labels' => array_column($filas, 'atleta'),
                'valores' => array_map('floatval', array_column($filas, 'cumplimiento'))
            ];
        } catch (PDOException $e) {
            error_log("Error metas analitica: " . $e->getMessage());
            return ['labels' => [], 'valores' => []];
        }
    }
}
