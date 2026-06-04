<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;


class Marca extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    // =====================================================================
    // 1. ENCAPSULAMIENTO ESTRICTO
    // =====================================================================
    /**
     * Bolsa de propiedades encapsulada. Único punto de almacenamiento de datos.
     */
    private array $datos = [];

    /**
     * Lista blanca de campos permitidos (Protección contra Asignación Masiva)
     */
    private array $camposPermitidos = [
        'id_atleta', 'id_sesion', 'id_evento', 'estilo', 'distancia_m',
        'tipo_piscina', 'tiempo_final_seg', 'tiempo_reaccion_seg',
        'tiempo_viraje_seg', 'nivel_evento', 'fecha', 'observaciones',
        'num_brazadas', 'splits','brazadas_por_largo','id_marca','accion'
    ];

    // NO SE DECLARA CONSTRUCTOR: PHP invoca automáticamente el de la clase Conexion.

    // =====================================================================
    // 2. HIDRATACIÓN Y DEPURACIÓN INTERNA (MÉTODOS PRIVADOS)
    // =====================================================================
    /**
     * Mapea el payload externo al arreglo privado filtrando campos basura.
     */
   /*  private function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo]) && $payload[$campo] !== '') {
                $this->datos[$campo] = $payload[$campo];
            } else {
                $this->datos[$campo] = null;
            }
        }
    } */

    /**
     * Mapea el payload externo al arreglo privado filtrando campos basura.
     * Soporta de forma segura variables escalares y arreglos estructurados (Splits).
     */
    private function setAtributos(array $payload): void {
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

    /**
     * Aplica las reglas de negocio sobre el estado del objeto.
     */
    private function validarAtributosInternos(): bool {
        $this->resetearErrores();

        // Validaciones obligatorias extrayendo de la bolsa encapsulada
        $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        $this->requerido((string)($this->datos['estilo'] ?? ''), 'estilo');
        $this->requerido((string)($this->datos['distancia_m'] ?? ''), 'distancia_m');
        $this->requerido((string)($this->datos['tipo_piscina'] ?? ''), 'tipo_piscina');
        $this->requerido((string)($this->datos['tiempo_final_seg'] ?? ''), 'tiempo_final_seg');
        $this->requerido((string)($this->datos['fecha'] ?? ''), 'fecha');
        $this->requerido((string)($this->datos['nivel_evento'] ?? ''), 'nivel_evento');

        // Regla de integridad de tiempo
        if (!empty($this->datos['fecha']) && $this->datos['fecha'] > date('Y-m-d')) {
            $this->agregarError('fecha', 'La fecha del registro no puede ser futura.');
        }

        return empty($this->obtenerErrores());
    }

    // =====================================================================
    // 3. OPERACIÓN TRANSACCIONAL (BACKEND / NEGOCIO)
    // =====================================================================
   
    /**
     * Registra una marca calculando el PB, el SWOLF e insertando los splits dinámicos
     */
    public function registrarMarca(array $payload): bool {
      
        // 1. Hidratación interna segura
        $this->setAtributos($payload);

        // 2. Autovalidación de integridad
        if (!$this->validarAtributosInternos()) {
            return false; 
        }
        
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
            
            // EL REEMPLAZO DEL EXECUTE: Creamos el mapa y llamamos al autoBind
            $mapaPB = [
                ':id_atleta' => ['id_atleta', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR]
            ];
            // Le pasamos el Statement, el mapa y tu arreglo de $datos original
            $this->autoBind($stmtPB, $mapaPB, $this->datos); 
            
            $stmtPB->execute(); // Se ejecuta vacío, sin arreglos adentro
            $historial = $stmtPB->fetch(PDO::FETCH_ASSOC);
            
            $es_pb = (empty($historial['mejor_tiempo']) || $tiempo_final < (float)$historial['mejor_tiempo']) ? 1 : 0;

            // -------------------------------------------------------------
            // B) INSERTAR EN TABLA PRINCIPAL: `marcas`
            // -------------------------------------------------------------
            $sqlInsert = "INSERT INTO marcas (id_atleta, id_sesion, id_evento, estilo, distancia_m, tipo_piscina, tiempo_final_seg, tiempo_reaccion_seg, tiempo_viraje_seg, nivel_evento, es_pb, fecha, observaciones) 
                          VALUES (:id_atleta, :id_sesion, :id_evento, :estilo, :distancia, :piscina, :tiempo, :reaccion, :viraje, :nivel, :es_pb, :fecha, :obs)";
            
            $stmt = $this->pdo->prepare($sqlInsert);
            
            // EL MAPA PRINCIPAL: Aquí definimos de qué llave del $datos sale cada variable
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
                ':es_pb'     => ['es_pb_local', PDO::PARAM_INT], // Fíjate que le puse otro nombre
                ':fecha'     => ['fecha', PDO::PARAM_STR],
                ':obs'       => ['observaciones', PDO::PARAM_STR]
            ];

            // Pasamos el array $datos, PERO también le inyectamos la variable local $es_pb 
            // que calculaste arriba, para que el autoBind la encuentre con el nombre 'es_pb_local'.
            $this->autoBind($stmt, $mapaPrincipal, $this->datos, ['es_pb_local' => $es_pb]);
            
            $stmt->execute(); // Nuevamente, ejecuta limpio
            $id_marca_insertada = $this->pdo->lastInsertId();

           // -------------------------------------------------------------
            // C) CÁLCULO SWOLF
            // -------------------------------------------------------------
            // CORRECCIÓN: Leemos desde $this->datos
            if (!empty($this->datos['brazadas_por_largo'])) {
                $brazadas = (int)$this->datos['brazadas_por_largo'];
                
                // CORRECCIÓN: Leemos tipo_piscina y distancia_m desde $this->datos
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
            // D) TRAMOS TRANSACCIONALES (Splits)
            // -------------------------------------------------------------
            // CORRECCIÓN: Leemos el arreglo de splits desde $this->datos
            if (!empty($this->datos['splits']) && is_array($this->datos['splits'])) {
                $sqlSplit = "INSERT INTO marcas_splits (id_marca, parcial_numero, distancia_parcial_m, tiempo_parcial_seg) 
                             VALUES (:id_marca, :numero, :distancia_parcial, :tiempo_parcial)";
                $stmtSplit = $this->pdo->prepare($sqlSplit);
                
                $numeroSplit = 1;
                // CORRECCIÓN: Iteramos sobre $this->datos['splits']
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
           error_log("ERROR REAL DE SQL: " . $e->getMessage()); 
            error_log("TRACE: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Actualiza una marca existente, recalculando el PB y reescribiendo sus métricas asociadas.
     */
    public function actualizarMarca(array $payload, int $id_marca): bool {
        
        // 1. Hidratación y validación estricta
        $this->setAtributos($payload);

        if (!$this->validarAtributosInternos()) {
            return false; 
        }

        try {
            $this->pdo->beginTransaction();
            $tiempo_final = (float)$this->datos['tiempo_final_seg'];

            // -------------------------------------------------------------
            // A) RECALCULAR PERSONAL BEST (Excluyendo la marca actual)
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
            
            // Pasamos el ID actual como variable local para excluirlo del cálculo
            $this->autoBind($stmtPB, $mapaPB, $this->datos, ['id_marca_actual' => $id_marca]); 
            
            $stmtPB->execute();
            $historial = $stmtPB->fetch(PDO::FETCH_ASSOC);
            
            $es_pb = (empty($historial['mejor_tiempo']) || $tiempo_final < (float)$historial['mejor_tiempo']) ? 1 : 0;

            // -------------------------------------------------------------
            // B) ACTUALIZACIÓN DE LA TABLA PRINCIPAL
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
            // C) LIMPIEZA DE TABLAS SECUNDARIAS (Delete & Re-insert Pattern)
            // -------------------------------------------------------------
            // Es más seguro y rápido borrar los tramos viejos y crear los nuevos 
            // que intentar hacer un UPDATE fila por fila averiguando qué cambió.
            
            $stmtDelSwolf = $this->pdo->prepare("DELETE FROM marcas_swolf WHERE id_marca = :id");
            $stmtDelSwolf->bindValue(':id', $id_marca, PDO::PARAM_INT);
            $stmtDelSwolf->execute();

            $stmtDelSplits = $this->pdo->prepare("DELETE FROM marcas_splits WHERE id_marca = :id");
            $stmtDelSplits->bindValue(':id', $id_marca, PDO::PARAM_INT);
            $stmtDelSplits->execute();

            // -------------------------------------------------------------
            // D) RE-INSERCIÓN DE MÉTRICAS (SWOLF Y SPLITS)
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
            error_log("Error en actualizacion de marca: " . $e->getMessage());
            return false;
        }
    }

  // =====================================================================
    // 3. MÉTODOS DE CONSULTA Y ESTADO (Listados y Soft Delete)
    // =====================================================================

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
            
            // CONCATENACIÓN DINÁMICA ULTRA-EFICIENTE
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
            
            // ENLACES ESTRICTOS
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
     * Hace un Soft Delete guardando la justificación de auditoría
     */
    public function eliminarMarca(int $id, string $motivo): bool {
        try {
            $sql = "UPDATE marcas 
                    SET estado = 'Inactivo', motivo_eliminacion = :motivo 
                    WHERE id_marca = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Tipado fuerte y limpieza del string de entrada
            $stmt->bindValue(':motivo', trim($motivo), PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en eliminarMarca: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reactiva la marca y limpia el historial de eliminación
     */
    public function reactivarMarca(int $id): bool {
        try {
            $sql = "UPDATE marcas 
                    SET estado = 'Activo', motivo_eliminacion = NULL 
                    WHERE id_marca = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Tipado fuerte para el ID numérico
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en reactivarMarca: " . $e->getMessage());
            return false;
        }
    }

   /**
     * Extrae el desglose científico de una marca y la cronología evolutiva del atleta.
     * Genera un payload anidado ideal para renderizado de Dashboards y Gráficas.
     */
    public function obtenerDetallePorId(int $id_marca): ?array {
        
        // Ya no instanciamos $conex. Usamos directamente $this->pdo heredado de la clase Conexion.
        try {
            // 1. Datos base de la marca e identificación del atleta
            $sqlBase = "SELECT m.*, CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta, a.cedula 
                        FROM marcas m 
                        INNER JOIN atletas a ON m.id_atleta = a.id_atleta 
                        WHERE m.id_marca = :id_marca";
            
            $stmtBase = $this->pdo->prepare($sqlBase);
            // Tipado estricto (Seguridad contra inyección y type-juggling)
            $stmtBase->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtBase->execute();
            
            $marca = $stmtBase->fetch(PDO::FETCH_ASSOC);

            // Si el ID no existe en la BD, abortamos y retornamos nulo tempranamente (Early Return)
            if (!$marca) return null;

            // 2. Desglose transaccional de Splits (RF-06) cada 25m/50m
            $sqlSplits = "SELECT distancia_parcial_m, tiempo_parcial_seg 
                          FROM marcas_splits 
                          WHERE id_marca = :id_marca 
                          ORDER BY parcial_numero ASC";
            
            $stmtSplits = $this->pdo->prepare($sqlSplits);
            $stmtSplits->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtSplits->execute();
            
            $marca['splits'] = $stmtSplits->fetchAll(PDO::FETCH_ASSOC);

            // 3. Métrica de Eficiencia SWOLF (RF-07) de la tabla externa 1 a 1
            $sqlSwolf = "SELECT num_brazadas, swolf FROM marcas_swolf WHERE id_marca = :id_marca";
            
            $stmtSwolf = $this->pdo->prepare($sqlSwolf);
            $stmtSwolf->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtSwolf->execute();
            
            // Usamos el operador ternario corto (?:) para evitar guardar un booleano false si no hay SWOLF
            $marca['swolf_data'] = $stmtSwolf->fetch(PDO::FETCH_ASSOC) ?: null;

            // 4. Serie temporal para la Gráfica de Evolución (CA-06.4)
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
            
            // Casting explícito al extraer datos del array para garantizar integridad de tipos en MySQL
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