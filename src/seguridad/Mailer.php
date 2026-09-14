<?php

namespace GrupoProyecto\SisBiomec\seguridad;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTPException;
use Throwable;

class Mailer {

    public static function enviar(string $destino, string $asunto, string $cuerpoHtml): bool {
        if (empty($_ENV['MAIL_HOST']) || empty($_ENV['MAIL_USER'])) {
            error_log("MAILER: Configuracion SMTP incompleta en .env (MAIL_HOST / MAIL_USER).");
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USER'];
            $mail->Password   = $_ENV['MAIL_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPDebug  = 0;

            $mail->setFrom($_ENV['MAIL_FROM'] ?? $_ENV['MAIL_USER'], $_ENV['MAIL_FROM_NAME'] ?? 'SGRD');
            $mail->addAddress($destino);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpoHtml;
            $mail->AltBody = strip_tags($cuerpoHtml);

            return $mail->send();
        } catch (Throwable $e) {
            error_log("MAILER: Fallo al enviar correo a [{$destino}]: " . $e->getMessage());
            return false;
        }
    }

    public static function plantillaRecuperacion(string $nombre, string $enlace, int $minutosVigencia): string {
        $nombreEsc = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $enlaceEsc = htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<body style="margin:0; padding:0; background-color:#0f0d23; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f0d23; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#161430; border-radius:16px; border:1px solid #252345; padding:40px;">
                    <tr>
                        <td style="text-align:center; padding-bottom:24px;">
                            <h1 style="color:#ffffff; font-size:24px; margin:0;">SGRD</h1>
                            <p style="color:#a0a0c0; font-size:12px; letter-spacing:2px; margin:6px 0 0 0; text-transform:uppercase;">Recuperacion de contrasena</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#c8c8e0; font-size:14px; line-height:1.6; padding-bottom:24px;">
                            Hola <strong style="color:#ffffff;">{$nombreEsc}</strong>,<br><br>
                            Recibimos una solicitud para restablecer la contrasena de tu cuenta.
                            Haz clic en el siguiente boton para continuar:
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-bottom:28px;">
                            <a href="{$enlaceEsc}"
                               style="display:inline-block; padding:14px 36px; border-radius:12px; text-decoration:none; color:#ffffff; font-weight:bold; font-size:14px; background:linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);">
                                Restablecer contrasena
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#8a8ab0; font-size:12px; line-height:1.6; padding-bottom:12px;">
                            Si no solicitaste este cambio, puedes ignorar este mensaje. Tu contrasena actual no sera modificada.
                            El enlace expira en <strong style="color:#a0a0c0;">{$minutosVigencia} minutos</strong> y puede usarse una sola vez.
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#5a5a80; font-size:11px; line-height:1.5;">
                            Si el boton no funciona, copia y pega este enlace en tu navegador:<br>
                            <span style="color:#3a7bd5; word-break:break-all;">{$enlaceEsc}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
?>
