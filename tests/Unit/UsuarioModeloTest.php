<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\UsuarioModelo;

class UsuarioModeloTest extends TestCase
{
    public function testContrasenaDemasiadoCortaDebeFallar()
    {
        $modelo = new UsuarioModelo();
        // Probamos una contraseña de 3 caracteres (el mínimo es 6)
        $errores = $modelo->validarContrasena('Ab1');
        
        $this->assertNotEmpty($errores, 'Debería haber un error por contraseña corta.');
        $this->assertArrayHasKey('contrasena', $errores);
        // Quitamos el [0] porque el mensaje es un string directo
        $this->assertStringContainsString('al menos 6 caracteres', $errores['contrasena']);
    }

    public function testContrasenaSinNumerosDebeFallar()
    {
        $modelo = new UsuarioModelo();
        // Contraseña de 8 caracteres pero sin números
        $errores = $modelo->validarContrasena('Password');
        
        $this->assertNotEmpty($errores);
        // Quitamos el [0] aquí también
        $this->assertStringContainsString('al menos un numero', $errores['contrasena']);
    }

    public function testContrasenaValidaDebePasar()
    {
        $modelo = new UsuarioModelo();
        // Contraseña de 8 caracteres, con letra y número (Cumple todas las reglas)
        $errores = $modelo->validarContrasena('Password123');
        
        $this->assertEmpty($errores, 'No debería haber errores con una contraseña válida.');
    }

    public function testContrasenaSoloNumerosDebeFallar()
    {
        $modelo = new UsuarioModelo();
        $errores = $modelo->validarContrasena('87654321');

        $this->assertArrayHasKey('contrasena', $errores);
        $this->assertStringContainsString('al menos una letra', $errores['contrasena']);
    }

    public function testContrasenaSoloLetrasDebeFallar()
    {
        $modelo = new UsuarioModelo();
        $errores = $modelo->validarContrasena('abcdefgh');

        $this->assertArrayHasKey('contrasena', $errores);
        $this->assertStringContainsString('al menos un numero', $errores['contrasena']);
    }

    public function testContrasenaEnElLimiteMinimoDebePasar()
    {
        $modelo = new UsuarioModelo();
        // Exactamente 6 caracteres: letra + numero
        $this->assertSame([], $modelo->validarContrasena('abc123'));
    }

    public function testContrasenaExcesivamenteLargaDebeFallar()
    {
        $modelo = new UsuarioModelo();
        // 130 caracteres supera el maximo de 128
        $errores = $modelo->validarContrasena(str_repeat('a1', 65));

        $this->assertArrayHasKey('contrasena', $errores);
        $this->assertStringContainsString('no puede superar', $errores['contrasena']);
    }

    public function testContrasenaVaciaDebeFallar()
    {
        $modelo = new UsuarioModelo();
        $errores = $modelo->validarContrasena('');

        $this->assertArrayHasKey('contrasena', $errores);
        $this->assertStringContainsString('al menos 6 caracteres', $errores['contrasena']);
    }
}