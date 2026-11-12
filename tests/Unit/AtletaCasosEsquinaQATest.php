<?php

namespace Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Atleta;

/**
 * =============================================================================
 * SUITE ADVERSARIAL DE QA - MODULO: src/modelo/Atleta.php
 * =============================================================================
 * Objetivo: provocar fallos reales del codigo ante casos esquina (tipos
 * incorrectos, valores nulos, desbordamientos, entradas maliciosas).
 *
 * Cada metodo documenta:
 *   - La funcion exacta bajo prueba (y linea en Atleta.php).
 *   - El caso esquina inyectado.
 *   - El contrato correcto esperado (la asercion) que el codigo viola.
 *
 * Convencion: si una prueba FALLA, se ha reproducido un defecto real.
 * El informe formal esta en docs/qa-evidencia/Atleta/informe-errores-Atleta.md
 *
 * NOTA: las pruebas requieren MySQL (XAMPP) activo, igual que el resto de la
 * suite existente del proyecto (la clase Conexion conecta en el constructor).
 * Toda fila creada usa el prefijo "qaatleta" y se elimina en tearDown().
 * =============================================================================
 */
class AtletaCasosEsquinaQATest extends TestCase
{
    private Atleta $atleta;
    private int $idCategoria;
    /** Rutas absolutas de archivos creados por procesarFoto() que hay que limpiar. */
    private array $archivosBasura = [];
    private string $pngPrueba = '';

    protected function setUp(): void
    {
        $this->atleta = new Atleta();
        $categorias = $this->atleta->obtenerCategorias();
        if ($categorias === []) {
            $this->markTestSkipped('No hay categorias FEVEDA activas en la BD.');
        }
        $this->idCategoria = (int)$categorias[0]['id_categoria'];
    }

    protected function tearDown(): void
    {
        // Limpieza de filas de prueba (el FK a atleta_datos_medicos es CASCADE).
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=sis_natacion;charset=utf8mb4',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec("DELETE FROM atletas WHERE correo LIKE 'qaatleta%'");
            // Registros huerfanos de datos medicos creados por ids desbordados.
            $pdo->exec("DELETE FROM atleta_datos_medicos WHERE id_atleta NOT IN (SELECT id_atleta FROM atletas)");
        } catch (\Throwable $e) {
            fwrite(STDERR, 'AVISO limpieza QA Atleta: ' . $e->getMessage() . PHP_EOL);
        }

        foreach ($this->archivosBasura as $ruta) {
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
        if ($this->pngPrueba !== '' && is_file($this->pngPrueba)) {
            @unlink($this->pngPrueba);
        }
        unset($this->atleta);
    }

    // =========================================================================
    // Datos base
    // =========================================================================

    private function datosValidos(array $overrides = []): array
    {
        $base = [
            'cedula'                           => 'V-' . random_int(10000000, 99999999),
            'nombres'                          => 'Juan',
            'apellidos'                        => 'Perez',
            'fecha_nacimiento'                 => '2005-05-10',
            'sexo'                             => 'M',
            'estado'                           => 'Activo',
            'correo'                           => 'qaatleta' . bin2hex(random_bytes(5)) . '@qa.test',
            'telefono'                         => '04141234567',
            'direccion'                        => 'Av Principal con calle 10',
            'fecha_registro_club'              => '2020-01-15',
            'grupo_sanguineo'                  => 'O+',
            'seguro_medico'                    => 'Seguros QA Cobertura',
            'alergias'                         => 'Ninguna conocida',
            'condiciones_previas'              => 'Ninguna',
            'contacto_emergencia_nombre'       => 'Maria Perez',
            'contacto_emergencia_telefono'     => '04241234567',
            'contacto_emergencia_parentesco'   => 'Madre',
            'id_categoria'                     => (string)$this->idCategoria,
            'numero_feveda'                    => 'FV-' . bin2hex(random_bytes(3)),
            'club_procedencia'                 => 'Club QA Natacion',
            'id_usuario'                       => null,
            'foto'                             => null,
        ];
        return array_merge($base, $overrides);
    }

    private function pngDePrueba(): string
    {
        if ($this->pngPrueba === '' || !is_file($this->pngPrueba)) {
            $ruta = sys_get_temp_dir() . '/qa_atleta_' . bin2hex(random_bytes(4)) . '.png';
            $img = \imagecreatetruecolor(64, 64);
            \imagefill($img, 0, 0, \imagecolorallocate($img, 200, 30, 30));
            \imagepng($img, $ruta);
            \imagedestroy($img);
            $this->pngPrueba = $ruta;
        }
        return $this->pngPrueba;
    }

    private function archivoUploadValido(): array
    {
        $ruta = $this->pngDePrueba();
        return [
            'error'    => UPLOAD_ERR_OK,
            'tmp_name' => $ruta,
            'type'     => 'image/png',
            'size'     => (int)filesize($ruta),
        ];
    }

    // =========================================================================
    // QA-01 | guardar() / guardarAtleta() - Atleta.php:165-229 (linea 192)
    // Caso esquina: invocar guardar() sin haber llamado setDatos().
    // Contrato: debe retornar false registrando un error, sin emitir warnings.
    // Defecto: acceso directo a $this->datos['token_asistencia'] inexistente.
    // =========================================================================
    public function testGuardarSinSetDatosNoDebeEmitirWarningNiIntentarInsertar(): void
    {
        $excepcion = null;
        try {
            $resultado = $this->atleta->guardar();
        } catch (\Throwable $e) {
            $excepcion = $e;
            $resultado = false;
        }
        $this->assertNull(
            $excepcion,
            'BUG-01: guardar() sin setDatos() previo revienta con: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
        $this->assertFalse($resultado, 'BUG-01: guardar() sin datos debe retornar false.');
        $this->assertNotEmpty($this->atleta->obtenerErrores(), 'BUG-01: guardar() sin datos debe registrar un error explícito.');
    }

    // =========================================================================
    // QA-02 | guardar() - Atleta.php:165-229 | validar() telefono - Atleta.php:110-112
    // Caso esquina: telefono de 15 digitos (validacion lo rechaza, BD varchar(20) lo acepta).
    // Contrato: guardar() NO debe persistir ni retornar true cuando hayErrores() === true.
    // Defecto: guardar() ignora por completo el resultado de la validacion.
    // =========================================================================
    public function testGuardarNoDebePersistirUnAtletaConTelefonoInvalido(): void
    {
        $this->atleta->setDatos($this->datosValidos(['telefono' => '041412345670123']));
        $this->assertTrue($this->atleta->hayErrores(), 'Precondicion: el telefono de 15 digitos debe generar error de validacion.');

        $guardado = $this->atleta->guardar();

        $this->assertFalse(
            $guardado,
            'BUG-02: guardar() retorno true con errores de validacion activos y persistio un telefono invalido en la BD.'
        );
    }

    // =========================================================================
    // QA-03 | setCampo() - Atleta.php:31-34 + guardar()
    // Caso esquina: inyeccion de HTML/JS por setCampo() evadiendo sanitizar()/validar().
    // Contrato: guardar() no debe almacenar carga util HTML cruda (XSS almacenado).
    // Defecto: setCampo escribe sin sanitizar ni revalidar y guardar() no revalida.
    // =========================================================================
    public function testSetCampoNoDebePermitirAlmacenarXssEnTelefono(): void
    {
        $this->atleta->setDatos($this->datosValidos());
        $this->assertFalse($this->atleta->hayErrores(), 'Precondicion: datos base validos.');

        $this->atleta->setCampo('telefono', '<b>HACK</b>123');
        $guardado = $this->atleta->guardar();

        $fila = $this->buscarAtletaPorCorreo((string)$this->atleta->getCampo('correo'));
        if ($fila === null) {
            $this->assertFalse($guardado, 'BUG-03: guardar() reporto true pero el atleta no esta en la BD.');
        }
        $this->assertNotNull($fila, 'BUG-03: el atleta quedo insertado en la BD pese a la inyeccion.');
        if ($fila !== null) {
            $this->assertStringNotContainsString(
                '<b>',
                (string)$fila['telefono'],
                'BUG-03: XSS ALMACENADO — la BD guarda "' . (string)$fila['telefono'] . '" (HTML crudo inyectado via setCampo()+guardar()).'
            );
        }
        $this->assertFalse($guardado, 'BUG-03: guardar() persistio datos inyectados por setCampo sin validacion.');
    }

    // =========================================================================
    // QA-04 | setDatos()/sanitizar()/validar() - Atleta.php:18-25, 40-50, 70-163
    // Caso esquina: valor de tipo array en 'cedula' (input malicioso por HTTP podria
    // llegar como array: cedula[]=x).
    // Contrato: setDatos debe registrar error de validacion, jamas lanzar TypeError.
    // Defecto: sanitizar() solo procesa strings y pasa el array intacto a
    // requerido()/unico() que exigen tipo string -> TypeError no capturado (HTTP 500).
    // =========================================================================
    public function testSetDatosConArrayEnCedulaDebeRegistrarErrorNoLanzarTypeError(): void
    {
        $excepcion = null;
        try {
            $this->atleta->setDatos($this->datosValidos(['cedula' => ['V-1234567']]));
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-04: setDatos() crashea con input de tipo array: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
        $this->assertArrayHasKey('cedula', $this->atleta->obtenerErrores(), 'BUG-04: debio registrarse error de validacion para cedula.');
    }

    // =========================================================================
    // QA-05 | validar() id_categoria - Atleta.php:152 + vincular() - Atleta.php:59
    // Caso esquina: id_categoria = 'abc' (pasa requerido, se castea a 0 en el bind).
    // Contrato: validar() debe rechazar ids no numericos; guardar() debe explicar el fallo.
    // Defecto: solo se valida presencia, nunca el tipo; el FK revienta y guardar()
    // retorna false SIN NINGUN error en obtenerErrores().
    // =========================================================================
    public function testIdCategoriaNoNumericoDebeGenerarErrorDeValidacion(): void
    {
        $this->atleta->setDatos($this->datosValidos(['id_categoria' => 'abc']));

        $this->assertArrayHasKey(
            'id_categoria',
            $this->atleta->obtenerErrores(),
            'BUG-05: id_categoria="abc" pasa la validacion sin ser numerico.'
        );
    }

    // =========================================================================
    // QA-06 | actualizar() - Atleta.php:231-238 (linea 232) + vincular() linea 59
    // Caso esquina: id_atleta = '99999999999999999999' (excede int de 64 bits).
    // Contrato: id desbordado debe rechazarse con error registrado.
    // Defecto: el cast (int) satura a PHP_INT_MAX silenciosamente y el fallo
    // posterior de BD se traga sin dejar rastro en obtenerErrores().
    // =========================================================================
    public function testActualizarConIdDesbordadoDebeRegistrarErrorExplicativo(): void
    {
        $this->atleta->setDatos($this->datosValidos());
        $this->atleta->setCampo('id_atleta', '99999999999999999999');

        $resultado = $this->atleta->actualizar();

        $this->assertFalse(
            $resultado,
            'BUG-06: actualizar() con id desbordado reporta exito o fallo sin control.'
        );
        $this->assertNotEmpty(
            $this->atleta->obtenerErrores(),
            'BUG-06: fallo de actualizar() por id desbordado no deja ningun error registrado (fallo silencioso).'
        );
    }

    // =========================================================================
    // QA-07 | eliminar()/eliminarAtleta() - Atleta.php:319-338 (linea 333)
    // Caso esquina: id inexistente (999999999).
    // Contrato: no debe reportar exito cuando no se desactivo ninguna fila.
    // Defecto: retorna el resultado crudo de execute() (true con 0 filas afectadas).
    // =========================================================================
    public function testEliminarIdInexistenteNoDebeReportarExito(): void
    {
        $resultado = $this->atleta->eliminar(999999999);

        $this->assertFalse(
            $resultado,
            'BUG-07: eliminar(999999999) retorno true sin haber desactivado ninguna fila (falso exito).'
        );
        $this->assertNotEmpty($this->atleta->obtenerErrores(), 'BUG-07: el falso exito ademas no registra error alguno.');
    }

    // =========================================================================
    // QA-08 | procesarFoto() - Atleta.php:556-570 (linea 557)
    // Caso esquina: array de upload vacio (sin clave 'error').
    // Contrato: debe degradar con agregarError('foto'), sin warnings.
    // Defecto: acceso a $archivo['error'] inexistente -> Warning "Undefined array key".
    // =========================================================================
    public function testProcesarFotoConArrayVacioNoDebeEmitirWarnings(): void
    {
        $excepcion = null;
        try {
            $this->atleta->procesarFoto([]);
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-08: procesarFoto([]) revienta con: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
        $this->assertArrayHasKey('foto', $this->atleta->obtenerErrores(), 'BUG-08: debio registrar error de foto controlado.');
    }

    // =========================================================================
    // QA-09 | procesarFoto() - Atleta.php:556-575 (linea 572)
    // Caso esquina: array sin clave 'size' (upload malformado).
    // Contrato: debe validar existencia de claves antes de usarlas.
    // Defecto: $archivo['size'] inexistente -> Warning "Undefined array key".
    // =========================================================================
    public function testProcesarFotoSinClaveSizeNoDebeEmitirWarnings(): void
    {
        $png = $this->pngDePrueba();
        $excepcion = null;
        try {
            $this->atleta->procesarFoto([
                'error'    => UPLOAD_ERR_OK,
                'tmp_name' => $png,
                'type'     => 'image/png',
                // 'size' omitido a proposito
            ]);
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-09: procesarFoto() sin clave "size" revienta con: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
    }

    // =========================================================================
    // QA-10 | procesarFoto() - Atleta.php:631-633
    // Caso esquina: fotoActual apunta a un archivo FUERA del directorio de fotos
    // (con "../" se escaparia del proyecto: borrado arbitrario de archivos).
    // Contrato: unlink solo debe aplicarse a archivos dentro de assets/uploads/fotos/.
    // Defecto: unlink(RAIZ . $fotoActual) sin ninguna validacion de ruta.
    // =========================================================================
    public function testProcesarFotoNoDebeBorrarArchivosFueraDelDirectorioDeFotos(): void
    {
        $rutaVictima = RAIZ . 'docs/qa-evidencia/Atleta/tmp-victima-unlink.txt';
        if (!is_dir(dirname($rutaVictima))) {
            mkdir(dirname($rutaVictima), 0777, true);
        }
        file_put_contents($rutaVictima, 'archivo victima de QA');
        $this->archivosBasura[] = $rutaVictima;

        $resultado = $this->atleta->procesarFoto(
            $this->archivoUploadValido(),
            'docs/qa-evidencia/Atleta/tmp-victima-unlink.txt'
        );

        if (is_string($resultado) && $resultado !== '') {
            $this->archivosBasura[] = RAIZ . $resultado; // foto generada por el propio test
        }

        $this->assertFileExists(
            $rutaVictima,
            'BUG-10: procesarFoto() BORRO un archivo fuera de assets/uploads/fotos/ (unlink sin validar ruta; con "../" permite borrado arbitrario en el servidor).'
        );
    }

    // =========================================================================
    // QA-11 | validar() telefono - Atleta.php:110-112 (regex trait ValidacionesTrait.php:168)
    // Caso esquina: telefono compuesto solo de simbolos '+++++++' (7 chars).
    // Contrato: un telefono debe contener digitos.
    // Defecto: la regex [\d\-\+\(\)\s]{7,11} no exige ningun digito.
    // =========================================================================
    public function testTelefonoCompuestoSoloDeSimbolosDebeSerRechazado(): void
    {
        $this->atleta->setDatos($this->datosValidos(['telefono' => '+++++++']));

        $this->assertArrayHasKey(
            'telefono',
            $this->atleta->obtenerErrores(),
            'BUG-11: telefono="+++++++" (sin un solo digito) pasa la validacion.'
        );
    }

    // =========================================================================
    // QA-12 | validar() fecha_nacimiento - Atleta.php:92-95
    // Caso esquina: fecha de nacimiento 1900-01-01 (atleta de 126 anos).
    // Contrato: rechazar edades imposibles (el trait tiene edadMaxima() PERO
    // Atleta::validar() nunca la invoca).
    // Defecto: gap de validacion -> registro con edad imposible.
    // =========================================================================
    public function testFechaNacimientoAnio1900DebeSerRechazadaPorEdadImposible(): void
    {
        $this->atleta->setDatos($this->datosValidos(['fecha_nacimiento' => '1900-01-01']));

        $this->assertArrayHasKey(
            'fecha_nacimiento',
            $this->atleta->obtenerErrores(),
            'BUG-12: fecha_nacimiento=1900-01-01 (126 anos) pasa la validacion; la regla edadMaxima() existe en el trait pero jamas se invoca.'
        );
    }

    // =========================================================================
    // QA-13 | ANALIZADO Y DESCARTADO (sin prueba de fallo):
    // Correo de 160+ caracteres: se verifico que filter_var() en PHP 8.0.30
    // rechaza correos con parte local > 64 chars y dominios largos, por lo que
    // correoValido() ya bloquea este caso esquina. No es defecto.
    // =========================================================================

    // =========================================================================
    // QA-14 | validar() - cruce de campos fecha_nacimiento/fecha_registro_club
    //          Atleta.php:92-95 y 118-121
    // Caso esquina: inscrito en el club en 1901 habiendo nacido en 2010.
    // Contrato: fecha de registro no puede ser anterior al nacimiento.
    // Defecto: no existe validacion de cruce de campos.
    // =========================================================================
    public function testFechaRegistroClubAnteriorAlNacimientoDebeSerRechazada(): void
    {
        $this->atleta->setDatos($this->datosValidos([
            'fecha_nacimiento'    => '2010-06-15',
            'fecha_registro_club' => '1901-01-01',
        ]));

        $this->assertArrayHasKey(
            'fecha_registro_club',
            $this->atleta->obtenerErrores(),
            'BUG-14: registro en el club (1901) anterior al nacimiento (2010) pasa la validacion.'
        );
    }

    // =========================================================================
    // QA-15 | CONTROL (prueba de humo del arnés de QA)
    // guardar() con datos validos completos debe retornar true y sin errores.
    // Si ESTA prueba falla, el entorno es el problema; si solo fallan las demas,
    // los fallos son defectos reales del codigo.
    // =========================================================================
    public function testControlGuardarDatosValidosRetornaTrue(): void
    {
        $this->atleta->setDatos($this->datosValidos());
        $this->assertFalse($this->atleta->hayErrores(), 'Control: los datos base deben ser validos. Errores: ' . json_encode($this->atleta->obtenerErrores()));
        $this->assertTrue($this->atleta->guardar(), 'Control: guardar() con datos validos debe retornar true.');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function buscarAtletaPorCorreo(string $correo): ?array
    {
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=sis_natacion;charset=utf8mb4',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $stmt = $pdo->prepare('SELECT id_atleta, telefono, correo FROM atletas WHERE correo = ?');
            $stmt->execute([$correo]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
