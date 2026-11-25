<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Asignacion;

class AsignacionValidarTest extends TestCase
{
    private Asignacion $asignacion;

    protected function setUp(): void
    {
        $this->asignacion = new Asignacion();
        
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Creamos la tabla con TODAS las columnas que usa el modelo
        $pdo->exec("
            CREATE TABLE asignacion_carril (
                id_asignacion INTEGER PRIMARY KEY AUTOINCREMENT,
                id_carril INTEGER,
                id_bloque_horario INTEGER,
                dia_especifico TEXT,
                fecha_vigencia_inicio TEXT,
                fecha_vigencia_fin TEXT,
                activa INTEGER,
                estado TEXT  -- <--- AGREGAR ESTA LÍNEA
            );
        ");

        $ref = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $prop = $ref->getProperty('conex1');
        $prop->setAccessible(true);
        $prop->setValue($this->asignacion, $pdo);
    }

    public function testDatosValidosNoGeneranErrores(): void
    {
        $this->asignacion->setDatos([
            'id_carril' => '1',
            'id_bloque_horario' => '2',
            'id_grupo' => '3',
            'dia_especifico' => 'Lunes',
            'fecha_vigencia_inicio' => date('Y-m-d'),
            'fecha_vigencia_fin' => date('Y-m-d', strtotime('+1 month')),
            'activa' => '1'
        ]);
        $this->assertEmpty($this->asignacion->validarDatos());
    }

    public function testCarrilCeroFalla(): void
    {
        $this->asignacion->setDatos([
            'id_carril' => '0',
            'id_bloque_horario' => '2',
            'id_grupo' => '3',
            'fecha_vigencia_inicio' => date('Y-m-d'),
            'activa' => '1'
        ]);
        $this->assertArrayHasKey('id_carril', $this->asignacion->validarDatos());
    }

    public function testFechasInvertidasFalla(): void
    {
        $this->asignacion->setDatos([
            'id_carril' => '1',
            'id_bloque_horario' => '2',
            'id_grupo' => '3',
            'fecha_vigencia_inicio' => date('Y-m-d', strtotime('+1 month')),
            'fecha_vigencia_fin' => date('Y-m-d'), // Fin menor al inicio
            'activa' => '1'
        ]);
        $this->assertArrayHasKey('fecha_vigencia_fin', $this->asignacion->validarDatos());
    }

    public function testIdGrupoVacioFalla(): void
    {
        $this->asignacion->setDatos([
            'id_carril' => '1',
            'id_bloque_horario' => '2',
            'id_grupo' => '',
            'fecha_vigencia_inicio' => date('Y-m-d'),
            'activa' => '1'
        ]);
        $this->assertArrayHasKey('id_grupo', $this->asignacion->validarDatos());
    }
}