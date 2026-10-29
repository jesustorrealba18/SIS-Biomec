<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Login;

class LoginTest extends TestCase
{
    public function testLoginFallidoConUsuarioInexistente()
    {
        $login = new Login();
        // Intentamos con un correo que sabemos que no existe
        $resultado = $login->validarUsuario('usuario_fantasma_999@test.com', 'password123');
        
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals('credenciales', $resultado['error']);
    }

    public function testLoginFallidoConContrasenaIncorrecta()
    {
        // NOTA: Asegúrate de tener un usuario real en tu BD para esta prueba, 
        // o cambia el correo por uno que sí exista en tu tabla `usuarios`.
        $login = new Login();
        
        // Supongamos que 'admin@test.com' existe en tu BD
        $resultado = $login->validarUsuario('admin@sgrd.com', 'contrasena_incorrecta_123');
        
        // Si el usuario existe, debería devolver error de credenciales
        // Si no existe, también devuelve error de credenciales, 
        // pero esta prueba sirve para verificar que no explote.
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
    }
}