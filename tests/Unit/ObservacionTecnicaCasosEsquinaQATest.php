<?php

namespace Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\ObservacionTecnica;

/**
 * =============================================================================
 * SUITE ADVERSARIAL DE QA - MODULO: src/modelo/ObservacionTecnica.php
 * =============================================================================
 * Casos esquina: arrays como strings, XSS almacenado, calificacion 0 (falsy),
 * campos NOT NULL no validados, rowCount()=0 en actualizaciones sin cambios.
 * Si una prueba FALLA se reprodujo un defecto. Informe formal en
 * docs/qa-evidencia/ObservacionTecnica/informe-errores-ObservacionTecnica.md
 * Limpieza: observaciones marcadas con texto 'QAOT%' o id_atleta 99999999.
 * =============================================================================
 */
class ObservacionTecnicaCasosEsquinaQATest extends TestCase
{
    private ObservacionTecnica $modelo;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->modelo = new ObservacionTecnica();
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
            $this->pdo->exec("DELETE FROM observaciones_tecnicas WHERE observacion_texto LIKE 'QAOT%' OR id_atleta = 99999999");
        } catch (\Throwable $e) {
            fwrite(STDERR, 'AVISO limpieza QA ObservacionTecnica: ' . $e->getMessage() . PHP_EOL);
        }
        unset($this->modelo, $this->pdo);
    }

    private function datosValidos(array $overrides = []): array
    {
        return array_merge([
            'id_atleta'         => '1',
            'id_aspecto_tecnico' => '1',
            'calificacion'      => '3',
            'observacion_texto' => 'QAOT ' . bin2hex(random_bytes(4)),
            'id_usuario'        => '1',
        ], $overrides);
    }

    // =========================================================================
    // QA-OT-01 | setAtributos() + validarAtributosInternos() - ObservacionTecnica.php:23-24, 60-61
    // Caso esquina: observacion_texto como array (observacion_texto[]=x por HTTP).
    // Contrato: registrar error de validacion, jamas TypeError (HTTP 500).
    // Defecto: setAtributos almacena arrays tal cual y longitud() exige string.
    // =========================================================================
    public function testTextoComoArrayDebeRegistrarErrorNoLanzarTypeError(): void
    {
        $excepcion = null;
        try {
            $this->modelo->setAtributos($this->datosValidos(['observacion_texto' => ['texto inyectado']]));
            $this->modelo->getRegistrarObservacion();
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-OT-01: getRegistrarObservacion() con observacion_texto tipo array crashea: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
        $this->assertArrayHasKey('observacion_texto', $this->modelo->obtenerErrores(), 'BUG-OT-01: debio registrarse error de validacion para el texto.');
    }

    // =========================================================================
    // QA-OT-02 | getRegistrarObservacion() - ObservacionTecnica.php:67-107
    // Caso esquina: inyeccion de HTML crudo en observacion_texto.
    // Contrato: el texto no debe llegar crudo a la BD (XSS almacenado).
    // Defecto: solo se valida longitud (0-500); ningun saneamiento HTML.
    // =========================================================================
    public function testTextoConHtmlNoDebeAlmacenarseCrudo(): void
    {
        $marca = 'QAOT-XSS-' . bin2hex(random_bytes(4));
        $this->modelo->setAtributos($this->datosValidos([
            'observacion_texto' => '<b>HACK</b> ' . $marca,
        ]));
        $resultado = $this->modelo->getRegistrarObservacion();

        $stmt = $this->pdo->prepare('SELECT observacion_texto FROM observaciones_tecnicas WHERE observacion_texto LIKE ?');
        $stmt->execute(['%' . $marca . '%']);
        $guardado = $stmt->fetchColumn();

        if ($guardado === false) {
            $this->assertFalse($resultado, 'BUG-OT-02: se reporto true pero la fila no existe.');
        }
        $this->assertStringNotContainsString(
            '<b>',
            (string)$guardado,
            'BUG-OT-02: XSS ALMACENADO — la BD guarda "' . (string)$guardado . '" (HTML crudo sin saneamiento).'
        );
    }

    // =========================================================================
    // QA-OT-03 | validarAtributosInternos() - ObservacionTecnica.php:36-65
    // Caso esquina: registro sin id_usuario (columna NOT NULL, jamas validada).
    // Contrato: error de validacion explicito para id_usuario.
    // Defecto: pasa validacion -> PDOException -> error generico 'bd'.
    // =========================================================================
    public function testRegistroSinIdUsuarioDebeGenerarErrorDeValidacionExplicito(): void
    {
        $this->modelo->setAtributos($this->datosValidos(['id_usuario' => '']));
        $resultado = $this->modelo->getRegistrarObservacion();

        $this->assertFalse($resultado, 'BUG-OT-03: sin id_usuario el registro no debe llegar a la BD.');
        $this->assertArrayHasKey(
            'id_usuario',
            $this->modelo->obtenerErrores(),
            'BUG-OT-03: el fallo se reporta solo como error generico de BD: ' . json_encode($this->modelo->obtenerErrores())
        );
    }

    // =========================================================================
    // QA-OT-04 | actualizarObservacionBD() - ObservacionTecnica.php:150-154
    // Caso esquina: actualizacion con valores identicos a los almacenados.
    // Contrato: la actualizacion sin cambios debe completarse con exito.
    // Defecto: rowCount()=0 se interpreta como "No se encontro la observacion".
    // =========================================================================
    public function testActualizacionSinCambiosNoDebeReportarObservacionInexistente(): void
    {
        $datos = $this->datosValidos();
        $this->modelo->setAtributos($datos);
        $this->assertTrue($this->modelo->getRegistrarObservacion(), 'Precondicion: la observacion inicial debe crearse.');

        $stmt = $this->pdo->prepare('SELECT id_observacion FROM observaciones_tecnicas WHERE observacion_texto = ?');
        $stmt->execute([$datos['observacion_texto']]);
        $idObs = (int)$stmt->fetchColumn();

        $this->modelo->setAtributos(array_merge($datos, ['id_observacion' => (string)$idObs]));
        $resultado = $this->modelo->getActualizarObservacion();

        $this->assertTrue(
            $resultado,
            'BUG-OT-04: actualizar la observacion existente #' . $idObs . ' con los mismos valores retorno false con error "' .
            ($this->modelo->obtenerErrores()['actualizacion'] ?? 'ninguno') . '" — rowCount()=0 se confunde con inexistencia.'
        );
    }

    // =========================================================================
    // QA-OT-05 | getRegistrarObservacion() - ObservacionTecnica.php:39-42
    // Caso esquina: id_atleta inexistente (99999999; soloNumeros pasa y la
    // tabla NO tiene FK a atletas).
    // Contrato: rechazar la referencia a un atleta inexistente.
    // Defecto: se persiste una observacion huerfana y retorna true.
    // =========================================================================
    public function testIdAtletaInexistenteNoDebePermitirObservacionHuerfana(): void
    {
        $this->modelo->setAtributos($this->datosValidos(['id_atleta' => '99999999']));
        $resultado = $this->modelo->getRegistrarObservacion();

        $this->assertFalse(
            $resultado,
            'BUG-OT-05: getRegistrarObservacion() retorno true para id_atleta=99999999 (inexistente): observacion huerfana persistida.'
        );
    }

    // =========================================================================
    // QA-OT-06 | validarAtributosInternos() - ObservacionTecnica.php:49-54
    // Caso esquina: calificacion '0' (fuera de la escala 1-5). El enEnum se
    // protege con !empty() y '0' es falsy -> el control de enum se SALTA.
    // Contrato: rechazar calificacion 0.
    // Defecto: se persiste calificacion 0 en la BD.
    // =========================================================================
    public function testCalificacionCeroDebeSerRechazada(): void
    {
        $marca = 'QAOT-CERO-' . bin2hex(random_bytes(4));
        $this->modelo->setAtributos($this->datosValidos([
            'calificacion'      => '0',
            'observacion_texto' => $marca,
        ]));
        $resultado = $this->modelo->getRegistrarObservacion();

        $stmt = $this->pdo->prepare('SELECT calificacion FROM observaciones_tecnicas WHERE observacion_texto = ?');
        $stmt->execute([$marca]);
        $calGuardada = $stmt->fetchColumn();

        $this->assertFalse(
            $resultado,
            'BUG-OT-06: getRegistrarObservacion() retorno true con calificacion=0 y la BD guarda calificacion=' . var_export($calGuardada, true) . ' (fuera de la escala 1-5).'
        );
    }

    // =========================================================================
    // QA-OT-07 | CONTROL (prueba de humo del arnes)
    // =========================================================================
    public function testControlRegistroValidoRetornaTrue(): void
    {
        $this->modelo->setAtributos($this->datosValidos());
        $this->assertTrue($this->modelo->getRegistrarObservacion(), 'Control: registro valido debe retornar true.');
    }
}
