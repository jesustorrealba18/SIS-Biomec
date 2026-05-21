<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;


class Marca extends Conexion {

    // =====================================================================
    // COMPOSICIÓN: Uso del Trait
    // =====================================================================
    use ValidacionesTrait;

    public function __construct() {
        // Inicializa la conexión heredada de la clase padre
        parent::__construct('sis_natacion');
    }

    // =====================================================================
    // 1. MOTOR DE VALIDACIÓN (Utiliza los métodos de ValidacionesTrait)
    // =====================================================================
    /**
     * Valida los campos obligatorios del formulario antes de procesar el guardado
     */
    public function validarDatos(array $datos): array {
        // Limpiamos errores previos del Trait
        $this->resetearErrores();

        // Aplicamos la regla 'requerido' del Trait a cada campo obligatorio
        $this->requerido($datos['id_atleta'] ?? '', 'id_atleta');
        $this->requerido($datos['fecha'] ?? '', 'fecha');
        $this->requerido($datos['estilo'] ?? '', 'estilo');
        $this->requerido($datos['distancia_m'] ?? '', 'distancia_m');
        $this->requerido($datos['tipo_piscina'] ?? '', 'tipo_piscina');
        $this->requerido($datos['tiempo_final_seg'] ?? '', 'tiempo_final_seg');
        $this->requerido($datos['nivel_evento'] ?? '', 'nivel_evento');

        // CA-06.5: Impedir fechas futuras
        if (!empty($datos['fecha']) && $datos['fecha'] > date('Y-m-d')) {
            $this->agregarError('fecha', 'La fecha del registro no puede ser una fecha futura.');
        }

        // Retornamos el array de errores (Si está vacío, la validación pasó con éxito)
        return $this->obtenerErrores();
    }

    // =====================================================================
    // 2. OPERACIONES DE BASE DE DATOS (Transacción unificada)
    // =====================================================================
    /**
     * Registra una marca calculando el PB, el SWOLF e insertando los splits dinámicos
     */
    public function registrarMarca(array $datos): bool {
        $conex = $this->getConex1(); // Capturamos la instancia de PDO
        
        try {
            // Abrimos una transacción para asegurar que si algo falla, no se guarde nada a medias
            $conex->beginTransaction();

            $id_atleta = (int)$datos['id_atleta'];
            $estilo = $datos['estilo'];
            $distancia = (int)$datos['distancia_m'];
            $piscina = $datos['tipo_piscina'];
            $tiempo_final = (float)$datos['tiempo_final_seg'];

            // -------------------------------------------------------------
            // A) DETECCIÓN AUTOMÁTICA DE PERSONAL BEST (PB) - Criterio CA-06.1
            // -------------------------------------------------------------
            $sqlPB = "SELECT MIN(tiempo_final_seg) as mejor_tiempo 
                      FROM marcas 
                      WHERE id_atleta = :id_atleta 
                      AND estilo = :estilo 
                      AND distancia_m = :distancia 
                      AND tipo_piscina = :piscina
                      AND estado = 'Activo'";
                      
            $stmtPB = $conex->prepare($sqlPB);
            $stmtPB->execute([
                ':id_atleta' => $id_atleta,
                ':estilo'    => $estilo,
                ':distancia' => $distancia,
                ':piscina'   => $piscina
            ]);
            $historial = $stmtPB->fetch(PDO::FETCH_ASSOC);
            
            $es_pb = 0; 
            if (empty($historial['mejor_tiempo']) || $tiempo_final < (float)$historial['mejor_tiempo']) {
                $es_pb = 1; // El sistema detecta un nuevo récord personal de forma autónoma
            }

            // -------------------------------------------------------------
            // B) INSERTAR EN TABLA PRINCIPAL: `marcas`
            // -------------------------------------------------------------
            $sqlInsert = "INSERT INTO marcas (id_atleta, id_sesion, id_evento, estilo, distancia_m, tipo_piscina, tiempo_final_seg, tiempo_reaccion_seg, tiempo_viraje_seg, nivel_evento, es_pb, fecha, observaciones) 
                          VALUES (:id_atleta, :id_sesion, :id_evento, :estilo, :distancia, :piscina, :tiempo, :reaccion, :viraje, :nivel, :es_pb, :fecha, :obs)";
            
            $stmt = $conex->prepare($sqlInsert);
            $stmt->execute([
                ':id_atleta' => $id_atleta,
                ':id_sesion' => !empty($datos['id_sesion']) ? (int)$datos['id_sesion'] : null,
                ':id_evento' => !empty($datos['id_evento']) ? (int)$datos['id_evento'] : null,
                ':estilo'    => $estilo,
                ':distancia' => $distancia,
                ':piscina'   => $piscina,
                ':tiempo'    => $tiempo_final,
                ':reaccion'  => !empty($datos['tiempo_reaccion_seg']) ? (float)$datos['tiempo_reaccion_seg'] : null,
                ':viraje'    => !empty($datos['tiempo_viraje_seg']) ? (float)$datos['tiempo_viraje_seg'] : null,
                ':nivel'     => $datos['nivel_evento'],
                ':es_pb'     => $es_pb,
                ':fecha'     => $datos['fecha'],
                ':obs'       => $datos['observaciones'] ?? null
            ]);

            // Recuperamos el ID autoincremental de la marca que se acaba de crear
            $id_marca_insertada = $conex->lastInsertId();

            // -------------------------------------------------------------
            // C) CÁLCULO Y GUARDADO EN TABLA DE EFICIENCIA: `marcas_swolf` - RF-07
            // -------------------------------------------------------------
            if (!empty($datos['brazadas_por_largo'])) {
                $brazadas = (int)$datos['brazadas_por_largo'];
                
                // Determinamos la longitud física del carril
                $longitud_piscina = ($piscina === '25m') ? 25 : 50;
                $cantidad_largos = $distancia / $longitud_piscina;
                
                // Calculamos el tiempo promedio empleado por longitud
                $tiempo_por_largo = $tiempo_final / $cantidad_largos;
                
                // Métrica SWOLF = Tiempo por longitud (s) + Número de brazadas por longitud
                $swolf_calculado = (int)round($tiempo_por_largo + $brazadas);

                $sqlSwolf = "INSERT INTO marcas_swolf (id_marca, num_brazadas, swolf) 
                             VALUES (:id_marca, :brazadas, :swolf)";
                $stmtSwolf = $conex->prepare($sqlSwolf);
                $stmtSwolf->execute([
                    ':id_marca' => $id_marca_insertada,
                    ':brazadas' => $brazadas,
                    ':swolf'    => $swolf_calculado
                ]);
            }

            // -------------------------------------------------------------
            // D) GUARDADO DE TRAMOS TRANSACCIONALES: `marcas_splits` - RF-06
            // -------------------------------------------------------------
            if (!empty($datos['splits']) && is_array($datos['splits'])) {
                $sqlSplit = "INSERT INTO marcas_splits (id_marca, parcial_numero, distancia_parcial_m, tiempo_parcial_seg) 
                             VALUES (:id_marca, :numero, :distancia_parcial, :tiempo_parcial)";
                $stmtSplit = $conex->prepare($sqlSplit);
                
                $numeroSplit = 1;
                foreach ($datos['splits'] as $dist_parcial => $tiempo_seg) {
                    $tiempoParcialLimpio = (float)$tiempo_seg; 
                    
                    if ($tiempoParcialLimpio > 0) {
                        $stmtSplit->execute([
                            ':id_marca'          => $id_marca_insertada,
                            ':numero'            => $numeroSplit,
                            ':distancia_parcial' => (int)$dist_parcial, // 25, 50, 75, 100...
                            ':tiempo_parcial'    => $tiempoParcialLimpio
                        ]);
                        $numeroSplit++;
                    }
                }
            }

            // Si todo se ejecutó sin errores, consolidamos los cambios físicos en la base de datos
            $conex->commit();
            return true;

        } catch (PDOException $e) {
            // Si cualquier consulta falla, deshacemos todo para mantener la integridad de los datos
            $conex->rollBack();
            error_log("Error en transaccion de marca: " . $e->getMessage());

            //throw new \Exception("Error SQL: " . $e->getMessage());
             return false;
        }
    }

    // =====================================================================
    // 3. MÉTODOS DE CONSULTA (Para el listado y visualización)
    // =====================================================================
    /**
     * Consulta las marcas registradas aplicando el borrado lógico por estado
     */
    public function listarMarcas(string $estado = 'Activo'): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT 
                        m.id_marca, m.estilo, m.distancia_m, m.tipo_piscina, 
                        m.tiempo_final_seg, m.nivel_evento, m.fecha, m.es_pb,
                        CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta, a.cedula
                    FROM marcas m
                    INNER JOIN atletas a ON m.id_atleta = a.id_atleta
                    WHERE m.estado = :estado
                    ORDER BY m.id_marca DESC";
                    
            $stmt = $conex->prepare($sql);
            $stmt->execute([':estado' => $estado]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Hace un Soft Delete (Baja lógica) cambiando el estado del registro
     */
    public function eliminarMarca(int $id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "UPDATE marcas SET estado = 'Inactivo' WHERE id_marca = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}