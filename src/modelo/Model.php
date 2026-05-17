<?php
abstract class Model {
    protected $conn;
    protected $table;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function save() { // Solo Guardado
        // 1. Extraer los atributos de la clase hija (Representante)
        $atributos = get_object_vars($this);
        unset($atributos['conn'], $atributos['table']);

        // 2. Armar SQL Dinámico
        $columnas = array_keys($atributos);
        $nombresColumnas = implode(", ", $columnas);
        $placeholders = ":" . implode(", :", $columnas);

        $query = "INSERT INTO " . $this->table . " ($nombresColumnas) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);

        // 3. Bind de datos seguro
        foreach ($atributos as $key => $value) {
            $stmt->bindValue(":$key", htmlspecialchars(strip_tags((string)$value)));
        }

        // 4. Ejecutar y registrar en Bitácora (¡Esto resuelve la Fase 1!)
        if ($stmt->execute()) {
            $this->registrarBitacora('INSERT', json_encode($atributos));
            return true;
        }
        return false;
    }

    public function saveReturnID() { // Guardado y returna ID
        // 1. Extraer los atributos de la clase hija (Representante)
        $atributos = get_object_vars($this);
        unset($atributos['conn'], $atributos['table']);

        // 2. Armar SQL Dinámico
        $columnas = array_keys($atributos);
        $nombresColumnas = implode(", ", $columnas);
        $placeholders = ":" . implode(", :", $columnas);

        $query = "INSERT INTO " . $this->table . " ($nombresColumnas) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);

        // 3. Bind de datos seguro
        foreach ($atributos as $key => $value) {
            $stmt->bindValue(":$key", htmlspecialchars(strip_tags((string)$value)));
        }

        // 4. Ejecutar y registrar en Bitácora (¡Esto resuelve la Fase 1!)
        if ($stmt->execute()) {
            $this->registrarBitacora('INSERT', json_encode($atributos));
            return $this->conn->lastInsertId();
        }
        return false;
    }


    // Método encapsulado para la Fase 1
    protected function registrarBitacora($accion, $detalles) {
        // Asume usuario 1 por ahora, luego lo cambias por $_SESSION['id']
        $usuario_id = $_SESSION['id'] ?? 1; 
        $query = "INSERT INTO bitacora (usuario_id, modulo, accion, detalle_json) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$usuario_id, $this->table, $accion, $detalles]);
    }
}
?>