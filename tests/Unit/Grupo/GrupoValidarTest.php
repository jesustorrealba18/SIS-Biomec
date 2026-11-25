<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Grupo;

class GrupoValidarTest extends TestCase
{
    private Grupo $grupo;

    protected function setUp(): void
    {
        $this->grupo = new Grupo();
    }

    public function testDatosValidosNoGeneranErrores(): void
    {
        $this->grupo->setDatos([
            'nombre' => 'Grupo A',
            'descripcion' => 'Entrenamiento de fuerza',
            'id_entrenador' => '5'
        ]);
        $this->assertEmpty($this->grupo->validarDatos());
    }

    public function testNombreRequerido(): void
    {
        $this->grupo->setDatos(['nombre' => '']);
        $this->assertArrayHasKey('nombre', $this->grupo->validarDatos());
    }

    public function testNombreCeroRechazado(): void
    {
        // Este era el bug del empty()
        $this->grupo->setDatos(['nombre' => '0']);
        $this->assertEmpty($this->grupo->validarDatos()); // '0' es un nombre válido en longitud, no debería fallar por vacío
    }

    public function testNombreMuyLargo(): void
    {
        $this->grupo->setDatos(['nombre' => str_repeat('a', 51)]);
        $this->assertArrayHasKey('nombre', $this->grupo->validarDatos());
    }

    public function testNombreConCaracteresInvalidos(): void
    {
        $this->grupo->setDatos(['nombre' => 'Grupo <script>']);
        $this->assertArrayHasKey('nombre', $this->grupo->validarDatos());
    }

    public function testDescripcionMuyLarga(): void
    {
        $this->grupo->setDatos(['nombre' => 'Test', 'descripcion' => str_repeat('a', 256)]);
        $this->assertArrayHasKey('descripcion', $this->grupo->validarDatos());
    }

    public function testIdEntrenadorInvalido(): void
    {
        $this->grupo->setDatos(['nombre' => 'Test', 'id_entrenador' => 'abc']);
        $this->assertArrayHasKey('id_entrenador', $this->grupo->validarDatos());
    }

    public function testIdEntrenadorCero(): void
    {
        $this->grupo->setDatos(['nombre' => 'Test', 'id_entrenador' => '0']);
        $this->assertArrayHasKey('id_entrenador', $this->grupo->validarDatos());
    }

    public function testValidarAsignacionCorrecta(): void
    {
        $this->grupo->setDatosAsignacion(['id_grupo' => 1, 'atletas' => [1, 2, 3]]);
        $this->assertEmpty($this->grupo->validarAsignacion());
    }

    public function testValidarAsignacionSinAtletas(): void
    {
        $this->grupo->setDatosAsignacion(['id_grupo' => 1, 'atletas' => []]);
        $this->assertArrayHasKey('atletas', $this->grupo->validarAsignacion());
    }

    public function testValidarAsignacionAtletaInvalido(): void
    {
        $this->grupo->setDatosAsignacion(['id_grupo' => 1, 'atletas' => [1, 'abc']]);
        $this->assertArrayHasKey('atletas', $this->grupo->validarAsignacion());
    }
}