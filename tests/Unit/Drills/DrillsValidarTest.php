<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\drills;

class DrillsValidarTest extends TestCase
{
    private drills $drill;
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("CREATE TABLE drills (id_drill INTEGER PRIMARY KEY, nombre TEXT)");

        $this->drill = new drills();
        $ref = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $prop = $ref->getProperty('conex1');
        $prop->setAccessible(true);
        $prop->setValue($this->drill, $this->pdo);
    }

    private function datosValidos(array $o = []): array
    {
        return array_merge([
            'nombre' => 'Crol Continuo',
            'estilo' => 'Libre',
            'categoria' => 'Tecnico',
            'enfoque_tecnico' => 'Respiración bilateral',
            'descripcion' => 'Ejercicio de nado continuo para resistencia aeróbica.',
            'instrucciones' => 'Nadar a ritmo constante manteniendo la cabeza alineada.',
            'metraje_sugerido' => '4x100m',
            'dificultad' => 'Basico',
            'material_requerido' => 'Ninguno',
        ], $o);
    }

    public function testDatosValidosNoGeneranErrores(): void
    {
        $this->drill->setDatos($this->datosValidos());
        $this->assertEmpty($this->drill->validarDatos());
    }

    public function testEstiloInvalidoFalla(): void
    {
        $this->drill->setDatos($this->datosValidos(['estilo' => 'Inventado']));
        $this->assertArrayHasKey('estilo', $this->drill->validarDatos());
    }

    public function testMetrajeInvalidoFalla(): void
    {
        $this->drill->setDatos($this->datosValidos(['metraje_sugerido' => 'Cuatrocientos metros']));
        $this->assertArrayHasKey('metraje_sugerido', $this->drill->validarDatos());
    }

    public function testMetrajeFormatoXEsValido(): void
    {
        $this->drill->setDatos($this->datosValidos(['metraje_sugerido' => '8x50m']));
        $this->assertArrayNotHasKey('metraje_sugerido', $this->drill->validarDatos());
    }

    public function testNombreDuplicadoFalla(): void
    {
        $this->pdo->exec("INSERT INTO drills (nombre) VALUES ('Crol Continuo')");
        $this->drill->setDatos($this->datosValidos(['nombre' => 'Crol Continuo']));
        $this->assertArrayHasKey('nombre', $this->drill->validarDatos());
    }

    public function testEditarConMismoNombrePasa(): void
    {
        $this->pdo->exec("INSERT INTO drills (id_drill, nombre) VALUES (1, 'Crol Continuo')");
        $this->drill->setDatos($this->datosValidos(['id_drill' => '1']));
        $errores = $this->drill->validarDatos('editar', 1);
        $this->assertArrayNotHasKey('nombre', $errores);
    }
}