<?php
namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Recuperacion extends Conexion {

    private const MINUTOS_VIGENCIA = 60;
    private const MINUTOS_ENFRIAMIENTO = 2;

    public function __construct() {
        parent::__construct('sis_seguridad');
    }

    public function solicitarToken(string $correo): array {
        $conex = $this->getConex1();
        try {
            if ($this->estaIpLimitada()) {
                return ['ok' => false, 'ip_limitada' => true];
            }

            $stmt = $conex->prepare("SELECT id_usuario, nombres, apellidos,
                                            (token_expiracion > DATE_SUB(NOW(), INTERVAL :ventana MINUTE)) AS en_enfriamiento
                                     FROM usuarios
                                     WHERE correo = :correo AND activo = 1
                                     LIMIT 1");
            $stmt->execute([
                ':correo'  => $correo,
                ':ventana' => self::MINUTOS_VIGENCIA - self::MINUTOS_ENFRIAMIENTO
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['ok' => true];
            }

            if ((int)$user['en_enfriamiento'] === 1) {
                return ['ok' => true, 'enfriamiento' => true, 'id_usuario' => (int)$user['id_usuario']];
            }

            $tokenPlano = bin2hex(random_bytes(32));
            $hash = hash('sha256', $tokenPlano);

            $stmt = $conex->prepare("UPDATE usuarios
                                     SET token_recuperacion = :hash,
                                         token_expiracion = DATE_ADD(NOW(), INTERVAL :vigencia MINUTE)
                                     WHERE id_usuario = :id");
            $stmt->execute([
                ':hash'     => $hash,
                ':vigencia' => self::MINUTOS_VIGENCIA,
                ':id'       => (int)$user['id_usuario']
            ]);

            return [
                'ok'         => true,
                'token'      => $tokenPlano,
                'nombre'     => $user['nombres'] . ' ' . $user['apellidos'],
                'id_usuario' => (int)$user['id_usuario'],
                'minutos'    => self::MINUTOS_VIGENCIA
            ];
        } catch (PDOException $e) {
            error_log("ERROR solicitarToken: " . $e->getMessage());
            return ['ok' => false];
        }
    }

    public function validarToken(string $token): ?array {
        $conex = $this->getConex1();
        try {
            $hash = hash('sha256', $token);
            $stmt = $conex->prepare("SELECT id_usuario, nombres, apellidos
                                     FROM usuarios
                                     WHERE token_recuperacion = :hash
                                       AND token_expiracion IS NOT NULL
                                       AND token_expiracion > NOW()
                                       AND activo = 1
                                     LIMIT 1");
            $stmt->execute([':hash' => $hash]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("ERROR validarToken: " . $e->getMessage());
            return null;
        }
    }

    public function restablecerContrasena(string $token, string $nuevaPass): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $hash = hash('sha256', $token);
            $stmt = $conex->prepare("SELECT id_usuario FROM usuarios
                                     WHERE token_recuperacion = :hash
                                       AND token_expiracion IS NOT NULL
                                       AND token_expiracion > NOW()
                                       AND activo = 1
                                     LIMIT 1
                                     FOR UPDATE");
            $stmt->execute([':hash' => $hash]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $conex->rollBack();
                return false;
            }

            $nuevoHash = password_hash($nuevaPass, PASSWORD_BCRYPT, ['cost' => 10]);

            $stmt = $conex->prepare("UPDATE usuarios
                                     SET contrasena_hash = :nuevo_hash,
                                         token_recuperacion = NULL,
                                         token_expiracion = NULL,
                                         intentos_fallidos = 0,
                                         bloqueado_hasta = NULL
                                     WHERE id_usuario = :id");
            $stmt->execute([
                ':nuevo_hash' => $nuevoHash,
                ':id'         => (int)$user['id_usuario']
            ]);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            error_log("ERROR restablecerContrasena: " . $e->getMessage());
            return false;
        }
    }

    private function estaIpLimitada(): bool {
        if (!($_ENV['RATE_LIMIT_ENABLED'] ?? true)) return false;
        $conex = $this->getConex1();
        $sql = "SELECT COUNT(*) FROM intentos_login 
                WHERE ip_origen = :ip 
                AND exitoso = 0 
                AND fecha_intento >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':ip' => $_SERVER['REMOTE_ADDR'] ?? null]);
        return (int)$stmt->fetchColumn() >= 20;
    }
}
?>
