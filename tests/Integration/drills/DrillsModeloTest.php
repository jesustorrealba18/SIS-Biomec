<?php
namespace Tests\Integration\drills;

require_once __DIR__ . '/BaseDrillsTestCase.php';

use GrupoProyecto\SisBiomec\modelo\drills;

class DrillsModeloTest extends BaseDrillsTestCase
{
    private drills $modelo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelo = new drills($_ENV['DB_NAME_NATACION']);
    }

    public function testRegistrarDrillExitoso(): void
    {
        $this->modelo->setDatos([
            'nombre'             => 'Patada de Mariposa',
            'estilo'             => 'Mariposa',
            'categoria'          => 'Tecnico',
            'enfoque_tecnico'    => 'Mejora de patada',
            'descripcion'        => 'Ejercicio para mejorar la patada de mariposa',
            'instrucciones'      => 'Mantener las piernas juntas y realizar movimiento ondulatorio',
            'metraje_sugerido'   => '4x50m',
            'dificultad'         => 'Intermedio',
            'material_requerido' => 'Aletas',
             'activo'             => 1,
        ]);

        $this->assertTrue($this->modelo->registrarDrills(), "Debería registrar el drill.");

        $drill = $this->db->query("SELECT * FROM drills WHERE nombre = 'Patada de Mariposa'")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($drill);
        $this->assertSame(1, (int)$drill['activo']);
    }

    public function testRegistrarFallaPorNombreDuplicado(): void
    {
        $this->crearDrillPersistido('Drill Existente');

        $this->modelo->setDatos([
            'nombre'             => 'Drill Existente',
            'estilo'             => 'Libre',
            'categoria'          => 'Tecnico',
            'enfoque_tecnico'    => 'Enfoque test',
            'descripcion'        => 'Descripción válida para el test',
            'instrucciones'      => 'Instrucciones válidas para el test',
            'metraje_sugerido'   => '100m',
            'dificultad'         => 'Basico',
            'material_requerido' => 'Ninguno',
        ]);

        $this->assertFalse($this->modelo->registrarDrills());
        $errores = $this->modelo->validarDatos();
        $this->assertArrayHasKey('nombre', $errores);
    }

    public function testEditarDrill(): void
    {
        $id = $this->crearDrillPersistido('Drill Original');

        $this->modelo->setDatos([
            'id_drill'           => $id,
            'nombre'             => 'Drill Editado',
            'estilo'             => 'Espalda',
            'categoria'          => 'Fuerza',
            'enfoque_tecnico'    => 'Nuevo enfoque',
            'descripcion'        => 'Nueva descripción válida',
            'instrucciones'      => 'Nuevas instrucciones válidas',
            'metraje_sugerido'   => '200m',
            'dificultad'         => 'Avanzado',
            'material_requerido' => 'Pullboy',
        ]);

        $this->assertTrue($this->modelo->editarDrills());

        $drill = $this->db->query("SELECT * FROM drills WHERE id_drill = {$id}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('Drill Editado', $drill['nombre']);
        $this->assertSame('Espalda', $drill['estilo']);
    }

    public function testCambiarEstadoYListar(): void
    {
        $id = $this->crearDrillPersistido();

        $this->assertTrue($this->modelo->cambiarEstado($id, 0));
        $activo = $this->db->query("SELECT activo FROM drills WHERE id_drill = {$id}")->fetchColumn();
        $this->assertSame(0, (int)$activo);

        $this->assertCount(0, $this->modelo->listarDrills(false));

        $this->assertCount(1, $this->modelo->listarDrills(true));
    }
}