<?php
namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;

class AsignacionesSmokeTest extends TestCase
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

    public function testVistaAsignacionesCargaSinErroresFatales(): void
    {
        $res = $this->get('/?p=asignacion');
        
        $this->assertNotEquals(500, $res['code'], 'La ruta de Asignaciones rompió el servidor.');
        $this->assertStringNotContainsString('Fatal error', $res['body'], 'Hay un error fatal de PHP en Asignaciones.');
    }

    public function testApiListarAsignacionesNoRompeElServidor(): void
    {
        $res = $this->get('/?p=asignacion&accion=listarAsignaciones');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiListarCarrilesNoRompeElServidor(): void
    {
        $res = $this->get('/?p=asignacion&accion=listarCarriles');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiListarHorariosNoRompeElServidor(): void
    {
        $res = $this->get('/?p=asignacion&accion=listarHorarios');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiListarGruposParaSelectNoRompeElServidor(): void
    {
        $res = $this->get('/?p=asignacion&accion=listarGruposParaSelect');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiObtenerAsignacionConIdInvalidoNoRompe(): void
    {
        $res = $this->get('/?p=asignacion&accion=obtenerAsignacion&id=0');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiVerificarVencidasNoRompeElServidor(): void
    {
        $res = $this->get('/?p=asignacion&accion=verificarVencidas');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiCarrilesDisponiblesNoRompeElServidor(): void
    {
        $res = $this->get('/?p=asignacion&accion=carrilesDisponibles&dia=Lunes&hora_inicio=08:00&hora_fin=10:00');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    // --- PRUEBAS DE HUMO POST ---

    public function testAccionGuardarAsignacionVaciaNoRompeElServidor(): void
    {
        $res = $this->post('/?p=asignacion', []);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionEliminarAsignacionConIdInvalidoNoRompe(): void
    {
        $res = $this->post('/?p=asignacion', ['accion' => 'eliminar', 'id_asignacion' => 0]);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionCompletarAsignacionConIdInvalidoNoRompe(): void
    {
        $res = $this->post('/?p=asignacion', ['accion' => 'completar', 'id_asignacion' => 0]);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionCambiarEstadoConDatosVaciosNoRompe(): void
    {
        $res = $this->post('/?p=asignacion', ['accion' => 'cambiarEstado', 'id_asignacion' => 0, 'estado' => '']);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }
}