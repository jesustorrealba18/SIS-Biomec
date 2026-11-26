<?php
namespace Tests\Integration\horario;

use Tests\Integration\sesiones\BaseDatabaseTestCase;

abstract class BaseHorarioTestCase extends BaseDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        try { $this->db->exec("TRUNCATE TABLE bloques_horarios"); } catch (\PDOException $e) {}
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}