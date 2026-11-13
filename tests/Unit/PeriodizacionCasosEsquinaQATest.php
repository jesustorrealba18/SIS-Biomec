<?php

namespace Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Periodizacion;

/**
 * =============================================================================
 * SUITE ADVERSARIAL DE QA - MODULO: src/modelo/Periodizacion.php
 * =============================================================================
 * Casos esquina: arrays como strings (TypeError), fechas sin tope superior,
 * registro sin invocar la propia validacion del modulo, falso exito en
 * eliminaciones, config del generador ATR sin validar y reglas inconsistentes
 * entre validarDatosMacrociclo() (min 3 semanas) y generarPlanPeriodizacion()
 * (min 4 semanas).
 * Si una prueba FALLA se reprodujo un defecto. Informe formal en
 * docs/qa-evidencia/Periodizacion/informe-errores-Periodizacion.md
 * Limpieza: macrociclos marcados con nombre 'QAPER-%' (cascade a fases,
 * mesociclos y microciclos).
 * =============================================================================
 */
class PeriodizacionCasosEsquinaQATest extends TestCase
{
    private Periodizacion $modelo;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->modelo = new Periodizacion();
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=sis_natacion;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    protected function tearDown(): void
    {
        try {
            $this->pdo->exec("DELETE FROM macrociclos WHERE nombre LIKE 'QAPER-%'");
        } catch (\Throwable $e) {
            fwrite(STDERR, 'AVISO limpieza QA Periodizacion: ' . $e->getMessage() . PHP_EOL);
        }
        unset($this->modelo, $this->pdo);
    }

    private function datosMacrocicloValidos(array $overrides = []): array
    {
        return array_merge([
            'id_temporada' => '1',
            'id_grupo'     => '1',
            'nombre'       => 'QAPER-' . bin2hex(random_bytes(4)),
            'fecha_inicio' => date('Y-m-d', strtotime('-30 days')),
            'fecha_fin'    => date('Y-m-d', strtotime('+60 days')),
        ], $overrides);
    }

    private function idDeMacrocicloCreado(string $nombre): int
    {
        $stmt = $this->pdo->prepare('SELECT id_macrociclo FROM macrociclos WHERE nombre = ?');
        $stmt->execute([$nombre]);
        return (int)$stmt->fetchColumn();
    }

    // =========================================================================
    // QA-PER-01 | validarDatosMacrociclo() - Periodizacion.php:44 (requerido)
    // Caso esquina: id_temporada como array (id_temporada[]=1 por HTTP).
    // Contrato: registrar error de validacion, jamas TypeError (HTTP 500).
    // Defecto: requerido() exige string; el array llega directo -> TypeError.
    // =========================================================================
    public function testValidarConIdTemporadaArrayDebeRegistrarErrorNoLanzarTypeError(): void
    {
        $excepcion = null;
        try {
            $this->modelo->validarDatosMacrociclo($this->datosMacrocicloValidos(['id_temporada' => ['1']]));
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-PER-01: validarDatosMacrociclo() con id_temporada tipo array crashea: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
        $this->assertArrayHasKey('id_temporada', $this->modelo->obtenerErrores(), 'BUG-PER-01: debio registrarse error de validacion.');
    }

    // =========================================================================
    // QA-PER-02 | validarDatosMacrociclo() - Periodizacion.php:57-69
    // Caso esquina: fecha_fin en el ano 2999 (sin tope superior ni
    // fechaNoFutura; el intervalo maximo no se valida).
    // Contrato: rechazar horizontes absurdos de 973 anos.
    // Defecto: la validacion pasa sin errores.
    // =========================================================================
    public function testMacrocicloDeMilAnosDebeSerRechazado(): void
    {
        $errores = $this->modelo->validarDatosMacrociclo($this->datosMacrocicloValidos([
            'fecha_inicio' => '2026-01-01',
            'fecha_fin'    => '2999-12-31',
        ]));

        $this->assertArrayHasKey(
            'fecha_fin',
            $errores,
            'BUG-PER-02: un macrociclo 2026-01-01 a 2999-12-31 (973 anos) pasa la validacion sin errores.'
        );
    }

    // =========================================================================
    // QA-PER-03 | registrarMacrociclo() - Periodizacion.php:132-150 (lineas 141-142)
    // Caso esquina: datos incompletos (sin fecha_inicio/fecha_fin/nombre).
    // Contrato: invocar validarDatosMacrociclo() y retornar false con errores,
    // sin warnings.
    // Defecto: NO invoca su propia validacion; accede directo a claves
    // inexistentes ($datos['fecha_inicio']) -> Warning "Undefined array key".
    // =========================================================================
    public function testRegistrarMacrocicloConDatosIncompletosDebeValidarYSinWarnings(): void
    {
        $excepcion = null;
        $resultado = false;
        try {
            $resultado = $this->modelo->registrarMacrociclo([
                'id_temporada' => '1',
                'id_grupo'     => '1',
                // fecha_inicio, fecha_fin y nombre omitidos a proposito
            ]);
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-PER-03: registrarMacrociclo() con datos incompletos revienta con: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
        $this->assertFalse($resultado, 'BUG-PER-03: con datos incompletos no debe intentar el INSERT.');
        $this->assertNotEmpty($this->modelo->obtenerErrores(), 'BUG-PER-03: el fallo no dejo ningun error de validacion registrado.');
    }

    // =========================================================================
    // QA-PER-04 | eliminarMesociclo() - Periodizacion.php:496-518
    // Caso esquina: id inexistente (999999999).
    // Contrato: no reportar exito cuando no se elimino nada.
    // Defecto: borra 0 filas, hace commit y retorna true (falso exito).
    // =========================================================================
    public function testEliminarMesocicloInexistenteNoDebeReportarExito(): void
    {
        $resultado = $this->modelo->eliminarMesociclo(999999999);

        $this->assertFalse(
            $resultado,
            'BUG-PER-04: eliminarMesociclo(999999999) retorno true sin haber eliminado ninguna fila (falso exito).'
        );
    }

    // =========================================================================
    // QA-PER-05 | generarPlanPeriodizacion() - Periodizacion.php:182-235
    // Caso esquina: config con pct_realizacion=101 (porcentajes imposibles;
    // $config se fusiona sin ninguna validacion).
    // Contrato: rechazar configuraciones cuya suma exceda 100%.
    // Defecto: genera un plan incoherente y reporta exito=true.
    // =========================================================================
    public function testConfigConPorcentajesImposiblesDebeSerRechazada(): void
    {
        $datos = $this->datosMacrocicloValidos();
        $this->assertTrue($this->modelo->registrarMacrociclo($datos), 'Precondicion: macrociclo de prueba creado.');
        $idMacro = $this->idDeMacrocicloCreado($datos['nombre']);

        $resultado = $this->modelo->generarPlanPeriodizacion($idMacro, [
            'pct_acumulacion'   => 55,
            'pct_transmutacion' => 28,
            'pct_realizacion'   => 101,
            'frecuencia_deload' => 4,
        ]);

        $this->assertSame(
            false,
            $resultado['exito'] ?? null,
            'BUG-PER-05: generarPlanPeriodizacion() acepto pct_realizacion=101 (55+28+101=184%) y genero un plan: ' . json_encode($resultado['porcentajes'] ?? null)
        );
    }

    // =========================================================================
    // QA-PER-06 | validarDatosMacrociclo() vs generarPlanPeriodizacion()
    //             Periodizacion.php:66-68 vs 207-210
    // Caso esquina: macrociclo de exactamente 21 dias (3 semanas).
    // Contrato: las reglas de ambas funciones deben ser consistentes.
    // Defecto: validarDatosMacrociclo acepta >=21 dias (3 semanas) pero
    // generarPlanPeriodizacion exige >=4 semanas: lo que valida uno, el otro
    // lo rechaza ("Minimo 4 semanas requeridas").
    // =========================================================================
    public function testReglasDeDuracionMinimaDebenSerConsistentes(): void
    {
        $datos = $this->datosMacrocicloValidos([
            'fecha_inicio' => date('Y-m-d', strtotime('-21 days')),
            'fecha_fin'    => date('Y-m-d'),
        ]);
        $errores = $this->modelo->validarDatosMacrociclo($datos);
        $this->assertSame([], $errores, 'Precondicion: 21 dias (3 semanas) pasan la validacion del modulo.');

        $this->assertTrue($this->modelo->registrarMacrociclo($datos), 'Precondicion: macrociclo creado.');
        $idMacro = $this->idDeMacrocicloCreado($datos['nombre']);

        $resultado = $this->modelo->generarPlanPeriodizacion($idMacro);

        $this->assertSame(
            true,
            $resultado['exito'] ?? null,
            'BUG-PER-06: inconsistencia de reglas — validarDatosMacrociclo() acepta 21 dias (3 semanas) pero generarPlanPeriodizacion() lo rechaza: "' . ($resultado['mensaje'] ?? '') . '"'
        );
    }

    // =========================================================================
    // QA-PER-07 | actualizarEstadoMacrociclo() - Periodizacion.php:100-126 (CONTROL)
    // Id inexistente: debe retornar false (comportamiento correcto confirmado).
    // =========================================================================
    public function testControlTransicionConIdInexistenteRetornaFalse(): void
    {
        $this->assertFalse(
            $this->modelo->actualizarEstadoMacrociclo(999999999, 'En Progreso'),
            'Control: transicion sobre id inexistente debe retornar false.'
        );
    }

    // =========================================================================
    // QA-PER-08 | CONTROL (prueba de humo del arnes)
    // =========================================================================
    public function testControlValidacionMacrocicloValidoSinErrores(): void
    {
        $errores = $this->modelo->validarDatosMacrociclo($this->datosMacrocicloValidos());
        $this->assertSame([], $errores, 'Control: macrociclo valido no genera errores. ' . json_encode($errores));
    }
}
