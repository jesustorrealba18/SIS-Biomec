<?php
namespace Tests\Integration\horario;

require_once __DIR__ . '/BaseHorarioTestCase.php';

use GrupoProyecto\SisBiomec\modelo\horario;

class HorarioModeloTest extends BaseHorarioTestCase
{
    private horario $modelo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelo = new horario($_ENV['DB_NAME_NATACION']);
    }

    public function testRegistrarHorarioExitoso(): void
    {
        $this->modelo->setDatos([
            'dia_semana'  => 'Lunes',
            'hora_inicio' => '08:00',
            'hora_fin'    => '10:00',
        ]);

        $this->assertTrue($this->modelo->registrarHorario(), "Debería registrar el horario. Errores: " . print_r($this->modelo->validarDatos(), true));

        $horario = $this->db->query("SELECT * FROM bloques_horarios WHERE dia_semana = 'Lunes'")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($horario);
        $this->assertSame('Activo', $horario['estado']);
    }

    public function testRegistrarFallaPorSolapamiento(): void
    {
        $this->db->exec("INSERT INTO bloques_horarios (dia_semana, hora_inicio, hora_fin, estado) VALUES ('Lunes', '08:00:00', '10:00:00', 'Activo')");

        $this->modelo->setDatos([
            'dia_semana'  => 'Lunes',
            'hora_inicio' => '09:00',
            'hora_fin'    => '11:00',
        ]);

        $this->assertFalse($this->modelo->registrarHorario(), "No debería permitir el solapamiento.");
        $errores = $this->modelo->validarDatos();
        $this->assertArrayHasKey('horario', $errores);
    }

    public function testEditarYDesactivarHorario(): void
    {
        $this->db->exec("INSERT INTO bloques_horarios (dia_semana, hora_inicio, hora_fin, estado) VALUES ('Martes', '14:00:00', '16:00:00', 'Activo')");
        $id = (int)$this->db->lastInsertId();

        $this->modelo->setDatos([
            'id_bloque'   => $id,
            'dia_semana'  => 'Martes',
            'hora_inicio' => '15:00',
            'hora_fin'    => '17:00',
        ]);

        $this->assertTrue($this->modelo->editarHorario(), "Debería editar correctamente.");
        
        $horario = $this->db->query("SELECT * FROM bloques_horarios WHERE id_bloque = {$id}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('15:00:00', $horario['hora_inicio']);

        $this->modelo->setDatos(['id_bloque' => $id]);
        $this->assertTrue($this->modelo->eliminarHorario());
        
        $estado = $this->db->query("SELECT estado FROM bloques_horarios WHERE id_bloque = {$id}")->fetchColumn();
        $this->assertSame('Inactivo', $estado);
    }

    public function testReactivarHorarioFallaSiHaySolapamiento(): void
    {
        $this->db->exec("INSERT INTO bloques_horarios (dia_semana, hora_inicio, hora_fin, estado) VALUES ('Miercoles', '08:00:00', '10:00:00', 'Activo')");
        
        $this->db->exec("INSERT INTO bloques_horarios (dia_semana, hora_inicio, hora_fin, estado) VALUES ('Miercoles', '09:00:00', '11:00:00', 'Inactivo')");
        $idInactivo = (int)$this->db->lastInsertId();

        $this->modelo->setDatos(['id_bloque' => $idInactivo]);
        
        $this->assertFalse($this->modelo->reactivarHorario(), "No debería permitir reactivar si hay solapamiento.");
    }
}