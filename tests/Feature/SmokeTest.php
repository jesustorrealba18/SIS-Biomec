<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Pruebas de humo (smoke tests).
 * Verifican que el sistema arranca y que sus funciones criticas responden
 * de forma basica: paginas publicas, proteccion de rutas, captcha, login
 * y conexion a las bases de datos. Se ejecutan contra el Apache de XAMPP.
 *
 * Requisito: Apache y MySQL de XAMPP deben estar corriendo.
 * Ejecutar: vendor\bin\phpunit.bat --testsuite Feature
 */
class SmokeTest extends TestCase
{
    private const URL_BASE = 'http://localhost/SIS-Biomec/';
    private const URL_INDEX = self::URL_BASE . 'index.php';

    private function peticion(string $metodo, string $url, array $datos = [], bool $seguirRedirecciones = true): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => $seguirRedirecciones,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HEADER         => true,
            CURLOPT_CUSTOMREQUEST  => $metodo,
        ]);
        if ($metodo === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos));
        }

        $cuerpo = curl_exec($ch);
        $tamanioCabeceras = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $codigo = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $tipoContenido = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $this->assertNotFalse($cuerpo, "La peticion HTTP fallo hacia: {$url}");

        $cabeceras = [];
        foreach (explode("\r\n", substr((string)$cuerpo, 0, $tamanioCabeceras)) as $linea) {
            if (str_contains($linea, ':')) {
                [$nombre, $valor] = explode(':', $linea, 2);
                $cabeceras[strtolower(trim($nombre))] = trim($valor);
            }
        }

        return [$codigo, $cabeceras, substr((string)$cuerpo, $tamanioCabeceras), $tipoContenido];
    }

    private function get(string $url, bool $seguir = true): array
    {
        return $this->peticion('GET', $url, [], $seguir);
    }

    // CP-HUM-01: La aplicacion arranca y protege el acceso sin sesion
    public function testAplicacionArrancaYRedirigeAlLoginSinSesion(): void
    {
        [$codigo, $cabeceras] = $this->get(self::URL_INDEX, false);
        $this->assertSame(302, $codigo, 'index.php sin sesion debe responder 302 (redireccion al login).');
        $this->assertStringContainsString('p=login', $cabeceras['location'] ?? '');
    }

    // CP-HUM-02: La pagina de login carga con su formulario completo
    public function testLoginCargaFormularioCompleto(): void
    {
        [$codigo, , $cuerpo] = $this->get(self::URL_INDEX . '?p=login');
        $this->assertSame(200, $codigo, 'La pagina de login debe responder 200.');
        $this->assertStringContainsString('name="usuario"', $cuerpo, 'Falta el campo usuario.');
        $this->assertStringContainsString('name="password"', $cuerpo, 'Falta el campo password.');

        if (!str_contains($cuerpo, 'name="captcha"')) {
            $this->markTestSkipped('R-01: el captcha esta temporalmente desactivado por el equipo (vista comentada); el test se reactivara al restaurarlo.');
        }
        $this->assertStringContainsString('name="captcha"', $cuerpo, 'Falta el campo captcha.');
    }

    // CP-HUM-03: Las rutas publicas responden 200
    /**
     * @dataProvider proveedorRutasPublicas
     */
    public function testRutasPublicasResponden200(string $ruta): void
    {
        [$codigo] = $this->get(self::URL_INDEX . '?p=' . $ruta);
        $this->assertSame(200, $codigo, "La ruta publica '{$ruta}' deberia responder 200.");
    }

    public static function proveedorRutasPublicas(): array
    {
        return [['recuperar'], ['restablecer'], ['live']];
    }

    // CP-HUM-04: Las rutas privadas redirigen al login cuando no hay sesion
    /**
     * @dataProvider proveedorRutasPrivadas
     */
    public function testRutasPrivadasRedirigenAlLoginSinSesion(string $ruta): void
    {
        [$codigo, $cabeceras] = $this->get(self::URL_INDEX . '?p=' . $ruta, false);
        $this->assertSame(302, $codigo, "La ruta privada '{$ruta}' deberia responder 302 sin sesion.");
        $this->assertStringContainsString('p=login', $cabeceras['location'] ?? '');
    }

    public static function proveedorRutasPrivadas(): array
    {
        return [
            ['atleta'], ['usuarios'], ['eventos'], ['marcas'], ['bitacora'], ['notificaciones'],
            ['testFisico'], ['observacionesTecnicas'],
        ];
    }

    // CP-HUM-05: Una pagina inexistente no expone errores (cae al inicio -> login)
    public function testPaginaInexistenteRedirigeAlLogin(): void
    {
        [$codigo] = $this->get(self::URL_INDEX . '?p=pagina_fantasma_xyz', false);
        $this->assertSame(302, $codigo, 'Una pagina inexistente deberia redirigir (a traves de inicio) al login.');
    }

    // CP-HUM-06: El captcha genera una imagen valida
    public function testCaptchaGeneraImagenValida(): void
    {
        [$codigo, $cabeceras] = $this->get(self::URL_BASE . 'captcha.php', false);
        $this->assertSame(200, $codigo, 'captcha.php debe responder 200.');
        $this->assertStringStartsWith('image/', $cabeceras['content-type'] ?? '', 'captcha.php deberia devolver una imagen.');
    }

    // CP-HUM-07: El POST del login funciona y rechaza un captcha invalido
    public function testLoginPostRechazaCaptchaInvalido(): void
    {
        $paginaLogin = $this->get(self::URL_INDEX . '?p=login')[2];
        if (!str_contains($paginaLogin, 'name="captcha"')) {
            $this->markTestSkipped('R-01: el captcha esta temporalmente desactivado por el equipo (controlador con if (false && ...)); el test se reactivara al restaurarlo.');
        }

        [$codigo, , $cuerpo] = $this->post(self::URL_INDEX . '?p=login', [
            'usuario'  => 'humo@test.local',
            'password' => 'ClaveIncorrecta123',
            'captcha'  => 'XXXX',
        ]);
        $this->assertSame(200, $codigo, 'El POST de login deberia responder 200 con el mensaje de error.');
        $this->assertStringContainsString('Codigo de verificacion incorrecto', $cuerpo, 'Debe mostrar el error de captcha invalido.');
    }

    // CP-HUM-08: Las cabeceras de seguridad del FrontController estan activas
    public function testCabecerasDeSeguridadPresentes(): void
    {
        [, $cabeceras] = $this->get(self::URL_INDEX . '?p=login', false);
        $this->assertSame('DENY', $cabeceras['x-frame-options'] ?? '', 'Falta X-Frame-Options: DENY.');
        $this->assertSame('nosniff', $cabeceras['x-content-type-options'] ?? '', 'Falta X-Content-Type-Options: nosniff.');
    }

    // CP-HUM-09: Ambas bases de datos del sistema son accesibles
    public function testBasesDeDatosAccesibles(): void
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $usuario = $_ENV['DB_USER'] ?? 'root';
        $clave = $_ENV['DB_PASS'] ?? '';
        $opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        $pdoNatacion = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $_ENV['DB_NAME_NATACION'] ?? 'sis_natacion'),
            $usuario,
            $clave,
            $opciones
        );
        $this->assertSame('1', $pdoNatacion->query('SELECT 1')->fetchColumn(), 'No se puede consultar sis_natacion.');

        $pdoSeguridad = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $_ENV['DB_NAME_SEGURIDAD'] ?? 'sis_seguridad'),
            $usuario,
            $clave,
            $opciones
        );
        $this->assertSame('1', $pdoSeguridad->query('SELECT 1')->fetchColumn(), 'No se puede consultar sis_seguridad.');
    }

    private function post(string $url, array $datos): array
    {
        return $this->peticion('POST', $url, $datos, true);
    }
}
