<?php
namespace Tests\Integration\drills;

use Tests\Integration\sesiones\BaseDatabaseTestCase;

abstract class BaseDrillsTestCase extends BaseDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION['id'] = 1;

        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        try { $this->db->exec("TRUNCATE TABLE drills"); } catch (\PDOException $e) {}
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function crearDrillPersistido(string $nombre = 'Drill Base'): int
    {
        $sql = "INSERT INTO drills (nombre, estilo, categoria, enfoque_tecnico, descripcion, instrucciones, metraje_sugerido, dificultad, material_requerido, activo, fecha_creacion) 
                VALUES (:nombre, 'Libre', 'Tecnico', 'Enfoque test', 'Descripción de prueba válida', 'Instrucciones de prueba válidas', '100m', 'Basico', 'Ninguno', 1, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        return (int)$this->db->lastInsertId();
    }
}