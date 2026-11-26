<?php
namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;

class GruposSmokeTest extends TestCase
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

    public function testVistaGruposCargaSinErroresFatales(): void
    {
        $res = $this->get('/?p=grupo');
        
        $this->assertNotEquals(500, $res['code'], 'La ruta de Grupos rompió el servidor.');
        $this->assertStringNotContainsString('Fatal error', $res['body'], 'Hay un error fatal de PHP en Grupos.');
    }

    public function testApiListarGruposNoRompeElServidor(): void
    {
        $res = $this->get('/?p=grupo&accion=listarGrupos');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiListarEntrenadoresNoRompeElServidor(): void
    {
        $res = $this->get('/?p=grupo&accion=listarEntrenador');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiListarCategoriasNoRompeElServidor(): void
    {
        $res = $this->get('/?p=grupo&accion=listarCategorias');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiListarAtletasDisponiblesNoRompeElServidor(): void
    {
        $res = $this->get('/?p=grupo&accion=listarAtletasDisponibles');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testApiObtenerGrupoConIdInvalidoNoRompe(): void
    {
        $res = $this->get('/?p=grupo&accion=obtenerGrupo&id=0');
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionGuardarGrupoConDatosVaciosNoRompeElServidor(): void
    {
        $res = $this->post('/?p=grupo&accion=guardarGrupo', ['accion' => 'guardarGrupo']);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionEliminarGrupoConIdInvalidoNoRompe(): void
    {
        $res = $this->post('/?p=grupo&accion=eliminar', ['id_grupo' => 0]);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionAsignarAtletasSinDatosNoRompe(): void
    {
        $res = $this->post('/?p=grupo&accion=asignarAtletas', []);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }

    public function testAccionInvalidaNoRompeElServidor(): void
    {
        $res = $this->post('/?p=grupo', ['accion' => 'accionInexistente']);
        $this->assertNotEquals(500, $res['code']);
        $this->assertStringNotContainsString('Fatal error', $res['body']);
    }
}