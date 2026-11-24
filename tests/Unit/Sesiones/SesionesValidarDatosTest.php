<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Sesiones;

class SesionesValidarDatosTest extends TestCase
{
    private Sesiones $sesiones;

    protected function setUp(): void
    {
        $this->sesiones = new Sesiones();
    }

    private function datosValidos(array $override = []): array
    {
        return array_merge([
            'id_entrenador'    => '5',
            'id_grupo'         => '10',
            'id_microciclo'    => '2',
            'fecha'            => date('Y-m-d', strtotime('+1 day')),
            'tipo_sesion'      => 'Tecnica',
            'duracion_minutos' => '60',
            'calentamiento'    => '10 min trote',
            'vuelta_calma'     => 'Estiramientos',
            'observaciones'    => 'Sesión normal',
        ], $override);
    }

    public function testDatosValidosNoGeneranErrores(): void
    {
        $this->sesiones->setDatos($this->datosValidos());
        $errores = $this->sesiones->validarDatos();
        $this->assertEmpty($errores, 'Errores inesperados: ' . json_encode($errores));
    }

    public function testIdEntrenadorRequerido(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['id_entrenador' => '']));
        $this->assertArrayHasKey('id_entrenador', $this->sesiones->validarDatos());
    }

    public function testIdEntrenadorNoNumerico(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['id_entrenador' => 'abc']));
        $this->assertArrayHasKey('id_entrenador', $this->sesiones->validarDatos());
    }

    public function testIdEntrenadorCero(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['id_entrenador' => '0']));
        $this->assertArrayHasKey('id_entrenador', $this->sesiones->validarDatos());
    }

    public function testIdGrupoRequerido(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['id_grupo' => '']));
        $this->assertArrayHasKey('id_grupo', $this->sesiones->validarDatos());
    }

    public function testIdGrupoCero(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['id_grupo' => '0']));
        $this->assertArrayHasKey('id_grupo', $this->sesiones->validarDatos());
    }

    public function testMicrocicloOpcionalVacioPasa(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['id_microciclo' => '']));
        $this->assertArrayNotHasKey('id_microciclo', $this->sesiones->validarDatos());
    }

    public function testFechaRequerida(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['fecha' => '']));
        $this->assertArrayHasKey('fecha', $this->sesiones->validarDatos());
    }

    public function testFechaFormatoInvalido(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['fecha' => '31/12/2030']));
        $this->assertArrayHasKey('fecha', $this->sesiones->validarDatos());
    }

    public function testFechaPasadaRechazada(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['fecha' => date('Y-m-d', strtotime('-1 day'))]));
        $errores = $this->sesiones->validarDatos();
        $this->assertArrayHasKey('fecha', $errores);
        $this->assertStringContainsString('fechas pasadas', $errores['fecha']);
    }

    public function testTipoSesionInvalido(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['tipo_sesion' => 'Inventado']));
        $this->assertArrayHasKey('tipo_sesion', $this->sesiones->validarDatos());
    }

    public function testDuracionMenorA15(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['duracion_minutos' => '10']));
        $errores = $this->sesiones->validarDatos();
        $this->assertArrayHasKey('duracion_minutos', $errores);
        $this->assertStringContainsString('15', $errores['duracion_minutos']);
    }

    public function testDuracionMayorA480(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['duracion_minutos' => '481']));
        $errores = $this->sesiones->validarDatos();
        $this->assertArrayHasKey('duracion_minutos', $errores);
        $this->assertStringContainsString('480', $errores['duracion_minutos']);
    }

    public function testDuracionLimite480Pasa(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['duracion_minutos' => '480']));
        $this->assertArrayNotHasKey('duracion_minutos', $this->sesiones->validarDatos());
    }

    public function testCalentamientoConCaracteresInvalidos(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['calentamiento' => 'Trote <script>']));
        $this->assertArrayHasKey('calentamiento', $this->sesiones->validarDatos());
    }

    public function testCalentamientoMuyLargo(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['calentamiento' => str_repeat('a', 101)]));
        $this->assertArrayHasKey('calentamiento', $this->sesiones->validarDatos());
    }

    public function testObservacionesMuyLargas(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['observaciones' => str_repeat('a', 501)]));
        $this->assertArrayHasKey('observaciones', $this->sesiones->validarDatos());
    }

    public function testSetDatosFiltraCamposNoPermitidos(): void
    {
        $this->sesiones->setDatos($this->datosValidos(['campo_malicioso' => 'DROP']));
        $this->assertNull($this->sesiones->getCampo('campo_malicioso'));
        $this->assertSame('5', $this->sesiones->getCampo('id_entrenador'));
    }
}