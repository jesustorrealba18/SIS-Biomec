<?php

namespace Tests\Smoke\Asistencia;

use GrupoProyecto\SisBiomec\modelo\Asistencia;
use PHPUnit\Framework\TestCase;

/**
 * 
 * Prueba de Humo (Smoke Test) - Flujo CRUD de Asistencias
 * Valida que un registro pueda crearse, leerse, actualizarse (Upsert) y limpiarse.
 * 
 * REQUISITO: Base de datos MySQL activa (XAMPP).
 */
class AsistenciaSmokeTest extends TestCase
{
    private static $idSesionTest = 9; // Asegúrate de que esta sesión exista en tu BD
    private static $idAtletaTest = 13; // Asegúrate de que este atleta exista y esté activo
    private $datosPrueba = [];

    protected function setUp(): void
    {
        // Definimos el payload válido para interactuar con el modelo
        $this->datosPrueba = [
            'id_sesion'         => self::$idSesionTest, 
            'id_atleta'         => self::$idAtletaTest, 
            'estado_asistencia' => 'Presente',
            'justificacion'     => 'No aplica',
            'tipo'              => 'Manual',
            'id_usuario'        => 2 // ID del entrenador logueado
        ];
    }

       public function test_CP_HUM_03_01_flujo_crud_completo(): void
    {
        $asistencia = new Asistencia();
        
        // 1. CREATE
        $asistencia->setDatos($this->datosPrueba);
        $creado = $asistencia->RegistrarManual();
        
        if (!$creado) {
            $errores = $asistencia->obtenerErrores();
            $this->fail('FALLO en CREATE. Errores: ' . json_encode($errores, JSON_UNESCAPED_UNICODE));
        }
        $this->assertTrue($creado, 'FALLO en CREATE');

        // 2. READ
        $listaAtletas = $asistencia->obtenerAtletasPorSesion(self::$idSesionTest);
        $atletaEncontrado = null;
        foreach ($listaAtletas as $a) {
            if ($a['id_atleta'] == self::$idAtletaTest) { $atletaEncontrado = $a; break; }
        }
        $this->assertNotNull($atletaEncontrado, 'FALLO en READ: No se encontró al atleta.');
        $this->assertEquals('Presente', $atletaEncontrado['estado']);

        // 3. UPDATE (Cambiamos a Justificado para probar que guarde la justificación)
        $datosActualizacion = $this->datosPrueba;
        $datosActualizacion['estado_asistencia'] = 'Justificado';
        $datosActualizacion['justificacion'] = 'Reposo por lesión muscular';

        $asistenciaActualizar = new Asistencia();
        $asistenciaActualizar->setDatos($datosActualizacion);
        $actualizado = $asistenciaActualizar->RegistrarManual();
        
        $this->assertTrue($actualizado, 'FALLO en UPDATE: No se pudo actualizar.');

        // Verificamos el cambio
        $listaActualizada = $asistenciaActualizar->obtenerAtletasPorSesion(self::$idSesionTest);
        $atletaActualizado = null;
        foreach ($listaActualizada as $a) {
            if ($a['id_atleta'] == self::$idAtletaTest) { $atletaActualizado = $a; break; }
        }
        $this->assertEquals('Justificado', $atletaActualizado['estado']);
        $this->assertEquals('Reposo por lesión muscular', $atletaActualizado['justificacion']);
    }
    
    protected function tearDown(): void
    {
        // 🧹 LIMPIEZA TOTAL: Borramos físicamente el registro para no dejar basura en la BD.
        // Usamos la misma lógica de tu prueba de Marcas.
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=sis_natacion;charset=utf8', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("DELETE FROM asistencia WHERE id_sesion = ? AND id_atleta = ?");
            $stmt->execute([self::$idSesionTest, self::$idAtletaTest]);
            
        } catch (\Exception $e) {
            // Silencioso, solo es limpieza
        }
    }
}