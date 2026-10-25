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
}