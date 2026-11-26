<?php
namespace Tests\Integration\asignacion;

require_once __DIR__ . '/BaseAsignacionTestCase.php';

use GrupoProyecto\SisBiomec\modelo\Asignacion;

class AsignacionModeloTest extends BaseAsignacionTestCase
{
    private Asignacion $modelo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelo = new Asignacion(); 
    }

    public function testRegistrarAsignacionExitosa(): void
    {
        $this->modelo->setDatos([
            'id_carril'             => 1,
            'id_bloque_horario'     => 1,
            'id_grupo'              => 1,
            'fecha_vigencia_inicio' => date('Y-m-d'),
            'fecha_vigencia_fin'    => date('Y-m-d', strtotime('+15 days')),
            'activa'                => 1,
        ]);

        $this->assertTrue($this->modelo->registrarAsignacion(), "Debería registrar la asignación correctamente.");

        $asignacion = $this->db->query("SELECT * FROM asignacion_carril ORDER BY id_asignacion DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($asignacion);
        $this->assertSame(1, (int)$asignacion['id_carril']);
        $this->assertSame(1, (int)$asignacion['activa']);
    }

    public function testRegistrarAsignacionFallaPorChoqueDeHorario(): void
    {
        $this->crearAsignacionPersistida([
            'fecha_vigencia_inicio' => date('Y-m-d'),
            'fecha_vigencia_fin'    => date('Y-m-d', strtotime('+10 days')),
        ]);

        $this->modelo->setDatos([
            'id_carril'             => 1,
            'id_bloque_horario'     => 1,
            'id_grupo'              => 1,
            'fecha_vigencia_inicio' => date('Y-m-d', strtotime('+5 days')),
            'fecha_vigencia_fin'    => date('Y-m-d', strtotime('+15 days')),
            'activa'                => 1,
        ]);

        $this->assertFalse($this->modelo->registrarAsignacion(), "No debería permitir el choque de horarios.");
        
        $errores = $this->modelo->validarDatos();
        $this->assertArrayHasKey('fechas_cruce', $errores);
    }

    public function testEditarAsignacionExitoso(): void
    {
        $id = $this->crearAsignacionPersistida();

        $this->modelo->setDatos([
            'id_asignacion'         => $id,
            'id_carril'             => 2, 
            'id_bloque_horario'     => 2, 
            'id_grupo'              => 1,
            'fecha_vigencia_inicio' => date('Y-m-d', strtotime('+1 days')),
            'fecha_vigencia_fin'    => date('Y-m-d', strtotime('+20 days')),
            'activa'                => 1,
        ]);

        $this->assertTrue($this->modelo->editarAsignacion(), "Debería editar correctamente.");

        $asignacion = $this->db->query("SELECT * FROM asignacion_carril WHERE id_asignacion = {$id}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame(2, (int)$asignacion['id_carril']);
    }

    public function testEliminarYCambiarEstadoAsignacion(): void
    {
        $id = $this->crearAsignacionPersistida();

        $this->assertTrue($this->modelo->desactivarAsignacion($id));
        $activa = $this->db->query("SELECT activa FROM asignacion_carril WHERE id_asignacion = {$id}")->fetchColumn();
        $this->assertSame(0, (int)$activa);

        $this->assertTrue($this->modelo->reactivarAsignacion($id));
        $activa = $this->db->query("SELECT activa FROM asignacion_carril WHERE id_asignacion = {$id}")->fetchColumn();
        $this->assertSame(1, (int)$activa);

        $this->assertTrue($this->modelo->completarAsignacion($id));
        $estado = $this->db->query("SELECT estado FROM asignacion_carril WHERE id_asignacion = {$id}")->fetchColumn();
        $this->assertSame('completada', $estado);

        $this->modelo->setDatos(['id_asignacion' => $id]);
        $this->assertTrue($this->modelo->eliminarAsignacion());
        $count = $this->db->query("SELECT COUNT(*) FROM asignacion_carril WHERE id_asignacion = {$id}")->fetchColumn();
        $this->assertSame(0, (int)$count);
    }

    public function testObtenerCarrilesDisponibles(): void
    {
        $disponibles = $this->modelo->obtenerCarrilesDisponibles();
        $this->assertCount(2, $disponibles);

        $this->crearAsignacionPersistida(['id_carril' => 1]);

        $disponibles = $this->modelo->obtenerCarrilesDisponibles();
        $this->assertCount(1, $disponibles);
        $this->assertSame(2, (int)$disponibles[0]['id_carril']);
    }

    public function testObtenerEstadoCarrilesPorBloque(): void
    {
        $this->crearAsignacionPersistida([
            'id_carril' => 1,
            'id_bloque_horario' => 1,
        ]);

        $estado = $this->modelo->obtenerEstadoCarrilesPorBloque(1);
        
        $this->assertCount(2, $estado); 
        $this->assertTrue($estado[0]['ocupado']);
        $this->assertSame(1, $estado[0]['id_carril']);
        
        $this->assertFalse($estado[1]['ocupado']);
        $this->assertSame(2, $estado[1]['id_carril']);
    }

    public function testListarAsignacionesYCompletadas(): void
    {
        $id1 = $this->crearAsignacionPersistida(); 
        $id2 = $this->crearAsignacionPersistida(['id_carril' => 2, 'id_bloque_horario' => 2]);
        $this->db->exec("UPDATE asignacion_carril SET estado = 'completada', activa = 0 WHERE id_asignacion = {$id2}");

        $activas = $this->modelo->listarAsignaciones(1);
        $this->assertCount(1, $activas);
        $this->assertSame($id1, (int)$activas[0]['id_asignacion']);

        $completadas = $this->modelo->listarAsignacionesCompletadas();
        $this->assertCount(1, $completadas);
        $this->assertSame($id2, (int)$completadas[0]['id_asignacion']);
    }
}