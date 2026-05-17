<?php
use GrupoProyecto\SisBiomec\modelo\Conexion;

class InicioModelo extends Conexion {
    public function __construct() {
        parent::__construct();
    }

    public function consultarAtletasDestacados() {
        $conex = $this->getConex1();
        try {
            // Ejemplo de consulta para tu tabla de atletas
            $sql = "SELECT nombre, apellido, rendimiento, edad FROM atletas ORDER BY rendimiento DESC LIMIT 2";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function consultarTotalesSesiones() {
        // Aquí iría la lógica para obtener el número 2,845 de tu vista
        return 2845; 
    }
}
?>