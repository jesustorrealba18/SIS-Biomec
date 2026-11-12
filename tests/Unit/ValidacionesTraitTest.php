<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\ValidacionesTrait;

/**
 * Clase espia que expone los metodos protegidos del trait para poder
 * evaluarlos de forma aislada, sin base de datos.
 */
class ValidacionesEspia
{
    use ValidacionesTrait;

    public function evalRequerido(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->requerido($valor, $campo);
    }

    public function evalLongitud(string $valor, string $campo, int $min, int $max): bool
    {
        $this->resetearErrores();
        return $this->longitud($valor, $campo, $min, $max);
    }

    public function evalSoloLetras(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->soloLetras($valor, $campo);
    }

    public function evalSoloLetrasyNumeros(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->soloLetrasyNumeros($valor, $campo);
    }

    public function evalFechaValida(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->fechaValida($valor, $campo);
    }

    public function evalFechaNoFutura(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->fechaNoFutura($valor, $campo);
    }

    public function evalEdadMinima(string $valor, string $campo, int $edadMinima = 18): bool
    {
        $this->resetearErrores();
        return $this->edadMinima($valor, $campo, $edadMinima);
    }

    public function evalEdadMaxima(string $valor, string $campo, int $edadMaxima = 100): bool
    {
        $this->resetearErrores();
        return $this->edadMaxima($valor, $campo, $edadMaxima);
    }

    public function evalEnEnum(string $valor, string $campo, array $valores): bool
    {
        $this->resetearErrores();
        return $this->enEnum($valor, $campo, $valores);
    }

    public function evalSoloNumeros(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->soloNumeros($valor, $campo);
    }

    public function evalCorreoValido(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->correoValido($valor, $campo);
    }

    public function evalCedula(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->cedula($valor, $campo);
    }

    public function evalTelefono(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->telefono($valor, $campo);
    }

    public function evalDecimalValido(string $valor, string $campo): bool
    {
        $this->resetearErrores();
        return $this->decimalValido($valor, $campo);
    }

    public function evalAgregarError(string $campo, string $mensaje): void
    {
        $this->agregarError($campo, $mensaje);
    }

    public function errores(): array
    {
        return $this->obtenerErrores();
    }
}

/**
 * CP-UNI: pruebas unitarias del motor de validaciones (ValidacionesTrait).
 * Se evalua cada regla con entradas validas, invalidas y de limite.
 */
class ValidacionesTraitTest extends TestCase
{
    private ValidacionesEspia $v;

    protected function setUp(): void
    {
        $this->v = new ValidacionesEspia();
    }

    // CP-UNI-01
    public function testRequeridoRechazaVacioYEspacios(): void
    {
        $this->assertFalse($this->v->evalRequerido('', 'nombre'));
        $this->assertFalse($this->v->evalRequerido('   ', 'nombre'));
        $this->assertArrayHasKey('nombre', $this->v->errores());
        $this->assertStringContainsString('obligatorio', $this->v->errores()['nombre']);
    }

    // CP-UNI-02
    public function testRequeridoAceptaValorConContenido(): void
    {
        $this->assertTrue($this->v->evalRequerido('contenido', 'nombre'));
        $this->assertSame([], $this->v->errores());
    }

    // CP-UNI-03
    public function testLongitudValidaLimitesInclusivos(): void
    {
        $this->assertTrue($this->v->evalLongitud('ab', 'campo', 2, 3), 'Longitud exacta al minimo debe pasar.');
        $this->assertTrue($this->v->evalLongitud('abc', 'campo', 2, 3), 'Longitud exacta al maximo debe pasar.');
        $this->assertFalse($this->v->evalLongitud('a', 'campo', 2, 3));
        $this->assertFalse($this->v->evalLongitud('abcd', 'campo', 2, 3));
        $this->assertStringContainsString('entre 2 y 3', $this->v->errores()['campo']);
    }

    // CP-UNI-04
    public function testSoloLetrasAceptaAcentosYRechazaNumeros(): void
    {
        $this->assertTrue($this->v->evalSoloLetras('José María Ñuñez', 'nombres'));
        $this->assertFalse($this->v->evalSoloLetras('Juan2', 'nombres'));
        $this->assertStringContainsString('letras y espacios', $this->v->errores()['nombres']);
    }

    // CP-UNI-05
    public function testSoloLetrasyNumerosAceptaSimbolosBasicos(): void
    {
        $this->assertTrue($this->v->evalSoloLetrasyNumeros('Calle 123-A _ok.', 'direccion'));
        $this->assertFalse($this->v->evalSoloLetrasyNumeros('correo@dominio', 'direccion'));
    }

    // CP-UNI-06
    public function testFechaValidaExigeFormatoYCalendarioReal(): void
    {
        $this->assertTrue($this->v->evalFechaValida('2024-02-29', 'fecha'), '2024 es bisiesto.');
        $this->assertFalse($this->v->evalFechaValida('2023-02-29', 'fecha'), '2023 no es bisiesto.');
        $this->assertFalse($this->v->evalFechaValida('2024-13-05', 'fecha'), 'El mes 13 no existe.');
        $this->assertFalse($this->v->evalFechaValida('2024/01/05', 'fecha'));
        // Hallazgo: '2024-1-5' pasa porque el codigo solo hace explode + checkdate
        // (no exige ceros a la izquierda). Se documenta el comportamiento actual.
        $this->assertTrue($this->v->evalFechaValida('2024-1-5', 'fecha'), 'Hallazgo: formato sin ceros pasa la validacion actual.');
    }

    // CP-UNI-07
    public function testFechaNoFuturaRechazaElManana(): void
    {
        $ayer = date('Y-m-d', strtotime('-1 day'));
        $manana = date('Y-m-d', strtotime('+1 day'));
        $this->assertTrue($this->v->evalFechaNoFutura($ayer, 'fecha'));
        $this->assertFalse($this->v->evalFechaNoFutura($manana, 'fecha'));
    }

    // CP-UNI-08
    public function testEdadMinimaRechazaMenoresDeEdad(): void
    {
        $menor = date('Y-m-d', strtotime('-17 years -30 days'));
        $mayor = date('Y-m-d', strtotime('-19 years'));
        $this->assertFalse($this->v->evalEdadMinima($menor, 'fecha_nacimiento'));
        $this->assertStringContainsString('al menos 18', $this->v->errores()['fecha_nacimiento']);
        $this->assertTrue($this->v->evalEdadMinima($mayor, 'fecha_nacimiento'));
        $this->assertTrue($this->v->evalEdadMinima('', 'fecha_nacimiento'), 'Campo vacio se considera opcional.');
    }

    // CP-UNI-09
    public function testEdadMaximaRechazaFechasMuyAntiguas(): void
    {
        $this->assertFalse($this->v->evalEdadMaxima('1890-01-01', 'fecha_nacimiento'));
        $this->assertArrayHasKey('fecha_nacimiento', $this->v->errores());
        $this->assertTrue($this->v->evalEdadMaxima('', 'fecha_nacimiento'));
    }

    // CP-UNI-10
    public function testEnEnumEsSensibleAMayusculas(): void
    {
        $this->assertTrue($this->v->evalEnEnum('M', 'sexo', ['M', 'F']));
        $this->assertFalse($this->v->evalEnEnum('m', 'sexo', ['M', 'F']));
        $this->assertStringContainsString('no permitido', $this->v->errores()['sexo']);
    }

    // CP-UNI-11
    public function testSoloNumerosRechazaLetras(): void
    {
        $this->assertTrue($this->v->evalSoloNumeros('12345', 'distancia'));
        $this->assertFalse($this->v->evalSoloNumeros('12a45', 'distancia'));
    }

    // CP-UNI-12
    public function testCorreoValidoDetectaFormatosInvalidos(): void
    {
        $this->assertTrue($this->v->evalCorreoValido('atleta@club.com', 'correo'));
        $this->assertFalse($this->v->evalCorreoValido('sin-arroba', 'correo'));
        $this->assertFalse($this->v->evalCorreoValido('dos espacios@mail.com', 'correo'));
    }

    // CP-UNI-13
    public function testCedulaAceptaFormatoVenezolano(): void
    {
        $this->assertTrue($this->v->evalCedula('V-12345678', 'cedula'));
        $this->assertTrue($this->v->evalCedula('e-1234567', 'cedula'), 'Se aceptan minusculas v/e.');
        $this->assertFalse($this->v->evalCedula('V-123', 'cedula'), 'Muy corta.');
        $this->assertFalse($this->v->evalCedula('X-1234567', 'cedula'), 'Letra inicial invalida.');
    }

    // CP-UNI-14
    public function testTelefonoValidaRangoDeLongitud(): void
    {
        $this->assertTrue($this->v->evalTelefono('0212-555123', 'telefono'), '11 caracteres permitidos.');
        $this->assertFalse($this->v->evalTelefono('abc', 'telefono'));
        $this->assertFalse($this->v->evalTelefono('0414-1234567', 'telefono'), '12 caracteres excede el maximo.');
    }

    // CP-UNI-15
    public function testDecimalValidoExigePuntoDecimal(): void
    {
        $this->assertTrue($this->v->evalDecimalValido('28.52', 'marca'));
        $this->assertTrue($this->v->evalDecimalValido('28', 'marca'));
        $this->assertFalse($this->v->evalDecimalValido('28,5', 'marca'), 'La coma no es aceptada.');
        $this->assertFalse($this->v->evalDecimalValido('-3.5', 'marca'));
    }

    // CP-UNI-16
    public function testAgregarErrorNoSobrescribeElPrimero(): void
    {
        $this->v->evalAgregarError('campo', 'primer mensaje');
        $this->v->evalAgregarError('campo', 'segundo mensaje');
        $this->assertSame(['campo' => 'primer mensaje'], $this->v->errores());
    }

    // CP-UNI-17
    public function testErroresAcumulanCamposDistintos(): void
    {
        $this->v->evalAgregarError('campo1', 'error uno');
        $this->v->evalAgregarError('campo2', 'error dos');
        $this->assertCount(2, $this->v->errores());
    }
}
