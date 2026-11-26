<?php
namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;

class EntrenadoresSmokeTest extends TestCase
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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['code' => $httpCode, 'body' => $response];
    }

    public function testVistaEntrenadoresCargaSinErroresFatales(): void
    {
        $res = $this->get('/?p=entrenador');
        
        $this->assertNotEquals(500, $res['code'], 'La ruta de Entrenadores rompió el servidor.');
        $this->assertStringNotContainsString('Fatal error', $res['body'], 'Hay un error fatal de PHP en Entrenadores.');
    }

    public function testApiListarEntrenadoresNoRompeElServidor(): void
    {
        $res = $this->get('/?p=entrenador&accion=listarEntrenador');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiObtenerEntrenadorConIdInvalidoNoRompe(): void
    {
        $res = $this->get('/?p=entrenador&accion=obtenerEntrenador&id=0');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiDescargarCurriculumInexistenteDevuelve404Controlado(): void
    {
        $url = $this->baseUrl . '/?p=entrenador&accion=descargarCurriculum&id=0';
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertNotEquals(500, $httpCode, 'El servidor falló al intentar descargar un CV inexistente.');
        
        $esRedireccion = ($httpCode === 302);
        $esErrorControlado = ($httpCode === 404);
        
        $this->assertTrue($esRedireccion || $esErrorControlado, "El servidor respondió con un código inesperado: {$httpCode}");
    }

    public function testAccionGuardarEntrenadorConDatosVaciosNoRompeElServidor(): void
    {
        $res = $this->post('/?p=entrenador&accion=guardar', []);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionEliminarEntrenadorConIdInvalidoNoRompe(): void
    {
        $res = $this->post('/?p=entrenador&accion=eliminar', ['id_entrenador' => 0]);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionReactivarEntrenadorConIdInvalidoNoRompe(): void
    {
        $res = $this->post('/?p=entrenador&accion=reactivar', ['id_entrenador' => 0]);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionInvalidaNoRompeElServidor(): void
    {
        $res = $this->post('/?p=entrenador', ['accion' => 'accionInexistente']);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }
}