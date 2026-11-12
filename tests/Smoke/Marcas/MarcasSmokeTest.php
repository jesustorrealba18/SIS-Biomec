<?php

namespace Tests\Smoke\Marcas;

use GrupoProyecto\SisBiomec\modelo\Marca;
use PHPUnit\Framework\TestCase;

/**
 * Prueba de Humo (Smoke Test) - CRUD Básico de Marcas
 * Valida que un registro pueda nacer, leerse, modificarse y archivarse.
 * 
 * REQUISITO: Base de datos MySQL activa (XAMPP).
 */
class MarcasSmokeTest extends TestCase
{
    private static $idMarcaCreada = null;
    private $datosPrueba = [];

    protected function setUp(): void
    {
        // Definimos un payload válido para crear la marca (usando IDs que existan en tu BD)
        // Nota: Si los IDs 1 no existen en tu BD, cambia estos valores por unos reales.
        $this->datosPrueba = [
            'id_atleta'        => 9, 
            'id_sesion'        => 8, // Debe existir y el atleta debe estar 'Presente'
            'id_evento'        => null,
            'estilo'           => 'Libre',
            'distancia_m'      => 100,
            'tipo_piscina'     => '50m',
            'tiempo_final_seg' => '60.00',
            'fecha'            => '2026-11-09',//Fecha de la sesion o evento
            'splits'           => [25 => '15.00', 50 => '15.00', 75 => '15.00', 100 => '15.00'],
            'virajes'          => [],
            'observaciones'    => 'Prueba de humo automatizada SGRD'
        ];
    }

    public function test_CP_HUM_06_01_flujo_crud_completo(): void
    {
        $marca = new Marca();
        
        // ==========================================
        // 1. CREATE (Registrar)
        // ==========================================
        $marca->setAtributos($this->datosPrueba);
        $creado = $marca->getRegistrarMarca();
        
         if (!$creado) {
            $errores = $marca->obtenerErrores();
            $this->fail('FALLO en CREATE. Errores del modelo: ' . json_encode($errores, JSON_UNESCAPED_UNICODE));
        }

        $this->assertTrue($creado, 'FALLO en CREATE: No se pudo registrar la marca inicial.');
        
        // Guardamos el ID para las siguientes fases
        self::$idMarcaCreada = $marca->getCampo('id_marca');
        $this->assertNotNull(self::$idMarcaCreada, 'FALLO: No se retornó un ID de marca válido.');

        // ==========================================
        // 2. READ (Leer / Consultar)
        // ==========================================
        $marcaLeida = new Marca();
        $detalle = $marcaLeida->obtenerDetallePorId(self::$idMarcaCreada);
        
        $this->assertNotNull($detalle, 'FALLO en READ: No se encontró la marca recién creada.');
        $this->assertEquals('60.00', $detalle['tiempo_final_seg'], 'FALLO en READ: El tiempo no coincide.');
        $this->assertCount(4, $detalle['splits'], 'FALLO en READ: No se guardaron los 4 splits.');

        // ==========================================
        // 3. UPDATE (Actualizar)
        // ==========================================
        $datosActualizacion = $this->datosPrueba;
        $datosActualizacion['id_marca'] = self::$idMarcaCreada;
        $datosActualizacion['tiempo_final_seg'] = '58.50'; // Mejoramos el tiempo
        $datosActualizacion['splits'] = [25 => '14.50', 50 => '14.50', 75 => '14.50', 100 => '15.00']; // Ajustamos splits

        $marcaActualizar = new Marca();
        $marcaActualizar->setAtributos($datosActualizacion);
        $actualizado = $marcaActualizar->getActualizarMarca();
        
        $this->assertTrue($actualizado, 'FALLO en UPDATE: No se pudo actualizar la marca.');

        // Verificamos que el cambio se haya guardado
        $marcaVerificar = new Marca();
        $detalleActualizado = $marcaVerificar->obtenerDetallePorId(self::$idMarcaCreada);
        $this->assertEquals('58.50', $detalleActualizado['tiempo_final_seg'], 'FALLO en UPDATE: El tiempo no se modificó en la BD.');

        // ==========================================
        // 4. DELETE (Archivar / Borrado Lógico)
        // ==========================================
        $marcaEliminar = new Marca();
        $marcaEliminar->setAtributos([
            'id_marca' => self::$idMarcaCreada,
            'motivo_eliminacion' => 'Prueba de humo: limpieza de datos'
        ]);
        $eliminado = $marcaEliminar->getEliminarMarca();
        
        $this->assertTrue($eliminado, 'FALLO en DELETE: No se pudo archivar la marca.');

        // Verificamos que el registro ahora esté Inactivo
        $marcaVerificarBorrado = new Marca();
        $detalleBorrado = $marcaVerificarBorrado->obtenerDetallePorId(self::$idMarcaCreada);
        $this->assertEquals('Inactivo', $detalleBorrado['estado'], 'FALLO en DELETE: La marca no pasó a estado Inactivo.');
    }

    protected function tearDown(): void
    {
        // 🧹 LIMPIEZA TOTAL: Si por alguna razón el test falló antes del DELETE,
        // nos aseguramos de borrar físicamente el registro de la BD para no dejar basura.
        if (self::$idMarcaCreada) {
            try {
                // Como la prueba unitaria no tiene el objeto PDO expuesto, hacemos una conexión rápida
                $pdo = new \PDO('mysql:host=localhost;dbname=sis_natacion;charset=utf8', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $pdo->exec("DELETE FROM marcas_splits WHERE id_marca = " . (int)self::$idMarcaCreada);
                $pdo->exec("DELETE FROM marcas_swolf WHERE id_marca = " . (int)self::$idMarcaCreada);
                $pdo->exec("DELETE FROM marcas WHERE id_marca = " . (int)self::$idMarcaCreada);
            } catch (\Exception $e) {
                // Silencioso, solo es limpieza
            }
        }
    }
}