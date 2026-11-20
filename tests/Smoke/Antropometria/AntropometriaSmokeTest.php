<?php

namespace Tests\Smoke\Antropometria;

use GrupoProyecto\SisBiomec\modelo\MedicionAntropometrica;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;

class AntropometriaSmokeTest extends TestCase
{
    private static $idMedicionCreada = null;
    private $pdo;
    private $datosPrueba = [];

    protected function setUp(): void
    {
        // 1. Conexión directa a la BD para verificaciones cruzadas
        $this->pdo = new \PDO('mysql:host=localhost;dbname=sis_natacion;charset=utf8', 'root', '');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // 2. Datos base para la prueba
        $this->datosPrueba = [
            'id_atleta' => 5, // Asegúrate de que este atleta exista en tu BD
            'fecha' => date('Y-m-d'),
            'peso_kg' => '75.50',
            'talla_cm' => '180.00',
            'envergadura_cm' => '185.00',
            'perimetro_abdominal_cm' => '80.00',
            'porcentaje_grasa' => '18.50',
            'responsable' => 'Test Smoke'
        ];
    }

    #[TestDox('CP-HUM-05.01: Flujo CRUD completo de Antropometría')]
    public function test_CP_HUM_05_01_flujo_crud_completo(): void
    {
        $modelo = new MedicionAntropometrica();

        // ==========================================
        // 1. CREATE (Registrar Medición)
        // ==========================================
        $creado = $modelo->registrarMedicion($this->datosPrueba);
        
        if (!$creado) {
            $this->fail('FALLO en CREATE. Errores del modelo: ' . json_encode($modelo->obtenerErroresValidacion()));
        }
        $this->assertTrue($creado, 'FALLO en CREATE: No se pudo registrar la medición.');

        // Recuperamos el ID consultando la BD (ya que el modelo retorna bool)
        $stmt = $this->pdo->prepare("SELECT id_medicion FROM mediciones_antropometricas WHERE id_atleta = ? ORDER BY id_medicion DESC LIMIT 1");
        $stmt->execute([$this->datosPrueba['id_atleta']]);
        self::$idMedicionCreada = $stmt->fetchColumn();

        $this->assertNotNull(self::$idMedicionCreada, 'FALLO: No se recuperó el ID de la medición creada.');

        // Validar cálculo de IMC en BD (75.50 / 1.80^2 = 23.3)
        $stmtIMC = $this->pdo->prepare("SELECT imc FROM mediciones_antropometricas WHERE id_medicion = ?");
        $stmtIMC->execute([self::$idMedicionCreada]);
        $imcGuardado = $stmtIMC->fetchColumn();
        $this->assertEquals(23.3, $imcGuardado, 'FALLO: El IMC calculado no es el esperado.');

        // ==========================================
        // 2. READ (Leer / Consultar)
        // ==========================================
        $detalle = $modelo->obtenerDetallePorId(self::$idMedicionCreada);
        
        $this->assertNotNull($detalle, 'FALLO en READ: No se encontró la medición.');
        $this->assertEquals('75.50', $detalle['peso_kg'], 'FALLO en READ: El peso no coincide.');
        
        //$this->assertEquals('180.00', $detalle['talla_cm'], 'FALLO en READ: La talla no coincide.');
        $this->assertEquals(180.00, (float)$detalle['talla_cm'], 'FALLO en READ: La talla no coincide.');

        // ==========================================
        // 3. UPDATE (Actualizar Medición)
        // ==========================================
        $datosActualizacion = $this->datosPrueba;
        $datosActualizacion['peso_kg'] = '80.00'; // Actualizamos el peso
        $datosActualizacion['porcentaje_grasa'] = '20.00';

        $actualizado = $modelo->actualizarMedicion($datosActualizacion, self::$idMedicionCreada);
        $this->assertTrue($actualizado, 'FALLO en UPDATE: No se pudo actualizar la medición.');

        // Verificar cambio en BD y recálculo de IMC (80 / 1.80^2 = 24.7)
        $stmtIMCActualizado = $this->pdo->prepare("SELECT peso_kg, imc FROM mediciones_antropometricas WHERE id_medicion = ?");
        $stmtIMCActualizado->execute([self::$idMedicionCreada]);
        $medicionActualizada = $stmtIMCActualizado->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('80.00', $medicionActualizada['peso_kg'], 'FALLO en UPDATE: El peso no cambió en BD.');
        $this->assertEquals(24.7, $medicionActualizada['imc'], 'FALLO en UPDATE: El IMC no se recalculó.');

        // ==========================================
        // 4. DELETE (Anulación Lógica)
        // ==========================================
        $motivo = 'Error de tipeo en la medición de prueba';
        $anulado = $modelo->anularMedicion(self::$idMedicionCreada, $motivo);
        
        $this->assertTrue($anulado, 'FALLO en DELETE: No se pudo anular la medición.');

        // Verificar Soft Delete
        $stmtAnulado = $this->pdo->prepare("SELECT deleted_at, motivo_eliminacion FROM mediciones_antropometricas WHERE id_medicion = ?");
        $stmtAnulado->execute([self::$idMedicionCreada]);
        $registroAnulado = $stmtAnulado->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotNull($registroAnulado['deleted_at'], 'FALLO en DELETE: El campo deleted_at sigue en NULL.');
        $this->assertEquals($motivo, $registroAnulado['motivo_eliminacion'], 'FALLO en DELETE: El motivo no se guardó correctamente.');

        // ==========================================
        // 5. RESTORE (Reactivar Medición - Validación extra)
        // ==========================================
        $reactivado = $modelo->reactivarMedicion(self::$idMedicionCreada);
        $this->assertTrue($reactivado, 'FALLO en RESTORE: No se pudo reactivar la medición.');

        $stmtReactivado = $this->pdo->prepare("SELECT deleted_at FROM mediciones_antropometricas WHERE id_medicion = ?");
        $stmtReactivado->execute([self::$idMedicionCreada]);
        $this->assertNull($stmtReactivado->fetchColumn(), 'FALLO en RESTORE: El campo deleted_at no volvió a NULL.');
    }

    protected function tearDown(): void
    {
        // Limpieza física total para que la prueba sea idempotente
        if (self::$idMedicionCreada) {
            try {
                $this->pdo->exec("DELETE FROM alertas_biologicas WHERE id_registro_origen = " . (int)self::$idMedicionCreada . " AND modulo_origen = 'ANTROPOMETRÍA'");
                $this->pdo->exec("DELETE FROM mediciones_antropometricas WHERE id_medicion = " . (int)self::$idMedicionCreada);
            } catch (\Exception $e) {
                // Silencioso si hay error en la limpieza
            }
        }
    }
}