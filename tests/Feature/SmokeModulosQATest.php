<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * =============================================================================
 * PRUEBAS DE HUMO (SMOKE) - MODULOS: Atleta, TestFisico, ObservacionTecnica,
 * Periodizacion. Nivel HTTP real (Apache + MySQL + sesion PHP), sin mocks.
 *
 * Verifican el cableado minimo de integracion de cada modulo:
 *   1. Sin sesion -> redireccion al login (302).
 *   2. Login real con usuario QA de rol Administrador.
 *   3. Con sesion -> cada endpoint JSON critico responde 200 + array JSON
 *      (modelo <-> controlador <-> BD cableados).
 *   4. La pagina HTML de cada modulo carga con sesion.
 *
 * Requisitos: Apache y MySQL de XAMPP corriendo + usuarios QA creados con
 * docs/qa-postman/setup-qa-usuarios.php
 * Ejecutar: vendor\bin\phpunit tests\Feature\SmokeModulosQATest.php
 * =============================================================================
 */
class SmokeModulosQATest extends TestCase
{
    private const URL = 'http://localhost/SIS-Biomec/index.php';
    private const QA_USER = 'qa.pm.admin@qa.test';
    private const QA_PASS = 'ClaveQA123*';
    private string $cookies = '';

    /** Peticion HTTP cruda con el cookie jar compartido de la suite. */
    private function http(string $metodo, string $url, ?array $form = null, bool $conSesion = true, bool $seguir = true): array
    {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => $seguir,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CUSTOMREQUEST  => $metodo,
        ];
        if ($conSesion && $this->cookies !== '') {
            $opts[CURLOPT_COOKIE] = $this->cookies;
        }
        if ($form !== null) {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($form);
        }
        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $codigo = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $tipo = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $nHeaders = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rawHeaders = substr((string)$resp, 0, $nHeaders);
        // Capturar la ULTIMA cookie emitida (login regenera el id de sesion).
        if (preg_match_all('/Set-Cookie:\s*(PHPSESSID=[^;]+)/i', $rawHeaders, $m)) {
            $this->cookies = end($m[1]);
        }
        curl_close($ch);
        $this->assertNotFalse($resp, "Fallo la peticion HTTP a {$url}");
        return [$codigo, $tipo, substr((string)$resp, $nHeaders)];
    }

    private function login(): void
    {
        [$codigo, , ] = $this->http('POST', self::URL . '?p=login', [
            'usuario'  => self::QA_USER,
            'password' => self::QA_PASS,
        ], false, false);
        $this->assertSame(302, $codigo, 'El login QA debe redirigir (302) al inicio.');
        $this->assertNotSame('', $this->cookies, 'El login QA debe establecer PHPSESSID.');
    }

    private function assertJsonArray200(array $resp, string $modulo): void
    {
        [$codigo, $tipo, $cuerpo] = $resp;
        $this->assertSame(200, $codigo, "[{$modulo}] el endpoint JSON debe responder 200.");
        $this->assertStringContainsString('application/json', $tipo, "[{$modulo}] debe devolver Content-Type JSON.");
        $datos = json_decode($cuerpo, true);
        $this->assertIsArray($datos, "[{$modulo}] el cuerpo debe ser un JSON array. Cuerpo: " . substr($cuerpo, 0, 200));
    }

    // ---------------------------------------------------------------------
    // Autenticacion
    // ---------------------------------------------------------------------

    // CP-HUM-M1: login con credenciales QA reales contra la BD de seguridad
    public function testLoginQaEstableceSesion(): void
    {
        $this->login();
        [$codigo, , ] = $this->http('GET', self::URL . '?p=inicio');
        $this->assertSame(200, $codigo, 'Con sesion activa el inicio debe responder 200.');
    }

    // CP-HUM-M2: login con password incorrecto NO establece sesion
    public function testLoginIncorrectoNoEstableceSesion(): void
    {
        [, , $cuerpo] = $this->http('POST', self::URL . '?p=login', [
            'usuario'  => self::QA_USER,
            'password' => 'Incorrecta999',
        ], false, true);
        $this->assertStringContainsString('incorrectos', $cuerpo, 'Debe mostrarse el error de credenciales.');
        [$codigo] = $this->http('GET', self::URL . '?p=inicio', null, false, false);
        $this->assertSame(302, $codigo, 'Sin sesion, inicio debe redirigir al login.');
    }

    // ---------------------------------------------------------------------
    // MODULO ATLETA
    // ---------------------------------------------------------------------

    // CP-HUM-M3: atleta/listar exige sesion
    public function testAtletaListarSinSesionRedirige(): void
    {
        [$codigo] = $this->http('GET', self::URL . '?p=atleta&accion=listar', null, false, false);
        $this->assertSame(302, $codigo, 'atleta&accion=listar sin sesion debe responder 302.');
    }

    // CP-HUM-M4: atleta/listar y categorias cableados con sesion
    public function testAtletaEndpointsJsonConSesion(): void
    {
        $this->login();
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=atleta&accion=listar'), 'atleta.listar');
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=atleta&accion=categorias'), 'atleta.categorias');
        [, , $cuerpo] = $this->http('GET', self::URL . '?p=atleta&accion=categorias');
        $this->assertNotEmpty(json_decode($cuerpo, true), 'atleta.categorias debe traer datos (FK de catalogo vivo).');
        // La vista HTML del modulo carga con sesion
        [$codigo, , ] = $this->http('GET', self::URL . '?p=atleta');
        $this->assertSame(200, $codigo, 'La vista HTML de atleta debe cargar con sesion.');
    }

    // ---------------------------------------------------------------------
    // MODULO TEST FISICO
    // ---------------------------------------------------------------------

    // CP-HUM-M5: testFisico exige sesion / responde JSON con sesion
    public function testTestFisicoEndpointsJsonConSesion(): void
    {
        $this->assertSame(302, $this->http('GET', self::URL . '?p=testFisico&accion=listarTiposTests', null, false, false)[0], 'testFisico sin sesion debe redirigir.');
        $this->login();
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=testFisico&accion=listarTiposTests'), 'testFisico.listarTiposTests');
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=testFisico&accion=listarTests'), 'testFisico.listarTests');
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=testFisico&accion=listarAtletasSelect'), 'testFisico.listarAtletasSelect');
        [, , $cuerpo] = $this->http('GET', self::URL . '?p=testFisico&accion=listarTiposTests');
        $this->assertNotEmpty(json_decode($cuerpo, true), 'testFisico.listarTiposTests debe traer el catalogo.');
    }

    // ---------------------------------------------------------------------
    // MODULO OBSERVACIONES TECNICAS
    // ---------------------------------------------------------------------

    // CP-HUM-M6: observacionesTecnicas exige sesion / responde JSON con sesion
    public function testObservacionesEndpointsJsonConSesion(): void
    {
        $this->assertSame(302, $this->http('GET', self::URL . '?p=observacionesTecnicas&accion=listarAspectosTecnicos', null, false, false)[0], 'observacionesTecnicas sin sesion debe redirigir.');
        $this->login();
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=observacionesTecnicas&accion=listarAspectosTecnicos'), 'obs.listarAspectosTecnicos');
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=observacionesTecnicas&accion=listarObservaciones'), 'obs.listarObservaciones');
        [, , $cuerpo] = $this->http('GET', self::URL . '?p=observacionesTecnicas&accion=listarAspectosTecnicos');
        $this->assertNotEmpty(json_decode($cuerpo, true), 'obs.listarAspectosTecnicos debe traer el catalogo.');
    }

    // ---------------------------------------------------------------------
    // MODULO PERIODIZACION
    // ---------------------------------------------------------------------

    // CP-HUM-M7: periodizacion exige sesion / responde JSON con sesion
    public function testPeriodizacionEndpointsJsonConSesion(): void
    {
        $this->assertSame(302, $this->http('GET', self::URL . '?p=periodizacion&accion=obtenerTemporadas', null, false, false)[0], 'periodizacion sin sesion debe redirigir.');
        $this->login();
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=periodizacion&accion=obtenerTemporadas'), 'periodizacion.obtenerTemporadas');
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=periodizacion&accion=obtenerGrupos'), 'periodizacion.obtenerGrupos');
        $this->assertJsonArray200($this->http('GET', self::URL . '?p=periodizacion&accion=listarMacrociclos'), 'periodizacion.listarMacrociclos');
        [, , $cuerpo] = $this->http('GET', self::URL . '?p=periodizacion&accion=obtenerGrupos');
        $this->assertNotEmpty(json_decode($cuerpo, true), 'periodizacion.obtenerGrupos debe traer grupos activos.');
    }

    // CP-HUM-M8: sin permisos RBAC el modulo se bloquea con 403 (usuario QA sin roles)
    public function testModuloPrivadoSinPermisosResponde403(): void
    {
        $this->http('POST', self::URL . '?p=login', [
            'usuario'  => 'qa.pm.noperm@qa.test',
            'password' => self::QA_PASS,
        ], false, false);
        $this->assertNotSame('', $this->cookies, 'El login del usuario sin permisos debe establecer sesion.');
        [$codigo] = $this->http('GET', self::URL . '?p=atleta', null, true, false);
        $this->assertSame(403, $codigo, 'Un usuario autenticado sin permisos debe recibir 403 en el modulo atleta.');
        $this->cookies = '';
    }
}

