<?php

namespace GrupoProyecto\SisBiomec\seguridad;

class Captcha {

    private int $ancho = 200;
    private int $alto = 60;
    private int $longitud = 6;

    public function generar(): void {
        $codigo = $this->generarCodigo();
        $_SESSION['captcha_code'] = $codigo;

        $imagen = imagecreatetruecolor($this->ancho, $this->alto);

        $colorFondo = imagecolorallocate($imagen, 240, 240, 245);
        imagefill($imagen, 0, 0, $colorFondo);

        $caracteres = str_split($codigo);
        $total = count($caracteres);
        $espacio = $this->ancho / ($total + 1);

        for ($i = 0; $i < $total; $i++) {
            $colorTexto = imagecolorallocate($imagen, rand(40, 100), rand(40, 100), rand(80, 160));
            $tamano = rand(24, 30);
            $angulo = rand(-15, 15);
            $x = $espacio * ($i + 0.7) + rand(-3, 3);
            $y = ($this->alto / 2) + rand(-6, 6);
            imagettftext($imagen, $tamano, $angulo, $x, $y, $colorTexto, $this->obtenerFuente(), $caracteres[$i]);
        }

        $this->agregarRuido($imagen);
        $this->agregarLineas($imagen);

        header('Content-Type: image/png');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        imagepng($imagen);
        imagedestroy($imagen);
    }

    public static function verificar(string $input): bool {
        if (empty($_SESSION['captcha_code'])) {
            return false;
        }
        $valido = strtolower(trim($input)) === strtolower($_SESSION['captcha_code']);
        unset($_SESSION['captcha_code']);
        return $valido;
    }

    private function generarCodigo(): string {
        $letras = 'abcdefghjkmnpqrstuvwxyz23456789';
        $max = strlen($letras) - 1;
        $codigo = '';
        for ($i = 0; $i < $this->longitud; $i++) {
            $codigo .= $letras[rand(0, $max)];
        }
        return $codigo;
    }

    private function agregarRuido($imagen): void {
        for ($i = 0; $i < 150; $i++) {
            $color = imagecolorallocate($imagen, rand(150, 200), rand(150, 200), rand(150, 200));
            imagesetpixel($imagen, rand(0, $this->ancho - 1), rand(0, $this->alto - 1), $color);
        }
    }

    private function agregarLineas($imagen): void {
        for ($i = 0; $i < 5; $i++) {
            $color = imagecolorallocate($imagen, rand(120, 180), rand(120, 180), rand(120, 180));
            $x1 = rand(0, $this->ancho);
            $y1 = rand(0, $this->alto);
            $x2 = rand(0, $this->ancho);
            $y2 = rand(0, $this->alto);
            imageline($imagen, $x1, $y1, $x2, $y2, $color);
        }
    }

    private function obtenerFuente(): string {
        $dir = dirname(__DIR__, 2) . '/assets/fonts';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fuenteDestino = $dir . '/captcha.ttf';

        if (!file_exists($fuenteDestino)) {
            $fuenteSistema = $this->buscarFuenteSistema();
            if ($fuenteSistema) {
                copy($fuenteSistema, $fuenteDestino);
            }
        }

        return $fuenteDestino;
    }

    private function buscarFuenteSistema(): ?string {
        $candidatos = [
            'C:\Windows\Fonts\arial.ttf',
            'C:\Windows\Fonts\verdana.ttf',
            'C:\Windows\Fonts\tahoma.ttf',
            'C:\Windows\Fonts\calibri.ttf',
        ];
        foreach ($candidatos as $f) {
            if (file_exists($f)) {
                return $f;
            }
        }
        return null;
    }
}
