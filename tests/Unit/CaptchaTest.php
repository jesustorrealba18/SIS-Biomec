<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\seguridad\Captcha;

class CaptchaTest extends TestCase
{
    public function testVerificarRetornaFalseSiNoHaySesion()
    {
        // Simulamos que no hay captcha en la sesión
        unset($_SESSION['captcha_code']);
        
        $captcha = new Captcha();
        $resultado = $captcha->verificar('codigo123');
        
        $this->assertFalse($resultado, 'Debería retornar false si no hay captcha en sesión.');
    }

    public function testVerificarRetornaTrueSiElCodigoCoincide()
    {
        // Simulamos que el usuario ya generó un captcha (guardado en sesión)
        $_SESSION['captcha_code'] = 'aBc12X';
        
        $captcha = new Captcha();
        $resultado = $captcha->verificar('aBc12X'); // Enviamos el mismo código
        
        $this->assertTrue($resultado);
    }

    public function testVerificarDestruyeElCaptchaTrasSuUso()
    {
        $_SESSION['captcha_code'] = 'aBc12X';
        
        $captcha = new Captcha();
        $captcha->verificar('aBc12X'); // Primer intento (exitoso)
        
        // Verificamos que el captcha ya no esté en la sesión
        $this->assertArrayNotHasKey('captcha_code', $_SESSION);
    }
}