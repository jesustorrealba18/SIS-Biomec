<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Reporte extends Conexion
{
    public function evolucionMarcas(int $idAtleta, string $estilo, int $distancia, string $piscina, string $fechaIni, string $fechaFin): array
    {
        try {
            $sql = "SELECT fecha, tiempo_final_seg, es_pb, estilo, distancia_m, tipo_piscina,
                           IF(id_evento IS NOT NULL, 'Competencia', 'Control') AS contexto,
                           tiempo_reaccion_seg, brazadas_por_largo, observaciones
                    FROM marcas
                    WHERE id_atleta = :atleta
                      AND estilo = :estilo
                      AND distancia_m = :distancia
                      AND tipo_piscina = :piscina
                      AND estado = 'Activo'
                      AND fecha BETWEEN :fecha_ini AND :fecha_fin
                    ORDER BY fecha ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':atleta', $idAtleta, PDO::PARAM_INT);
            $stmt->bindValue(':estilo', $estilo, PDO::PARAM_STR);
            $stmt->bindValue(':distancia', $distancia, PDO::PARAM_INT);
            $stmt->bindValue(':piscina', $piscina, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_ini', $fechaIni, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reporte::evolucionMarcas - " . $e->getMessage());
            return [];
        }
    }

    public function asistenciaGrupo(int $idGrupo, string $fechaIni, string $fechaFin): array
    {
        try {
            $sql = "SELECT a.id_atleta,
                           CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta,
                           COUNT(DISTINCT s.id_sesion) AS total_sesiones,
                            SUM(CASE WHEN asis.estado = 'Presente' THEN 1 ELSE 0 END) AS presentes,
                            SUM(CASE WHEN asis.estado = 'Ausente' THEN 1 ELSE 0 END) AS ausentes,
                            SUM(CASE WHEN asis.estado = 'Justificado' THEN 1 ELSE 0 END) AS justificados,
                            SUM(CASE WHEN asis.estado = 'Retardo' THEN 1 ELSE 0 END) AS retardos
                    FROM grupo_atleta ga
                    INNER JOIN atletas a ON ga.id_atleta = a.id_atleta
                    INNER JOIN sesiones s ON s.id_grupo = ga.id_grupo
                        AND s.estado IN ('Completada', 'Parcial')
                        AND s.fecha BETWEEN :fecha_ini AND :fecha_fin
                    LEFT JOIN asistencia asis ON asis.id_sesion = s.id_sesion
                        AND asis.id_atleta = a.id_atleta
                    WHERE ga.id_grupo = :grupo
                      AND a.estado = 'Activo'
                    GROUP BY a.id_atleta, a.nombres, a.apellidos
                    ORDER BY presentes DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':grupo', $idGrupo, PDO::PARAM_INT);
            $stmt->bindValue(':fecha_ini', $fechaIni, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reporte::asistenciaGrupo - " . $e->getMessage());
            return [];
        }
    }

    public function volumenSemanal(int $idGrupo, string $fechaIni, string $fechaFin): array
    {
        try {
            $sql = "SELECT YEARWEEK(s.fecha, 1) AS semana,
                           CONCAT(
                               DATE(DATE_ADD(s.fecha, INTERVAL -WEEKDAY(s.fecha) DAY)),
                               ' - ',
                               DATE(DATE_ADD(s.fecha, INTERVAL (5 - WEEKDAY(s.fecha)) DAY))
                           ) AS rango,
                           SUM(s.volumen_planificado) AS metros_planificados,
                           COALESCE(SUM(s.volumen_ejecutado), 0) AS metros_ejecutados,
                           COUNT(s.id_sesion) AS total_sesiones
                    FROM sesiones s
                    WHERE s.id_grupo = :grupo
                      AND s.estado IN ('Completada', 'Parcial')
                      AND s.fecha BETWEEN :fecha_ini AND :fecha_fin
                    GROUP BY YEARWEEK(s.fecha, 1)
                    ORDER BY semana ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':grupo', $idGrupo, PDO::PARAM_INT);
            $stmt->bindValue(':fecha_ini', $fechaIni, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reporte::volumenSemanal - " . $e->getMessage());
            return [];
        }
    }

    public function cargaSRPE(int $idGrupo, int $idAtleta, string $fechaIni, string $fechaFin): array
    {
        try {
            $sql = "SELECT r.fecha, r.rpe, r.srpe, r.horas_sueno, r.calidad_sueno,
                           r.sensacion_muscular, r.estres_percibido, r.metros_nadados,
                           r.duracion_minutos,
                           CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta,
                           a.id_atleta
                    FROM registro_rpe r
                    INNER JOIN atletas a ON r.id_atleta = a.id_atleta
                    WHERE r.deleted_at IS NULL
                      AND r.fecha BETWEEN :fecha_ini AND :fecha_fin";

            if ($idAtleta > 0) {
                $sql .= " AND r.id_atleta = :id_atleta";
            } elseif ($idGrupo > 0) {
                $sql .= " AND a.id_atleta IN (
                              SELECT ga.id_atleta FROM grupo_atleta ga
                              WHERE ga.id_grupo = :id_grupo
                          )";
            }

            $sql .= " ORDER BY r.fecha ASC, a.apellidos ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':fecha_ini', $fechaIni, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_fin', $fechaFin, PDO::PARAM_STR);

            if ($idAtleta > 0) {
                $stmt->bindValue(':id_atleta', $idAtleta, PDO::PARAM_INT);
            } elseif ($idGrupo > 0) {
                $stmt->bindValue(':id_grupo', $idGrupo, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reporte::cargaSRPE - " . $e->getMessage());
            return [];
        }
    }

    public function fichaAtleta(int $idAtleta): ?array
    {
        try {
            $sql = "SELECT a.id_atleta, a.cedula, a.nombres, a.apellidos, a.fecha_nacimiento,
                           a.sexo, a.estado, a.direccion, a.telefono, a.correo,
                           a.fecha_registro_club, a.foto,
                           TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) AS edad,
                           c.nombre AS categoria_nombre,
                           g.nombre AS grupo_nombre,
                           dm.numero_feveda, dm.club_procedencia,
                           dm.grupo_sanguineo, dm.seguro_medico, dm.alergias,
                           dm.condiciones_previas, dm.contacto_emergencia_nombre,
                           dm.contacto_emergencia_telefono, dm.contacto_emergencia_parentesco,
                           r.nombres AS rep_nombres, r.apellidos AS rep_apellidos,
                           r.cedula AS rep_cedula, r.telefono_principal AS rep_telefono,
                           r.parentesco AS rep_parentesco, r.correo AS rep_correo
                    FROM atletas a
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    LEFT JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                    LEFT JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                    LEFT JOIN atleta_datos_medicos dm ON a.id_atleta = dm.id_atleta
                    LEFT JOIN atleta_representante ar ON a.id_atleta = ar.id_atleta
                    LEFT JOIN representantes r ON ar.id_representante = r.id_representante
                        AND r.estado = 'Activo'
                    WHERE a.id_atleta = :id_atleta";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $idAtleta, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Reporte::fichaAtleta - " . $e->getMessage());
            return null;
        }
    }

    public function obtenerAtletasSelect(): array
    {
        try {
            $sql = "SELECT id_atleta, CONCAT(nombres, ' ', apellidos) AS nombre_completo
                    FROM atletas WHERE estado = 'Activo'
                    ORDER BY apellidos, nombres ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reporte::obtenerAtletasSelect - " . $e->getMessage());
            return [];
        }
    }

    public function obtenerGruposSelect(): array
    {
        try {
            $sql = "SELECT id_grupo, nombre FROM grupos_entrenamiento
                    WHERE activo = 1 ORDER BY nombre ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reporte::obtenerGruposSelect - " . $e->getMessage());
            return [];
        }
    }
}
