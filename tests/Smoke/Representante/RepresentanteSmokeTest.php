<?php

namespace Tests\Smoke\Representante;

use GrupoProyecto\SisBiomec\modelo\Representante;
use PHPUnit\Framework\TestCase;

class RepresentanteSmokeTest extends TestCase
{
    private static $idRepresentanteCreado = null;
    private $pdo;
    private $datosPrueba = [];

    protected function setUp(): void
    {
        // 1. Conexión directa a la BD para las verificaciones cruzadas
        $this->pdo = new \PDO('mysql:host=localhost;dbname=sis_natacion;charset=utf8', 'root', '');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Aseguramos que el atleta 10 exista en la BD y sea menor de edad
        $this->datosPrueba = [
            'cedula'               => 'V-99887766',
            'nombres'              => 'Representante',
            'apellidos'            => 'De Prueba',
            'fecha_nacimiento'     => date('Y-m-d', strtotime('-35 years')),
            'parentesco'           => 'Padre',
            'telefono_principal'   => '04141112233',
            'correo'               => 'prueba@example.com',
            'direccion_residencia' => 'Calle de Pruebas, Casa 1',
            'atletas_ids'          => [10], // ID del atleta a vincular
            'aut_medica'           => [10 => '1'],
            'aut_imagen'           => [10 => '1']
        ];
    }

    public function test_CP_HUM_18_01_flujo_crud_completo(): void
    {
        $representante = new Representante();
        
        // ==========================================
        // 1. CREATE (Registrar Representante y Vínculo)
        // ==========================================
        $representante->setAtributos($this->datosPrueba);
        $creado = $representante->Registrar();
        
        if (!$creado) {
            $errores = $representante->obtenerErrores();
            $this->fail('FALLO en CREATE. Errores del modelo: ' . json_encode($errores, JSON_UNESCAPED_UNICODE));
        }
        $this->assertTrue($creado, 'FALLO en CREATE: No se pudo registrar el representante.');

        // Recuperamos el ID consultando la BD
        $stmt = $this->pdo->prepare("SELECT id_representante FROM representantes WHERE cedula = ?");
        $stmt->execute(['V-99887766']);
        self::$idRepresentanteCreado = $stmt->fetchColumn();

        $this->assertNotNull(self::$idRepresentanteCreado, 'FALLO: No se recuperó el ID del representante creado.');

        // ⚡ NUEVA VALIDACIÓN: Verificar que el vínculo en la tabla intermedia se creó
        $stmtVinculo = $this->pdo->prepare("SELECT autorizacion_medica, autorizacion_imagen FROM atleta_representante WHERE id_representante = ? AND id_atleta = 10");
        $stmtVinculo->execute([self::$idRepresentanteCreado]);
        $vinculo = $stmtVinculo->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotFalse($vinculo, 'FALLO: El atleta no fue vinculado al representante en la tabla atleta_representante.');
        $this->assertEquals(1, $vinculo['autorizacion_medica'], 'FALLO: La autorización médica no se guardó correctamente.');
        $this->assertEquals(1, $vinculo['autorizacion_imagen'], 'FALLO: La autorización de imagen no se guardó correctamente.');

        // ==========================================
        // 2. READ (Leer / Consultar)
        // ==========================================
        $representanteLeido = new Representante();
        $detalle = $representanteLeido->obtenerPorId(self::$idRepresentanteCreado);
        
        $this->assertNotNull($detalle, 'FALLO en READ: No se encontró el representante.');
        $this->assertEquals('De Prueba', $detalle['apellidos'], 'FALLO en READ: Los apellidos no coinciden.');

        // ==========================================
        // 3. UPDATE (Actualizar Datos y Recrear Vínculos)
        // ==========================================
        $datosActualizacion = $this->datosPrueba;
        $datosActualizacion['cedula_original'] = self::$idRepresentanteCreado; 
        $datosActualizacion['telefono_principal'] = '04249998877'; // Cambiamos el teléfono
        
        // Simulamos que le quitamos la autorización de imagen en la actualización
        $datosActualizacion['aut_imagen'] = [10 => '0'];

        $representanteActualizar = new Representante();
        $representanteActualizar->setAtributos($datosActualizacion);
        $actualizado = $representanteActualizar->Actualizar();
        
        $this->assertTrue($actualizado, 'FALLO en UPDATE: No se pudo actualizar.');

        // Verificamos el cambio en la tabla principal
        $detalleActualizado = $representanteLeido->obtenerPorId(self::$idRepresentanteCreado);
        $this->assertEquals('04249998877', $detalleActualizado['telefono_principal'], 'FALLO en UPDATE: El teléfono no cambió.');

        // ⚡ NUEVA VALIDACIÓN: Verificar que los vínculos se recrearon con los nuevos valores
        $stmtVinculoUpdate = $this->pdo->prepare("SELECT autorizacion_imagen FROM atleta_representante WHERE id_representante = ? AND id_atleta = 10");
        $stmtVinculoUpdate->execute([self::$idRepresentanteCreado]);
        $vinculoUpdate = $stmtVinculoUpdate->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals(0, $vinculoUpdate['autorizacion_imagen'], 'FALLO en UPDATE: La autorización de imagen no se actualizó correctamente en la tabla intermedia.');

        // ==========================================
        // 4. DELETE (Inactivar y desvincular)
        // ==========================================
        $representanteEliminar = new Representante();
        $representanteEliminar->setAtributos(['id_representante' => self::$idRepresentanteCreado]);
        $inactivado = $representanteEliminar->Eliminar();
        
        $this->assertTrue($inactivado, 'FALLO en DELETE: No se pudo inactivar.');

        // Verificamos el estado del representante
        $detalleBorrado = $representanteLeido->obtenerPorId(self::$idRepresentanteCreado);
        $this->assertEquals('Inactivo', $detalleBorrado['estado'], 'FALLO en DELETE: El representante no pasó a estado Inactivo.');

        // ⚡ NUEVA VALIDACIÓN: Verificar que el atleta quedó libre (el vínculo se borró físicamente)
        $stmtVinculoDelete = $this->pdo->prepare("SELECT COUNT(*) FROM atleta_representante WHERE id_representante = ? AND id_atleta = 10");
        $stmtVinculoDelete->execute([self::$idRepresentanteCreado]);
        $conteoVinculo = $stmtVinculoDelete->fetchColumn();

        $this->assertEquals(0, $conteoVinculo, 'FALLO en DELETE: El vínculo del atleta no fue eliminado de la tabla intermedia.');
    }

    protected function tearDown(): void
    {
        // Limpieza física total
        if (self::$idRepresentanteCreado) {
            try {
                $this->pdo->exec("DELETE FROM atleta_representante WHERE id_representante = " . (int)self::$idRepresentanteCreado);
                $this->pdo->exec("DELETE FROM representantes WHERE id_representante = " . (int)self::$idRepresentanteCreado);
            } catch (\Exception $e) {}
        }
    }
}