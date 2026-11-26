<?php
namespace Tests\Integration\sesiones;

use GrupoProyecto\SisBiomec\modelo\Sesiones;

class SesionesModeloTest extends BaseDatabaseTestCase
{
    private Sesiones $modelo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelo = new Sesiones($_ENV['DB_NAME_NATACION']);
    }

    public function testRegistrarSesionPersisteSesionYSeries(): void
    {
        $this->modelo->setDatos($this->payloadSesionValida());
        $this->modelo->setSeries([
            $this->serieValida(['bloque' => 'Calentamiento', 'repeticiones' => 2, 'distancia_m' => 200]),
            $this->serieValida(['bloque' => 'Principal',    'repeticiones' => 4, 'distancia_m' => 100]),
            $this->serieValida(['bloque' => 'VueltaCalma',  'repeticiones' => 1, 'distancia_m' => 100]),
        ]);

        $resultado = $this->modelo->registrarSesion();
        
        if (!$resultado) {
            $errores = $this->modelo->validarDatos();
            $this->fail("Fallo registrarSesion(). Errores de validación: " . print_r($errores, true));
        }
        
        $this->assertTrue($resultado);

        $sesion = $this->db->query("SELECT * FROM sesiones ORDER BY id_sesion DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($sesion);
        $this->assertSame('Planificada', $sesion['estado']);
        $this->assertSame(900, (int)$sesion['volumen_planificado']);

        $series = $this->db->query("SELECT * FROM series_sesion WHERE id_sesion = {$sesion['id_sesion']} ORDER BY orden_ejecucion")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(3, $series);
        $this->assertSame('Calentamiento', $series[0]['bloque']);
    }

    public function testRegistrarSesionRechazaFechaPasadaSinInsertar(): void
    {
        $this->modelo->setDatos($this->payloadSesionValida([
            'fecha' => date('Y-m-d', strtotime('-3 days')),
        ]));
        $this->modelo->setSeries([$this->serieValida()]);

        $this->assertFalse($this->modelo->registrarSesion());
        $this->assertSame(0, (int)$this->db->query("SELECT COUNT(*) FROM sesiones")->fetchColumn());
    }

    public function testRegistrarSesionEsAtomicaAnteSerieInvalida(): void
    {
        $this->modelo->setDatos($this->payloadSesionValida());
        $this->modelo->setSeries([
            $this->serieValida(),
            $this->serieValida(['repeticiones' => 0]),
        ]);

        $this->assertFalse($this->modelo->registrarSesion());
        $this->assertSame(0, (int)$this->db->query("SELECT COUNT(*) FROM sesiones")->fetchColumn());
    }

    public function testIniciarSesionCambiaEstadoSoloSiEstaPlanificada(): void
    {
        $id = $this->crearSesionPersistida();
        $this->assertGreaterThan(0, $id, "El ID de la sesión es 0. La sesión no se guardó.");

        $this->db->exec("UPDATE sesiones SET estado = 'Planificada' WHERE id_sesion = {$id}");
        $this->assertSame('Planificada', $this->estadoDe($id), "La BD no se actualizó a Planificada antes de la prueba.");

        $this->modelo->setDatos(['id_sesion' => $id]);
        
        $iniciado = $this->modelo->InicializarSesion();
        $this->assertTrue($iniciado, "InicializarSesion() devolvió false. El estado en BD era: " . $this->estadoDe($id));
        
        $this->assertSame('Parcial', $this->estadoDe($id));

        $this->assertFalse($this->modelo->InicializarSesion());
        $this->assertSame('Parcial', $this->estadoDe($id));
    }

    public function testCompletarSesionValidaVolumenEjecutadoContraPlanificado(): void
    {
        $id = $this->crearSesionPersistida();
        
        $this->db->exec("UPDATE sesiones SET estado = 'Parcial' WHERE id_sesion = {$id}");
        $this->assertSame('Parcial', $this->estadoDe($id), "La BD no se actualizó a Parcial.");

        $this->modelo->setDatos(['id_sesion' => $id, 'volumen_ejecutado' => 1200]);
        $this->assertFalse($this->modelo->completarSesion(), "Debería fallar por exceder volumen planificado (900).");
        $this->assertSame('Parcial', $this->estadoDe($id));

        $this->modelo->setDatos(['id_sesion' => $id, 'volumen_ejecutado' => 850, 'observaciones' => 'Buen ritmo']);
        $this->assertTrue($this->modelo->completarSesion(), "Debería pasar. El estado era: " . $this->estadoDe($id));
        $this->assertSame('Completada', $this->estadoDe($id));
    }

    public function testCancelarSesionNoAfectaCompletadas(): void
    {
        $id = $this->crearSesionPersistida();
        
        $this->db->exec("UPDATE sesiones SET estado = 'Completada' WHERE id_sesion = {$id}");
        $this->assertSame('Completada', $this->estadoDe($id), "La BD no se actualizó a Completada.");

        $this->modelo->setDatos(['id_sesion' => $id]);
        
        $cancelado = $this->modelo->cancelarSesion();
        $this->assertFalse($cancelado, "cancelarSesion() debería devolver false para una sesión Completada.");
        
        $this->assertSame('Completada', $this->estadoDe($id));
    }

    public function testEditarSesionReemplazaSeriesEnTransaccion(): void
    {
        $id = $this->crearSesionPersistida();
        
        $this->db->exec("UPDATE sesiones SET estado = 'Planificada' WHERE id_sesion = {$id}");
        $this->assertSame('Planificada', $this->estadoDe($id));

        $this->modelo->setDatos($this->payloadSesionValida([
            'id_sesion'        => $id,
            'duracion_minutos' => 60,
        ]));
        $this->modelo->setSeries([
            $this->serieValida(['repeticiones' => 10, 'distancia_m' => 50]),
        ]);

        $editado = $this->modelo->editarSesion();
        $this->assertTrue($editado, "editarSesion() devolvió false. Verifica si el modelo requiere estado 'Planificada'.");

        $series = $this->db->query("SELECT * FROM series_sesion WHERE id_sesion = {$id}")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $series);
        $this->assertSame(10, (int)$series[0]['repeticiones']);
    }

    public function testObtenerDetalleSesionIncluyeSeriesYNombreGrupo(): void
    {
        $id = $this->crearSesionPersistida();
        $detalle = $this->modelo->obtenerDetalleSesion($id);

        $this->assertIsArray($detalle, "obtenerDetalleSesion() devolvió null. Revisa si la consulta SQL en tu modelo está fallando.");
        $this->assertSame('Grupo Test', $detalle['grupo_nombre']);
        $this->assertArrayHasKey('series', $detalle);
        $this->assertCount(3, $detalle['series']);
    }

    public function testListarSesionesFiltraPorGrupoYEstado(): void
    {
        $id1 = $this->crearSesionPersistida();
        $id2 = $this->crearSesionPersistida();
        
        $this->db->exec("UPDATE sesiones SET estado = 'Completada' WHERE id_sesion = {$id2}");
        $this->assertSame('Completada', $this->estadoDe($id2));

        $todas    = $this->modelo->listarSesiones();
        $soloPlan = $this->modelo->listarSesiones(null, 'Planificada');
        
        $this->assertCount(2, $todas);
        $this->assertCount(1, $soloPlan, "El filtro por estado 'Planificada' no funcionó. Revisa el método listarSesiones() en tu modelo.");
    }

    private function crearSesionPersistida(): int
    {
        $this->modelo->setDatos($this->payloadSesionValida());
        $this->modelo->setSeries([
            $this->serieValida(['bloque' => 'Calentamiento', 'repeticiones' => 2, 'distancia_m' => 200]),
            $this->serieValida(['bloque' => 'Principal',    'repeticiones' => 4, 'distancia_m' => 100]),
            $this->serieValida(['bloque' => 'VueltaCalma',  'repeticiones' => 1, 'distancia_m' => 100]),
        ]);
        
        $exito = $this->modelo->registrarSesion();
        
        if (!$exito) {
            $errores = $this->modelo->validarDatos();
            $this->fail("Fallo al crear la sesión persistida. Errores: " . print_r($errores, true));
        }

        $id = $this->db->query("SELECT id_sesion FROM sesiones ORDER BY id_sesion DESC LIMIT 1")->fetchColumn();
        
        return (int)$id;
    }

    private function estadoDe(int $id): string
    {
        return (string)$this->db->query("SELECT estado FROM sesiones WHERE id_sesion = {$id}")->fetchColumn();
    }
}