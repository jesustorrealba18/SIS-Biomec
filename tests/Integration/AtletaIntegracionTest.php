<?php

namespace Tests\Integration;

use GrupoProyecto\SisBiomec\modelo\Atleta;

/**
 * CP-INT: pruebas de integracion del modelo de atletas contra la BD real
 * (sis_natacion). Verifica el flujo completo: validacion -> guardar ->
 * persistencia en atletas + atleta_datos_medicos -> actualizar -> soft delete.
 *
 * Nota: un TRIGGER de BD reasigna id_categoria segun la edad al insertar,
 * por lo que no se aserta sobre id_categoria sino sobre campos directos.
 */
class AtletaIntegracionTest extends IntegrationTestCase
{
    private function categoriaAdultaId(): int
    {
        return (int)$this->pdoNatacion->query(
            'SELECT id_categoria FROM categorias_feveda WHERE activa = 1 ORDER BY edad_minima DESC LIMIT 1'
        )->fetchColumn();
    }

    private function datosAtleta(): array
    {
        return [
            'cedula'                         => $this->cedulaPrueba(),
            'nombres'                        => 'TESTPRUEBA',
            'apellidos'                      => 'Integracion',
            'fecha_nacimiento'               => '1999-05-10',
            'sexo'                           => 'M',
            'direccion'                      => 'Av. Prueba N 123',
            'telefono'                       => '0212-555123',
            'correo'                         => $this->correoPrueba(),
            'fecha_registro_club'            => '2023-01-15',
            'estado'                         => 'Activo',
            'grupo_sanguineo'                => 'O+',
            'seguro_medico'                  => 'Seguro de prueba',
            'alergias'                       => 'Ninguna conocida',
            'condiciones_previas'            => 'Ninguna',
            'contacto_emergencia_nombre'     => 'Contacto Prueba',
            'contacto_emergencia_telefono'   => '0212-555124',
            'contacto_emergencia_parentesco' => 'Padre',
            'id_categoria'                   => $this->categoriaAdultaId(),
            'numero_feveda'                  => 'TN-0001',
            'club_procedencia'               => 'Club de prueba',
        ];
    }

    private function buscarIdAtleta(string $correo): int
    {
        $sentencia = $this->pdoNatacion->prepare('SELECT id_atleta FROM atletas WHERE correo = ?');
        $sentencia->execute([$correo]);
        return (int)$sentencia->fetchColumn();
    }

    // CP-INT-07: setDatos con datos completos no genera errores y crea token
    public function testSetDatosValidoNoGeneraErroresYGeneraToken(): void
    {
        $atleta = new Atleta();
        $atleta->setDatos($this->datosAtleta());

        $this->assertFalse($atleta->hayErrores(), 'Errores: ' . json_encode($atleta->obtenerErrores()));
        $token = (string)$atleta->getCampo('token_asistencia');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $token, 'El token de asistencia debe ser 32 hex.');
    }

    // CP-INT-08: guardar persiste en atletas y atleta_datos_medicos
    public function testGuardarPersisteAtletaYDatosMedicos(): void
    {
        $datos = $this->datosAtleta();
        $atleta = new Atleta();
        $atleta->setDatos($datos);

        $this->assertTrue($atleta->guardar(), 'guardar() debe retornar true con datos validos.');

        $id = $this->buscarIdAtleta($datos['correo']);
        $this->assertGreaterThan(0, $id, 'El atleta debe existir en la BD.');

        $fila = $this->pdoNatacion->query("SELECT nombres, sexo, estado FROM atletas WHERE id_atleta = {$id}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('TESTPRUEBA', $fila['nombres']);
        $this->assertSame('M', $fila['sexo']);
        $this->assertSame('Activo', $fila['estado']);

        $medicos = $this->pdoNatacion->query("SELECT grupo_sanguineo, numero_feveda FROM atleta_datos_medicos WHERE id_atleta = {$id}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($medicos, 'Debe existir el registro 1:1 de datos medicos.');
        $this->assertSame('O+', $medicos['grupo_sanguineo']);
    }

    // CP-INT-09: la validacion rechaza datos incompletos y no inserta
    public function testDatosIncompletosGeneranErroresYNoInsertan(): void
    {
        $datos = $this->datosAtleta();
        unset($datos['cedula'], $datos['correo']);

        $atleta = new Atleta();
        $atleta->setDatos($datos);

        $this->assertTrue($atleta->hayErrores(), 'Debe haber errores de validacion.');
        $this->assertArrayHasKey('cedula', $atleta->obtenerErrores());

        $this->assertFalse($atleta->guardar(), 'guardar() debe fallar sin cedula (NOT NULL en BD).');
        $this->assertSame(0, $this->buscarIdAtleta($datos['correo'] ?? ''), 'No debe insertarse ningun registro.');
    }

    // CP-INT-10: actualizar modifica el telefono del atleta
    public function testActualizarModificaDatosDelAtleta(): void
    {
        $datos = $this->datosAtleta();
        $atleta = new Atleta();
        $atleta->setDatos($datos);
        $this->assertTrue($atleta->guardar());

        $id = $this->buscarIdAtleta($datos['correo']);
        $this->assertGreaterThan(0, $id);

        $editor = new Atleta();
        $editor->setDatos($datos);
        $editor->setCampo('id_atleta', $id);
        $editor->setCampo('telefono', '0416-9998877');

        $this->assertTrue($editor->actualizar(), 'actualizar() debe retornar true.');

        $telefono = $this->pdoNatacion->query("SELECT telefono FROM atletas WHERE id_atleta = {$id}")->fetchColumn();
        $this->assertSame('0416-9998877', $telefono);
    }

    // CP-INT-11: eliminar es un borrado logico (Inactivo)
    public function testEliminarEsBorradoLogico(): void
    {
        $datos = $this->datosAtleta();
        $atleta = new Atleta();
        $atleta->setDatos($datos);
        $this->assertTrue($atleta->guardar());

        $id = $this->buscarIdAtleta($datos['correo']);
        $this->assertTrue($atleta->eliminar($id));

        $estado = $this->pdoNatacion->query("SELECT estado FROM atletas WHERE id_atleta = {$id}")->fetchColumn();
        $this->assertSame('Inactivo', $estado, 'eliminar() debe marcar Inactivo, no borrar la fila.');

        $existe = $this->pdoNatacion->query("SELECT COUNT(*) FROM atletas WHERE id_atleta = {$id}")->fetchColumn();
        $this->assertSame(1, (int)$existe, 'La fila debe seguir existiendo.');
    }

    // CP-INT-12: listar incluye al atleta recien creado
    public function testListarIncluyeAlAtletaCreado(): void
    {
        $datos = $this->datosAtleta();
        $atleta = new Atleta();
        $atleta->setDatos($datos);
        $this->assertTrue($atleta->guardar());

        $listado = $atleta->listar();
        $this->assertNotEmpty($listado);

        $cedulas = array_column($listado, 'cedula');
        $this->assertContains($datos['cedula'], $cedulas, 'El atleta creado debe aparecer en el listado.');
    }
}
