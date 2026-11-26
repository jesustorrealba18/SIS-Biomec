<?php
namespace Tests\Integration\entrenador;

use Tests\Integration\sesiones\BaseDatabaseTestCase;
use PDOException;

abstract class BaseEntrenadorTestCase extends BaseDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        try { $this->db->exec("TRUNCATE TABLE entrenador"); } catch (\PDOException $e) {}
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function crearEntrenadorPersistido(string $cedula = 'V-12345678'): int
    {
        $sql = "INSERT INTO entrenador (cedula, nombres, apellidos, fecha_nacimiento, genero, correo, telefono, direccion, estado) 
                VALUES (:cedula, 'Carlos', 'Pérez', '1985-05-05', 'M', 'carlos@test.com', '04141234567', 'Calle 1', 'Activo')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cedula' => $cedula]);
        return (int)$this->db->lastInsertId();
    }
}