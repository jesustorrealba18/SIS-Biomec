<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Conexion; // Ajusta según tu clase de conexión

class SmokeTest extends TestCase
{
    /**
     * Prueba de Humo 1: Verificar que la clase de conexión se puede instanciar y conectar.
     */
    public function testConexionBaseDeDatosExitosa()
    {
        try {
            $conexion = new Conexion('sis_natacion');
            $pdo = $conexion->getConex1(); // o el método que uses para obtener el objeto PDO
            $this->assertNotNull($pdo, 'El objeto PDO no debería ser nulo.');
            
            // Ejecutamos una consulta trivial para confirmar la conexión
            $stmt = $pdo->query("SELECT 1");
            $resultado = $stmt->fetchColumn();
            $this->assertEquals(1, $resultado, 'La base de datos no respondió correctamente.');
        } catch (\Exception $e) {
            $this->fail('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Prueba de Humo 2: Verificar que la página de login responde con HTTP 200.
     */
    public function testPaginaLoginCarga()
    {
        // Usamos cURL para hacer una petición real a la URL local
        $url = 'http://localhost/ProyectoPiscinaBolivariana/SIS-Biomec/index.php?p=login';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode, 'La página de login no devolvió un HTTP 200.');
    }
}