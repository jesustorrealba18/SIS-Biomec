<?php

namespace Tests\Integration\Marcas;

use GrupoProyecto\SisBiomec\modelo\Marca;
use PHPUnit\Framework\TestCase;
use PDO;

class MarcaIntegrationTest extends TestCase
{
    private static $pdo;
    private static $idMarcaCreada = null;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('mysql:host=localhost;dbname=sis_natacion;charset=utf8', 'root', '');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function setUp(): void
    {
        self::$idMarcaCreada = null;
    }

    protected function tearDown(): void
    {
        if (self::$idMarcaCreada) {
            self::$pdo->exec("DELETE FROM marcas_splits WHERE id_marca = " . (int)self::$idMarcaCreada);
            self::$pdo->exec("DELETE FROM marcas_swolf WHERE id_marca = " . (int)self::$idMarcaCreada);
            self::$pdo->exec("DELETE FROM marcas WHERE id_marca = " . (int)self::$idMarcaCreada);
            self::$idMarcaCreada = null;
        }
    }

    private function payloadBase(array $overrides = []): array
    {
        // 🔍 SOLUCIÓN: Consultamos la fecha real de la sesión 8 en la BD
        $stmt = self::$pdo->query("SELECT fecha FROM sesiones WHERE id_sesion = 8");
        $fechaRealSesion = $stmt->fetchColumn();

        // Si por alguna razón la tabla sesiones no devuelve nada, usamos la fecha por defecto
        if (!$fechaRealSesion) {
            $fechaRealSesion = '2026-11-09'; 
        }

        return array_merge([
            'id_atleta'           => 13,
            'id_sesion'           => 8,
            'id_evento'           => null,
            'estilo'              => 'Libre',
            'distancia_m'         => 100,
            'tipo_piscina'        => '50m',
            'tiempo_final_seg'    => '60.00',
            'tiempo_reaccion_seg' => '0.65',
            'nivel_evento'        => 'Control',
            'fecha'               => $fechaRealSesion, // 👈 Inyectamos la fecha exacta de la sesión
            'splits'              => [25 => '15.00', 50 => '15.00', 75 => '15.00', 100 => '15.00'],
            'virajes'             => [],
            'brazadas_por_largo'  => 16,
            'observaciones'       => 'TestIntegracion'
        ], $overrides);
    }

    public function test_CP_INT_06_01_fraude_logistico_asistencia(): void
    {
        $payloadInvalido = $this->payloadBase(['id_atleta' => 17]); // Atleta 17 está Ausente
        $marca = new Marca();
        $marca->setAtributos($payloadInvalido);
        $resultado = $marca->getRegistrarMarca();

        $this->assertFalse($resultado, 'FALLO: El sistema permitió registrar una marca de un atleta Ausente.');
        $errores = $marca->obtenerErrores();
        $this->assertStringContainsString('Fraude Logístico', json_encode($errores, JSON_UNESCAPED_UNICODE));
    }

    // ─────────────────────────────────────────────────────────────
    // CP-INT-06.02: Fraude Logístico (Evento - Inscripción)
    // ─────────────────────────────────────────────────────────────
    public function test_CP_INT_06_02_fraude_logistico_evento(): void
    {
        // Atleta 13 NO está inscrito en el Evento 1 (REGIONAL)
        $payloadInvalido = $this->payloadBase([
            'id_sesion' => null,
            'id_evento' => 1,
            'id_atleta' => 13,
            'fecha'     => '2026-09-15' // Fecha dentro del rango del Evento 1
        ]);

        $marca = new Marca();
        $marca->setAtributos($payloadInvalido);
        $resultado = $marca->getRegistrarMarca();

        $this->assertFalse($resultado, 'FALLO: El sistema permitió registrar una marca de un atleta no inscrito.');
        $errores = $marca->obtenerErrores();
        $this->assertStringContainsString('Fraude Logístico', json_encode($errores, JSON_UNESCAPED_UNICODE));
    }

    public function test_CP_INT_06_03_recalcular_pb(): void
    {
        $marca = new Marca();
        $marca->setAtributos($this->payloadBase([
            'tiempo_final_seg' => '55.50',
            'splits' => [25 => '13.50', 50 => '14.00', 75 => '14.00', 100 => '14.00']
        ]));
        
        // 🚨 DIAGNÓSTICO: Si falla, imprimimos los errores exactos
        $creado = $marca->getRegistrarMarca();
        if (!$creado) {
            $this->fail('FALLÓ AL CREAR LA MARCA. Errores del modelo: ' . json_encode($marca->obtenerErrores(), JSON_UNESCAPED_UNICODE));
        }
        
        self::$idMarcaCreada = $marca->getCampo('id_marca');

        $stmt = self::$pdo->query("SELECT es_pb FROM marcas WHERE id_marca = " . (int)self::$idMarcaCreada);
        $this->assertEquals(1, $stmt->fetchColumn(), 'FALLO: La nueva marca no se marcó como PB.');

        $stmt = self::$pdo->query("SELECT es_pb FROM marcas WHERE id_marca = 1");
        $this->assertEquals(0, $stmt->fetchColumn(), 'FALLO: La marca antigua no perdió el PB.');
    }

    public function test_CP_INT_06_04_anular_pb_y_autoreparacion(): void
    {
        $m = new Marca(); 
        $m->setAtributos($this->payloadBase([
            'tiempo_final_seg' => '54.00',
            'splits' => [25 => '13.00', 50 => '13.50', 75 => '13.50', 100 => '14.00']
        ])); 
        
        $creado = $m->getRegistrarMarca();
        if (!$creado) {
            $this->fail('FALLÓ AL CREAR LA MARCA (Para anular). Errores: ' . json_encode($m->obtenerErrores(), JSON_UNESCAPED_UNICODE));
        }
        $idNuevaPb = $m->getCampo('id_marca');

        $mEliminar = new Marca();
        $mEliminar->setAtributos(['id_marca' => $idNuevaPb, 'motivo_eliminacion' => 'Prueba de auto-reparacion']);
        
        $eliminado = $mEliminar->getEliminarMarca();
        if (!$eliminado) {
            $this->fail('FALLÓ AL ANULAR LA MARCA. Errores: ' . json_encode($mEliminar->obtenerErrores(), JSON_UNESCAPED_UNICODE));
        }

        $stmt = self::$pdo->query("SELECT es_pb FROM marcas WHERE id_marca = 1");
        $this->assertEquals(1, $stmt->fetchColumn(), 'FALLO: La marca anterior no recuperó el PB tras la anulación.');

        self::$pdo->exec("DELETE FROM marcas_splits WHERE id_marca = " . (int)$idNuevaPb);
        self::$pdo->exec("DELETE FROM marcas WHERE id_marca = " . (int)$idNuevaPb);
    }

    // ─────────────────────────────────────────────────────────────
    // CP-INT-06.05: Reactivar Marca y recalcular PB
    // ─────────────────────────────────────────────────────────────
    public function test_CP_INT_06_05_reactivar_marca_y_pb(): void
    {
        // 1. Creamos una marca rápida (52.00s) y la coronamos como PB
        $m = new Marca(); 
        $m->setAtributos($this->payloadBase([
            'tiempo_final_seg' => '52.00',
            'splits' => [25 => '12.50', 50 => '13.00', 75 => '13.00', 100 => '13.50']
        ])); 
        $creado = $m->getRegistrarMarca();
        if (!$creado) { $this->fail('FALLÓ AL CREAR LA MARCA.'); }
        self::$idMarcaCreada = $m->getCampo('id_marca');

        // 2. La anulamos
        $mEliminar = new Marca();
        $mEliminar->setAtributos(['id_marca' => self::$idMarcaCreada, 'motivo_eliminacion' => 'Prueba de reactivacion']);
        $mEliminar->getEliminarMarca();

        // 3. La reactivamos
        $mReactivar = new Marca();
        // Asumimos que el método se llama getReactivarMarca() o reactivarMarca()
        $mReactivar->setAtributos(['id_marca' => self::$idMarcaCreada]);
        
        // Nota: Ajusta el nombre del método si en tu modelo se llama diferente (ej. activarMarca())
        $reactivado = $mReactivar->getReactivarMarca(); 
        
        if (!$reactivado) {
            $this->fail('FALLÓ AL REACTIVAR LA MARCA. Errores: ' . json_encode($mReactivar->obtenerErrores(), JSON_UNESCAPED_UNICODE));
        }

        // 4. Verificamos que volvió a ser Activo y recuperó el PB
        $stmt = self::$pdo->query("SELECT estado, es_pb FROM marcas WHERE id_marca = " . (int)self::$idMarcaCreada);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Activo', $resultado['estado'], 'FALLO: La marca no volvió a estado Activo.');
        $this->assertEquals(1, $resultado['es_pb'], 'FALLO: La marca reactivada no recuperó el PB.');
    }
   
}