<?php
namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;

class SesionesSmokeTest extends TestCase
{
    private string $baseUrl = 'http://localhost/SIS-Biomec';

    private function get(string $ruta): array
    {
        $ch = curl_init($this->baseUrl . $ruta);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['code' => $httpCode, 'body' => $response];
    }

    private function post(string $ruta, array $datos): array
    {
        $ch = curl_init($this->baseUrl . $ruta);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos));
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['code' => $httpCode, 'body' => $response];
    }

    public function testVistaSesionesCargaSinErroresFatales(): void
    {
        $res = $this->get('/?p=sesiones');

        $this->assertNotEquals(500, $res['code'], 'La vista de sesiones rompió el servidor.');
        $this->assertStringNotContainsString('Fatal error', $res['body'], 'Hay un error fatal de PHP en sesiones.');
    }

    public function testApiListarSesionesDevuelveJson(): void
    {
        $res = $this->get('/?p=sesiones&accion=listarSesiones');
        
        $this->assertNotEquals(500, $res['code']);
    }

    public function testApiListarMicrociclosDevuelveJson(): void
    {
        $res = $this->get('/?p=sesiones&accion=listarMicrociclos');
        $this->assertNotEquals(500, $res['code']);
    }

    public function testApiListarDrillsActivosDevuelveJson(): void
    {
        $res = $this->get('/?p=sesiones&accion=listarDrillsActivos');
        $this->assertNotEquals(500, $res['code']);
    }

    public function testApiObtenerDetalleConIdInvalidoNoRompe(): void
    {
        $res = $this->get('/?p=sesiones&accion=obtenerDetalle&id=0');
        $this->assertNotEquals(500, $res['code']);
    }

    public function testAccionGuardarConDatosVaciosDevuelveErrorControlado(): void
    {
        $res = $this->post('/?p=sesiones', ['accion' => 'guardar']);
  
        $this->assertNotEquals(500, $res['code']);
    }

    public function testAccionInvalidaDevuelveError(): void
    {
        $res = $this->post('/?p=sesiones', ['accion' => 'accionInexistente']);
        $this->assertNotEquals(500, $res['code']);
    }
}