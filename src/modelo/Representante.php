<?php
require_once 'Model.php';

class Representante extends Model {
    
    protected $table = 'representante'; // Nombre de la tabla

    public $cedula;
    public $nombres;
    public $apellidos;
    public $telefono_principal;
    public $telefono_emergencia;
    public $correo;
    public $parentesco;
    public $direccion_residencia;

    /**
     * Método propio para manejar la relación M:N con Atletas.
     * Inserta en la tabla intermedia y lo registra en la bitácora.
     */
public function vincularAtletas($id_representante, $atletas_ids) {
        $query = "INSERT INTO atleta_representante (id_representante, id_atleta) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);

        foreach ($atletas_ids as $id_atleta) {
            // Si la inserción falla, disparamos el error directo al controlador
            if (!$stmt->execute([$id_representante, $id_atleta])) {
                throw new Exception("Fallo interno al vincular el atleta ID: " . $id_atleta);
            }
        }

        // Si el ciclo terminó sin excepciones, garantizamos que todo fue un éxito
        $detalles = json_encode([
            'id_representante' => $id_representante, 
            'atletas_vinculados' => $atletas_ids
        ]);
        
        $this->registrarBitacora('VINCULAR_ATLETAS', $detalles);

        return true;
    }
}
?>