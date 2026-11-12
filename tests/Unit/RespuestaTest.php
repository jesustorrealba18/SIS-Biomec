<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * CP-UNI: pruebas del helper de respuestas JSON (utils/Respuesta.php).
 *
 * Nota de disenio: los metodos enviar()/manejarExcepcion() terminan con exit(),
 * lo que impide ejecutarlos dentro del proceso de PHPUnit. Ese comportamiento
 * (echo + codigo HTTP + exit) se cubre a nivel de integracion mediante las
 * pruebas HTTP de la suite Feature. Aqui se valida el catalogo de mensajes
 * que alimenta la estructura JSON.
 */
class RespuestaTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // utils/Respuesta.php no esta mapeada en el autoload de Composer
        require_once RAIZ . 'utils/Respuesta.php';
    }

    private function catalogoMensajes(): array
    {
        $propiedad = new ReflectionProperty(\Respuesta::class, 'mensajes');
        $propiedad->setAccessible(true);
        return $propiedad->getValue();
    }

    // CP-UNI-36: el catalogo contiene las claves que usan los controladores
    public function testCatalogoContieneLasClavesDelSistema(): void
    {
        $mensajes = $this->catalogoMensajes();
        $this->assertArrayHasKey('success_reg', $mensajes);
        $this->assertArrayHasKey('error_db', $mensajes);
        $this->assertArrayHasKey('error_dup', $mensajes);
        $this->assertArrayHasKey('error_404', $mensajes);
    }

    // CP-UNI-37: todos los mensajes son textos legibles no vacios
    public function testTodosLosMensajesSonTextosNoVacios(): void
    {
        foreach ($this->catalogoMensajes() as $clave => $mensaje) {
            $this->assertIsString($mensaje, "El mensaje '$clave' debe ser string.");
            $this->assertNotSame('', trim($mensaje), "El mensaje '$clave' no debe estar vacio.");
        }
    }

    // CP-UNI-38: el mensaje de duplicado comunica el problema al usuario
    public function testMensajeDeDuplicadoEsClaroParaElUsuario(): void
    {
        $mensajes = $this->catalogoMensajes();
        $this->assertStringContainsString('ya existe', $mensajes['error_dup']);
    }

    // CP-UNI-39: el mensaje de error tecnico no expone detalles de la BD
    public function testMensajeDeErrorTecnicoNoFiltraDetallesInternos(): void
    {
        $mensajes = $this->catalogoMensajes();
        $this->assertStringNotContainsString('SQLSTATE', $mensajes['error_db']);
        $this->assertStringNotContainsString('mysql', strtolower($mensajes['error_db']));
        $this->assertStringNotContainsString('password', strtolower($mensajes['error_db']));
    }
}
