<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class NormalizacionTiempo extends Conexion {
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
        'id_normalizacion', 'id_atleta', 'estilo', 'distancia_m',
        'tipo_piscina_origen', 'tiempo_original_seg', 'tiempo_convertido_seg',
        'fecha_registro', 'accion'
    ];

    // NO SE DECLARA CONSTRUCTOR: PHP invoca automáticamente el de la clase Conexion.

    // =====================================================================
    // 2. HIDRATACIÓN Y DEPURACIÓN INTERNA (MÉTODOS PRIVADOS)
    // =====================================================================
    /**
     * Mapea el payload externo al arreglo privado filtrando campos basura.
     */
    private function hidratar(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo])) {
                $this->datos[$campo] = trim($payload[$campo]);
            }
        }
    }

    // =====================================================================
    // 3. LÓGICA DE NEGOCIO (Algoritmo matemático RF-08)
    // =====================================================================
    /**
     * Calcula la normalización del tiempo basándose en la distancia y los virajes.
     */
    private function calcularNormalizacion(): void {
        // Obtenemos el tiempo en segundos como flotante puro
        $tiempoOriginal = (float) $this->datos['tiempo_original_seg'];
        $factor_conversion = 0.02; // Ejemplo: 2% de variación
        
        if ($this->datos['tipo_piscina_origen'] === '25m') {
            // De Corta a Larga: El tiempo es mayor
            $this->datos['tiempo_convertido_seg'] = $tiempoOriginal + ($tiempoOriginal * $factor_conversion);
        } else if ($this->datos['tipo_piscina_origen'] === '50m') {
            // De Larga a Corta: El tiempo es menor
            $this->datos['tiempo_convertido_seg'] = $tiempoOriginal - ($tiempoOriginal * $factor_conversion);
        } else {
            $this->datos['tiempo_convertido_seg'] = $tiempoOriginal; 
        }
        
        // Formateo final a 2 decimales
        $this->datos['tiempo_convertido_seg'] = round($this->datos['tiempo_convertido_seg'], 2);
    }

    // =====================================================================
    // 4. CAPA DE PERSISTENCIA Y TRANSACCIONALIDAD (ACID & ZAP)
    // =====================================================================
    
    public function guardarNormalizacion(array $datosPost): array {
        
        // 1. Hidratación segura (puedes reemplazar por $this->autoBind si tu Trait maneja el arreglo interno)
        $this->hidratar($datosPost);
        
        // 2. Validaciones internas
        if(empty($this->datos['id_atleta']) || empty($this->datos['tiempo_original_seg'])) {
            return ["status" => "error", "mensaje" => "Datos incompletos para la normalización."];
        }

        // 3. Ejecutar algoritmo de conversión
        $this->calcularNormalizacion();

        // 4. Cumplimiento de Propiedades ACID
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO normalizacion_tiempos 
                    (id_atleta, estilo, distancia_m, tipo_piscina_origen, tiempo_original_seg, tiempo_convertido_seg, fecha_registro) 
                    VALUES (:id_atleta, :estilo, :distancia_m, :tipo_piscina_origen, :tiempo_original_seg, :tiempo_convertido_seg, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Casting explícito al extraer datos del array para garantizar integridad de tipos (Defensa de Caja Negra)
            $stmt->bindValue(':id_atleta', (int)$this->datos['id_atleta'], PDO::PARAM_INT);
            $stmt->bindValue(':estilo', $this->datos['estilo'], PDO::PARAM_STR);
            $stmt->bindValue(':distancia_m', (int)$this->datos['distancia_m'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_piscina_origen', $this->datos['tipo_piscina_origen'], PDO::PARAM_STR);
            $stmt->bindValue(':tiempo_original_seg', (float)$this->datos['tiempo_original_seg'], PDO::PARAM_STR);
            $stmt->bindValue(':tiempo_convertido_seg', (float)$this->datos['tiempo_convertido_seg'], PDO::PARAM_STR);

            $stmt->execute();
            
            $this->pdo->commit();

            return [
                "status" => "success", 
                "mensaje" => "Tiempo normalizado registrado con éxito.",
                "data_ia" => [
                    "id_atleta" => $this->datos['id_atleta'],
                    "tiempo_original" => $this->datos['tiempo_original_seg'],
                    "tiempo_convertido" => $this->datos['tiempo_convertido_seg']
                ]
            ];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en NormalizacionTiempo: " . $e->getMessage());
            return ["status" => "error", "mensaje" => "Error interno en la base de datos."];
        }
    }

    // En NormalizacionTiempo.php
    public function eliminarNormalizacion($id, $motivo) {
    // 1. Iniciar transacción ACID
    // 2. Realizar UPDATE para marcar como eliminado (borrado lógico)
    // 3. Registrar en la Bitácora de Auditoría
    // 4. Commit o Rollback en caso de error
    }

    // En src/modelo/NormalizacionTiempo.php

public function listar(string $modo = 'activos', int $id_atleta = 0, string $estilo = '', string $distancia = '', string $piscina = ''): array {
    try {
        $sql = "SELECT n.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta
                FROM normalizacion_tiempos n
                INNER JOIN atletas a ON n.id_atleta = a.id_atleta
                WHERE 1=1";
        $params = [];

        if ($modo === 'activos') {
            $sql .= " AND n.estado = 'Activo'";
        } else {
            $sql .= " AND n.estado = 'Inactivo'";
        }

        if ($id_atleta > 0) {
            $sql .= " AND n.id_atleta = :id_atleta";
            $params[':id_atleta'] = $id_atleta;
        }
        if (!empty($estilo)) {
            $sql .= " AND n.estilo = :estilo";
            $params[':estilo'] = $estilo;
        }
        if (!empty($distancia)) {
            $sql .= " AND n.distancia_m = :distancia";
            $params[':distancia'] = (int)$distancia;
        }
        if (!empty($piscina)) {
            $sql .= " AND n.tipo_piscina_origen = :piscina";
            $params[':piscina'] = $piscina;
        }

        $sql .= " ORDER BY n.fecha_registro DESC";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en listar Normalizacion: " . $e->getMessage());
        return [];
    }
}
}