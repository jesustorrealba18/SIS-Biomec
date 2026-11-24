<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Sesiones;

class SesionesCierreTest extends TestCase
{
    public function testVolumenEjecutadoMenorPasa(): void
    {
        $s = new Sesiones();
        $s->setDatos(['volumen_ejecutado' => '500', 'observaciones' => 'ok']);
        $this->assertEmpty($s->validarCierreSesion(1000));
    }

    public function testVolumenEjecutadoSupera(): void
    {
        $s = new Sesiones();
        $s->setDatos(['volumen_ejecutado' => '1500']);
        $this->assertArrayHasKey('volumen_ejecutado', $s->validarCierreSesion(1000));
    }

    public function testVolumenEjecutadoNegativo(): void
    {
        $s = new Sesiones();
        $s->setDatos(['volumen_ejecutado' => '-5']);
        $this->assertArrayHasKey('volumen_ejecutado', $s->validarCierreSesion(1000));
    }

    public function testVolumenEjecutadoRequerido(): void
    {
        $s = new Sesiones();
        $s->setDatos([]);
        $this->assertArrayHasKey('volumen_ejecutado', $s->validarCierreSesion(1000));
    }

    public function testCompletarSinIdDevuelveFalse(): void
    {
        $s = new Sesiones();
        $s->setDatos(['volumen_ejecutado' => '100']);
        $this->assertFalse($s->completarSesion());
    }

    public function testCancelarSinIdDevuelveFalse(): void
    {
        $this->assertFalse((new Sesiones())->cancelarSesion());
    }

    public function testInicializarSinIdDevuelveFalse(): void
    {
        $s = new Sesiones();
        $s->setDatos(['id_sesion' => '0']);
        $this->assertFalse($s->InicializarSesion());
    }

    public function testEditarSinIdDevuelveFalse(): void
    {
        $s = new Sesiones();
        $s->setDatos([
            'id_grupo'         => '1',
            'id_entrenador'    => '1',
            'fecha'            => date('Y-m-d', strtotime('+1 day')),
            'tipo_sesion'      => 'Tecnica',
            'duracion_minutos' => '60',
        ]);
        $this->assertFalse($s->editarSesion());
    }
}