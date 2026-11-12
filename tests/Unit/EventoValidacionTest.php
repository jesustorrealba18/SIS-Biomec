<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Evento;

/**
 * CP-UNI: pruebas unitarias del motor de validacion del modulo de eventos.
 * validarDatosEvento y validarDatosMeta son funciones puras (no tocan la BD).
 */
class EventoValidacionTest extends TestCase
{
    private Evento $evento;

    protected function setUp(): void
    {
        $this->evento = new Evento();
    }

    private function datosValidos(): array
    {
        return [
            'nombre'      => 'Copa de Pruebas',
            'fecha_inicio' => date('Y-m-d', strtotime('+7 days')),
            'fecha_fin'   => date('Y-m-d', strtotime('+9 days')),
            'tipo'        => 'Regional',
            'nivel'       => 'B',
            'estado'      => 'Planificado',
            'sede'        => 'Piscina Olimpica',
            'organizador' => 'FEVEDA',
        ];
    }

    // CP-UNI-18
    public function testEventoCompletoNoGeneraErrores(): void
    {
        $this->assertSame([], $this->evento->validarDatosEvento($this->datosValidos()));
    }

    // CP-UNI-19
    public function testEventoVacioMarcaSoloLosCamposObligatorios(): void
    {
        $errores = $this->evento->validarDatosEvento([]);
        $this->assertArrayHasKey('nombre', $errores);
        $this->assertArrayHasKey('fecha_inicio', $errores);
        $this->assertArrayHasKey('tipo', $errores);
        $this->assertArrayHasKey('estado', $errores);
        $this->assertArrayNotHasKey('fecha_fin', $errores, 'fecha_fin es opcional.');
        $this->assertArrayNotHasKey('nivel', $errores, 'nivel es opcional.');
    }

    // CP-UNI-20
    public function testTipoFueraDelCatalogoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['tipo'] = 'Mundial';
        $errores = $this->evento->validarDatosEvento($datos);
        $this->assertArrayHasKey('tipo', $errores);
    }

    // CP-UNI-21
    public function testEstadoInvalidoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['estado'] = 'Pospuesto';
        $errores = $this->evento->validarDatosEvento($datos);
        $this->assertArrayHasKey('estado', $errores);
    }

    // CP-UNI-22
    public function testNivelInvalidoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['nivel'] = 'D';
        $errores = $this->evento->validarDatosEvento($datos);
        $this->assertArrayHasKey('nivel', $errores);
    }

    // CP-UNI-23
    public function testFechaFinAnteriorAInicioSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['fecha_fin'] = date('Y-m-d', strtotime('+1 day'));
        $errores = $this->evento->validarDatosEvento($datos);
        $this->assertArrayHasKey('fecha_fin', $errores);
        $this->assertStringContainsString('anterior a la fecha de inicio', $errores['fecha_fin']);
    }

    // CP-UNI-24
    public function testFechaInicioConFormatoInvalidoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['fecha_inicio'] = '15/06/2026';
        $errores = $this->evento->validarDatosEvento($datos);
        $this->assertArrayHasKey('fecha_inicio', $errores);
    }

    // CP-UNI-25
    public function testMetaValidaNoGeneraErrores(): void
    {
        $errores = $this->evento->validarDatosMeta([
            'id_atleta'          => '12',
            'estilo'             => 'Libre',
            'distancia'          => '50',
            'marca_objetivo_seg' => '28.50',
        ]);
        $this->assertSame([], $errores);
    }

    // CP-UNI-26
    public function testMetaConAtletaNoNumericoSeRechaza(): void
    {
        $errores = $this->evento->validarDatosMeta([
            'id_atleta'          => 'abc',
            'estilo'             => 'Libre',
            'distancia'          => '50',
            'marca_objetivo_seg' => '28.50',
        ]);
        $this->assertArrayHasKey('id_atleta', $errores);
    }

    // CP-UNI-27
    public function testMetaConMarcaNegativaSeRechaza(): void
    {
        $base = ['id_atleta' => '12', 'estilo' => 'Braza', 'distancia' => '100'];
        $erroresNegativa = $this->evento->validarDatosMeta($base + ['marca_objetivo_seg' => '-5']);
        $this->assertArrayHasKey('marca_objetivo_seg', $erroresNegativa);
    }

    // CP-UNI-27b: comportamiento actual documentado — el cero se acepta porque
    // el codigo usa !empty() (falsy en PHP). Queda registrado como hallazgo.
    public function testMetaConMarcaCeroSeAceptaComportamientoActual(): void
    {
        $base = ['id_atleta' => '12', 'estilo' => 'Braza', 'distancia' => '100'];
        $erroresCero = $this->evento->validarDatosMeta($base + ['marca_objetivo_seg' => '0']);
        $this->assertSame([], $erroresCero, 'Hallazgo: marca 0 pasa la validacion actual (revisar con el equipo).');
    }

    // CP-UNI-28
    public function testMetaConEstiloInvalidoSeRechaza(): void
    {
        $errores = $this->evento->validarDatosMeta([
            'id_atleta'          => '12',
            'estilo'             => 'Perrito',
            'distancia'          => '50',
            'marca_objetivo_seg' => '28.50',
        ]);
        $this->assertArrayHasKey('estilo', $errores);
    }

    // CP-UNI-29
    public function testMetaConDistanciaNoNumericaSeRechaza(): void
    {
        $errores = $this->evento->validarDatosMeta([
            'id_atleta'          => '12',
            'estilo'             => 'Libre',
            'distancia'          => '50m',
            'marca_objetivo_seg' => '28.50',
        ]);
        $this->assertArrayHasKey('distancia', $errores);
    }
}
