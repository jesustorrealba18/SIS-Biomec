<?php
namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;

class DrillsSmokeTest extends TestCase
{
    private string $baseUrl = 'http://localhost/SIS-Biomec';

    /**
     * Helper para simular una petición GET.
     */
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

    /**
     * Helper para simular una petición POST.
     */
    private function post(string $ruta, array $datos): array
    {
        $ch = curl_init($this->baseUrl . $ruta);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['code' => $httpCode, 'body' => $response];
    }

    // --- PRUEBAS DE HUMO GET ---

    public function testVistaDrillsCargaSinErroresFatales(): void
    {
        $res = $this->get('/?p=drills');
        
        $this->assertNotEquals(500, $res['code'], 'La ruta de Drills rompió el servidor.');
        $this->assertStringNotContainsString('Fatal error', $res['body'], 'Hay un error fatal de PHP en Drills.');
    }

    public function testApiListarDrillsNoRompeElServidor(): void
    {
        $res = $this->get('/?p=drills&accion=listarDrills');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiListarDrillsInactivosNoRompeElServidor(): void
    {
        $res = $this->get('/?p=drills&accion=listarDrills&inactivos=1');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiObtenerDrillConIdInvalidoNoRompe(): void
    {
        $res = $this->get('/?p=drills&accion=obtenerDrills&id=0');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiListarAtletasNoRompeElServidor(): void
    {
        $res = $this->get('/?p=drills&accion=listarAtletas');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    // --- PRUEBAS DE HUMO POST ---

    public function testAccionGuardarDrillConDatosVaciosNoRompeElServidor(): void
    {
        $res = $this->post('/?p=drills&accion=guardar', []);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionEliminarDrillConIdInvalidoNoRompe(): void
    {
        $res = $this->post('/?p=drills&accion=eliminar', ['id_drill' => 0]);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionCambiarEstadoConIdInvalidoNoRompe(): void
    {
        $res = $this->post('/?p=drills&accion=cambiarEstado', ['id_drill' => 0, 'estado' => 1]);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionInvalidaNoRompeElServidor(): void
    {
        $res = $this->post('/?p=drills', ['accion' => 'accionInexistente']);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }
}