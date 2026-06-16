<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;


class Marca extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    
    private array $datos = [];

    /**
     * Lista blanca de campos permitidos (Protección contra Asignación Masiva)
     */
    private array $camposPermitidos = [
        'id_atleta', 'id_sesion', 'id_evento', 'estilo', 'distancia_m',
        'tipo_piscina', 'tiempo_final_seg', 'tiempo_reaccion_seg',
        'tiempo_viraje_seg', 'nivel_evento', 'fecha', 'observaciones',
        'num_brazadas', 'splits','brazadas_por_largo','id_marca','accion','motivo_eliminacion'
    ];

    
    /**
     * Mapea el payload externo  filtrando campos basura.
     * Soporta de forma segura variables escalares y arreglos estructurados (Splits).
     */
    public function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo])) {
                // Si el campo es un arreglo (como los splits), lo guardamos directo sin comparar con string
                if (is_array($payload[$campo])) {
                    $this->datos[$campo] = $payload[$campo];
                } 
                // Si es un dato normal, validamos que no esté vacío
                elseif ($payload[$campo] !== '') {
                    $this->datos[$campo] = $payload[$campo];
                } else {
                    $this->datos[$campo] = null;
                }
            } else {
                $this->datos[$campo] = null;
            }
        }
    }

    public function getCampo(string $clave) {
        return $this->datos[$clave] ?? null;
    }

    public function obtenerDatos(): array {
        return $this->datos;
    }

   
    private function validarAtributosInternos(): bool {
        $this->resetearErrores();

        $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        $this->requerido((string)($this->datos['estilo'] ?? ''), 'estilo');
        $this->requerido((string)($this->datos['distancia_m'] ?? ''), 'distancia_m');
        $this->requerido((string)($this->datos['tipo_piscina'] ?? ''), 'tipo_piscina');
        $this->requerido((string)($this->datos['tiempo_final_seg'] ?? ''), 'tiempo_final_seg');
        $this->requerido((string)($this->datos['fecha'] ?? ''), 'fecha');
        $this->requerido((string)($this->datos['nivel_evento'] ?? ''), 'nivel_evento');



        if (!empty($this->datos['fecha']) && $this->datos['fecha'] > date('Y-m-d')) {
            $this->agregarError('fecha', 'La fecha del registro no puede ser futura.');
        }

  

        return empty($this->obtenerErrores());
    }


    /**
     * Valida reglas que son exclusivas de Marcas
     */
    private function validarReglasDeNegocio(): bool {

        // Validamos la regla XOR (O es sesión, o es evento, no ambas)
        if (!empty($this->datos['id_sesion'] ?? null) && !empty($this->datos['id_evento'] ?? null)) {
             $this->agregarError('Sesion/Evento', 'Una marca deportiva no puede registrarse simultáneamente en un entrenamiento y en una competencia.');
            return false;
        }

        // Aquí puedes agregar más if() con reglas personalizadas en el futuro...

        return empty($this->obtenerErrores());
    }

    // =====================================================================
    // OPERACIÓN TRANSACCIONAL (BACKEND)
    // =====================================================================
   
   public function getRegistrarMarca(){

        if (!$this->validarAtributosInternos()) {
            return false; 
        }

        
        if (!$this->validarReglasDeNegocio()) {
            return false;
        }

    return $this->registrarMarca();
   }

    public function getActualizarMarca(){

        $id = (int)($this->datos['id_marca'] ?? 0);
        if ($id <= 0) {
            
            $this->agregarError('id', 'No se proporcionó un identificador de marca válido para actualizar.');
            return false;
        }

        if (!$this->validarAtributosInternos()) {
            return false; 
        }

        
        if (!$this->validarReglasDeNegocio()) {
            return false;
        }

        return $this->actualizarMarca();
   }

    public function getEliminarMarca(){


        $id = (int)($this->datos['id_marca'] ?? 0);
        if ($id <= 0) {
            $this->agregarError('id_marca', 'No se proporcionó un identificador válido para archivar el registro.');
            return false;
        }

        // Usamos mb_strlen para contar bien los caracteres con acentos
        $motivo = $this->datos['motivo_eliminacion'] ?? '';
        if (empty($motivo) || mb_strlen($motivo) < 5) {
            $this->agregarError('motivo_eliminacion', 'Debe proporcionar una justificación detallada (mínimo 5 letras) para archivar la marca.');
            return false;
        }


        return $this->eliminarMarca();
   }

    public function getReactivarMarca(){
         $id = (int)($this->datos['id_marca'] ?? 0);
        if ($id <= 0) {
            $this->agregarError('id_marca', 'No se proporcionó un identificador válido para archivar el registro.');
            return false;
        }
        return $this->reactivarMarca();
   }

    private function registrarMarca(): bool {
      
        
       try {
            $this->pdo->beginTransaction();

            $tiempo_final = (float)$this->datos['tiempo_final_seg'];

            // -------------------------------------------------------------
            // A) DETECCIÓN AUTOMÁTICA DE PERSONAL BEST (PB)
            // -------------------------------------------------------------
            $sqlPB = "SELECT MIN(tiempo_final_seg) as mejor_tiempo 
                      FROM marcas 
                      WHERE id_atleta = :id_atleta AND estilo = :estilo 
                      AND distancia_m = :distancia AND tipo_piscina = :piscina AND estado = 'Activo'";
                      
            $stmtPB = $this->pdo->prepare($sqlPB);
            
            
            $mapaPB = [
                ':id_atleta' => ['id_atleta', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR]
            ];
            
            $this->autoBind($stmtPB, $mapaPB, $this->datos); 
            
            $stmtPB->execute(); 
            $historial = $stmtPB->fetch(PDO::FETCH_ASSOC);
            
            $es_pb = (empty($historial['mejor_tiempo']) || $tiempo_final < (float)$historial['mejor_tiempo']) ? 1 : 0;

            // -------------------------------------------------------------
            // INSERTAR EN TABLA PRINCIPAL: `marcas`
            // -------------------------------------------------------------
            $sqlInsert = "INSERT INTO marcas (id_atleta, id_sesion, id_evento, estilo, distancia_m, tipo_piscina, tiempo_final_seg, tiempo_reaccion_seg, tiempo_viraje_seg, nivel_evento, es_pb, fecha, observaciones) 
                          VALUES (:id_atleta, :id_sesion, :id_evento, :estilo, :distancia, :piscina, :tiempo, :reaccion, :viraje, :nivel, :es_pb, :fecha, :obs)";
            
            $stmt = $this->pdo->prepare($sqlInsert);
            
            
            $mapaPrincipal = [
                ':id_atleta' => ['id_atleta', PDO::PARAM_INT],
                ':id_sesion' => ['id_sesion', PDO::PARAM_INT],
                ':id_evento' => ['id_evento', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR],
                ':tiempo'    => ['tiempo_final_seg', PDO::PARAM_STR],
                ':reaccion'  => ['tiempo_reaccion_seg', PDO::PARAM_STR],
                ':viraje'    => ['tiempo_viraje_seg', PDO::PARAM_STR],
                ':nivel'     => ['nivel_evento', PDO::PARAM_STR],
                ':es_pb'     => ['es_pb_local', PDO::PARAM_INT], 
                ':fecha'     => ['fecha', PDO::PARAM_STR],
                ':obs'       => ['observaciones', PDO::PARAM_STR]
            ];

           
            $this->autoBind($stmt, $mapaPrincipal, $this->datos, ['es_pb_local' => $es_pb]);
            
            $stmt->execute(); 
            $id_marca_insertada = $this->pdo->lastInsertId();

           // -------------------------------------------------------------
            // C) CÁLCULO SWOLF
            // -------------------------------------------------------------
          
            if (!empty($this->datos['brazadas_por_largo'])) {
                $brazadas = (int)$this->datos['brazadas_por_largo'];
                
               
                $longitud_piscina = ($this->datos['tipo_piscina'] === '25m') ? 25 : 50;
                $cantidad_largos = (int)$this->datos['distancia_m'] / $longitud_piscina;
                
                $tiempo_por_largo = $tiempo_final / $cantidad_largos;
                $swolf_calculado = (int)round($tiempo_por_largo + $brazadas);

                $sqlSwolf = "INSERT INTO marcas_swolf (id_marca, num_brazadas, swolf) 
                             VALUES (:id_marca, :brazadas, :swolf)";
                $stmtSwolf = $this->pdo->prepare($sqlSwolf);
                
                $stmtSwolf->bindValue(':id_marca', $id_marca_insertada, PDO::PARAM_INT);
                $stmtSwolf->bindValue(':brazadas', $brazadas, PDO::PARAM_INT);
                $stmtSwolf->bindValue(':swolf', $swolf_calculado, PDO::PARAM_INT);
                
                $stmtSwolf->execute();
            }

            // -------------------------------------------------------------
            // TRAMOS TRANSACCIONALES (Splits)
            // -------------------------------------------------------------
            
            if (!empty($this->datos['splits']) && is_array($this->datos['splits'])) {
                $sqlSplit = "INSERT INTO marcas_splits (id_marca, parcial_numero, distancia_parcial_m, tiempo_parcial_seg) 
                             VALUES (:id_marca, :numero, :distancia_parcial, :tiempo_parcial)";
                $stmtSplit = $this->pdo->prepare($sqlSplit);
                
                $numeroSplit = 1;
                
                foreach ($this->datos['splits'] as $dist_parcial => $tiempo_seg) {
                    $tiempoParcialLimpio = (float)$tiempo_seg; 
                    
                    if ($tiempoParcialLimpio > 0) {
                        $stmtSplit->bindValue(':id_marca', $id_marca_insertada, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':numero', $numeroSplit, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':distancia_parcial', (int)$dist_parcial, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':tiempo_parcial', $tiempoParcialLimpio, PDO::PARAM_STR);
                        
                        $stmtSplit->execute();
                        $numeroSplit++;
                    }
                }
            }

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();

            if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
           error_log("ERROR REAL DE SQL: " . $e->getMessage()); 
            error_log("TRACE: " . $e->getTraceAsString());
            return false;
        }
    }

   
    private function actualizarMarca(): bool {
        
        $id_marca = (int)$this->datos['id_marca'];

        try {
            $this->pdo->beginTransaction();
            $tiempo_final = (float)$this->datos['tiempo_final_seg'];

            // -------------------------------------------------------------
            // RECALCULAR PERSONAL BEST (Excluyendo la marca actual)
            // -------------------------------------------------------------
            $sqlPB = "SELECT MIN(tiempo_final_seg) as mejor_tiempo 
                      FROM marcas 
                      WHERE id_atleta = :id_atleta AND estilo = :estilo 
                      AND distancia_m = :distancia AND tipo_piscina = :piscina 
                      AND estado = 'Activo' AND id_marca != :id_marca_actual";
                      
            $stmtPB = $this->pdo->prepare($sqlPB);
            
            $mapaPB = [
                ':id_atleta' => ['id_atleta', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR],
                ':id_marca_actual' => ['id_marca_actual', PDO::PARAM_INT]
            ];
            
            
            $this->autoBind($stmtPB, $mapaPB, $this->datos, ['id_marca_actual' => $id_marca]); 
            
            $stmtPB->execute();
            $historial = $stmtPB->fetch(PDO::FETCH_ASSOC);
            
            $es_pb = (empty($historial['mejor_tiempo']) || $tiempo_final < (float)$historial['mejor_tiempo']) ? 1 : 0;

            // -------------------------------------------------------------
            // ACTUALIZACIÓN DE LA TABLA PRINCIPAL
            // -------------------------------------------------------------
            $sqlUpdate = "UPDATE marcas SET 
                            id_sesion = :id_sesion, id_evento = :id_evento, estilo = :estilo, 
                            distancia_m = :distancia, tipo_piscina = :piscina, tiempo_final_seg = :tiempo, 
                            tiempo_reaccion_seg = :reaccion, tiempo_viraje_seg = :viraje, 
                            nivel_evento = :nivel, es_pb = :es_pb, fecha = :fecha, observaciones = :obs
                          WHERE id_marca = :id_marca_condicion";
            
            $stmt = $this->pdo->prepare($sqlUpdate);
            
            $mapaPrincipal = [
                ':id_sesion' => ['id_sesion', PDO::PARAM_INT],
                ':id_evento' => ['id_evento', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR],
                ':tiempo'    => ['tiempo_final_seg', PDO::PARAM_STR],
                ':reaccion'  => ['tiempo_reaccion_seg', PDO::PARAM_STR],
                ':viraje'    => ['tiempo_viraje_seg', PDO::PARAM_STR],
                ':nivel'     => ['nivel_evento', PDO::PARAM_STR],
                ':es_pb'     => ['es_pb_local', PDO::PARAM_INT], 
                ':fecha'     => ['fecha', PDO::PARAM_STR],
                ':obs'       => ['observaciones', PDO::PARAM_STR],
                ':id_marca_condicion' => ['id_marca_condicion', PDO::PARAM_INT]
            ];

            $this->autoBind($stmt, $mapaPrincipal, $this->datos, [
                'es_pb_local' => $es_pb, 
                'id_marca_condicion' => $id_marca
            ]);
            
            $stmt->execute();

            // -------------------------------------------------------------
            // LIMPIEZA DE TABLAS SECUNDARIAS (Delete & Re-insert Pattern)
            // -------------------------------------------------------------
            
            $stmtDelSwolf = $this->pdo->prepare("DELETE FROM marcas_swolf WHERE id_marca = :id");
            $stmtDelSwolf->bindValue(':id', $id_marca, PDO::PARAM_INT);
            $stmtDelSwolf->execute();

            $stmtDelSplits = $this->pdo->prepare("DELETE FROM marcas_splits WHERE id_marca = :id");
            $stmtDelSplits->bindValue(':id', $id_marca, PDO::PARAM_INT);
            $stmtDelSplits->execute();

            // -------------------------------------------------------------
            // RE-INSERCIÓN DE MÉTRICAS (SWOLF Y SPLITS)
            // -------------------------------------------------------------
            if (!empty($this->datos['brazadas_por_largo'])) {
                $brazadas = (int)$this->datos['brazadas_por_largo'];
                $longitud_piscina = ($this->datos['tipo_piscina'] === '25m') ? 25 : 50;
                $cantidad_largos = (int)$this->datos['distancia_m'] / $longitud_piscina;
                
                $swolf_calculado = (int)round(($tiempo_final / $cantidad_largos) + $brazadas);

                $stmtSwolf = $this->pdo->prepare("INSERT INTO marcas_swolf (id_marca, num_brazadas, swolf) VALUES (:id_marca, :brazadas, :swolf)");
                $stmtSwolf->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
                $stmtSwolf->bindValue(':brazadas', $brazadas, PDO::PARAM_INT);
                $stmtSwolf->bindValue(':swolf', $swolf_calculado, PDO::PARAM_INT);
                $stmtSwolf->execute();
            }

            if (!empty($this->datos['splits']) && is_array($this->datos['splits'])) {
                $stmtSplit = $this->pdo->prepare("INSERT INTO marcas_splits (id_marca, parcial_numero, distancia_parcial_m, tiempo_parcial_seg) VALUES (:id_marca, :numero, :distancia_parcial, :tiempo_parcial)");
                
                $numeroSplit = 1;
                foreach ($this->datos['splits'] as $dist_parcial => $tiempo_seg) {
                    $tiempoParcialLimpio = (float)$tiempo_seg; 
                    if ($tiempoParcialLimpio > 0) {
                        $stmtSplit->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':numero', $numeroSplit, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':distancia_parcial', (int)$dist_parcial, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':tiempo_parcial', $tiempoParcialLimpio, PDO::PARAM_STR);
                        $stmtSplit->execute();
                        $numeroSplit++;
                    }
                }
            }

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error en actualizacion de marca: " . $e->getMessage());
            return false;
        }
    }

  // =====================================================================
    // MÉTODOS DE CONSULTA Y ESTADO (Listados y Soft Delete)
    // =====================================================================
    
    private function eliminarMarca(): bool {
        try {
            $sql = "UPDATE marcas 
                    SET estado = 'Inactivo', motivo_eliminacion = :motivo 
                    WHERE id_marca = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':id', (int)$this->datos['id_marca'], PDO::PARAM_INT);
            $stmt->bindValue(':motivo', trim($this->datos['motivo_eliminacion']), PDO::PARAM_STR);
            
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error en eliminarMarca: " . $e->getMessage());
            return false;
        }
    }

   
    private function reactivarMarca(): bool {
        try {
            $sql = "UPDATE marcas 
                    SET estado = 'Activo', motivo_eliminacion = NULL 
                    WHERE id_marca = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':id', (int)$this->datos['id_marca'], PDO::PARAM_INT);
           
            
            return $stmt->execute();
            
        } catch (PDOException $e) {

             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
            
            error_log("Error en reactivarMarca: " . $e->getMessage());
            return false;
        }
    }

    public function listarMarcas(string $estado = 'Activo', int $id_atleta = 0, int $distancia = 0, string $estilo = '', string $piscina = ''): array {
        
        $estadosPermitidos = ['Activo', 'Inactivo'];
        if (!in_array($estado, $estadosPermitidos)) {
            return [];
        }

        try {
            $sql = "SELECT m.id_marca, m.estilo, m.distancia_m, m.tipo_piscina, 
                        m.tiempo_final_seg, m.nivel_evento, m.fecha, m.es_pb, 
                        CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta, a.cedula 
                    FROM marcas m 
                    INNER JOIN atletas a ON m.id_atleta = a.id_atleta 
                    WHERE m.estado = :estado";
            
           
            if ($id_atleta > 0) {
                $sql .= " AND m.id_atleta = :id_atleta";
            }
            if ($distancia > 0) {
                $sql .= " AND m.distancia_m = :distancia";
            }
            if ($estilo !== '') {
                $sql .= " AND m.estilo = :estilo";
            }
            if ($piscina !== '') {
                $sql .= " AND m.tipo_piscina = :piscina";
            }
            
            $sql .= " ORDER BY m.fecha DESC";
            
            $stmt = $this->pdo->prepare($sql);
            
            
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            
            if ($id_atleta > 0) {
                $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            }
            if ($distancia > 0) {
                $stmt->bindValue(':distancia', $distancia, PDO::PARAM_INT);
            }
            if ($estilo !== '') {
                $stmt->bindValue(':estilo', $estilo, PDO::PARAM_STR);
            }
            if ($piscina !== '') {
                $stmt->bindValue(':piscina', $piscina, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error crítico en filtros listarMarcas: " . $e->getMessage());
            return [];
        }
    }


   /**
     * Extrae el desglose científico de una marca y la cronología evolutiva del atleta.
     * Genera un payload anidado ideal para renderizado de Dashboards y Gráficas.
     */
    public function obtenerDetallePorId(int $id_marca): ?array {
        
        try {
            $sqlBase = "SELECT m.*, CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta, a.cedula 
                        FROM marcas m 
                        INNER JOIN atletas a ON m.id_atleta = a.id_atleta 
                        WHERE m.id_marca = :id_marca";
            
            $stmtBase = $this->pdo->prepare($sqlBase);
            $stmtBase->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtBase->execute();
            
            $marca = $stmtBase->fetch(PDO::FETCH_ASSOC);

            if (!$marca) return null;

            $sqlSplits = "SELECT distancia_parcial_m, tiempo_parcial_seg 
                          FROM marcas_splits 
                          WHERE id_marca = :id_marca 
                          ORDER BY parcial_numero ASC";
            
            $stmtSplits = $this->pdo->prepare($sqlSplits);
            $stmtSplits->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtSplits->execute();
            
            $marca['splits'] = $stmtSplits->fetchAll(PDO::FETCH_ASSOC);

            $sqlSwolf = "SELECT num_brazadas, swolf FROM marcas_swolf WHERE id_marca = :id_marca";
            
            $stmtSwolf = $this->pdo->prepare($sqlSwolf);
            $stmtSwolf->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtSwolf->execute();
            
            $marca['swolf_data'] = $stmtSwolf->fetch(PDO::FETCH_ASSOC) ?: null;

            // Serie temporal para la Gráfica de Evolución
            // Filtra estrictamente por las mismas características biométricas de la prueba
            $sqlHistorial = "SELECT fecha, tiempo_final_seg 
                             FROM marcas 
                             WHERE id_atleta = :id_atleta 
                             AND estilo = :estilo 
                             AND distancia_m = :distancia_m 
                             AND tipo_piscina = :tipo_piscina 
                             AND estado = 'Activo' 
                             ORDER BY fecha ASC";
            
            $stmtHistorial = $this->pdo->prepare($sqlHistorial);
            
            $stmtHistorial->bindValue(':id_atleta', (int)$marca['id_atleta'], PDO::PARAM_INT);
            $stmtHistorial->bindValue(':estilo', $marca['estilo'], PDO::PARAM_STR);
            $stmtHistorial->bindValue(':distancia_m', (int)$marca['distancia_m'], PDO::PARAM_INT);
            $stmtHistorial->bindValue(':tipo_piscina', $marca['tipo_piscina'], PDO::PARAM_STR);
            
            $stmtHistorial->execute();
            $marca['historial_evolucion'] = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);

            return $marca;
            
        } catch (PDOException $e) {
            error_log("Error en extractor de detalles: " . $e->getMessage());
            return null;
        }
    }
}