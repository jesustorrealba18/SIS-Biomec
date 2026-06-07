<?php
namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Login extends Conexion {
    public function __construct() {
        parent::__construct('sis_seguridad');
    }

    public function validarUsuario($usuario, $password) {
        $conex = $this->getConex1();
        try {
            if ($this->estaIpLimitada()) {
                return ['error' => 'credenciales'];
            }

            $sql = "SELECT u.id_usuario, u.nombres, u.apellidos, u.correo, 
                           u.contrasena_hash, u.activo, u.bloqueado_hasta, u.intentos_fallidos,
                           GROUP_CONCAT(r.nombre SEPARATOR ', ') AS roles
                    FROM usuarios u
                    LEFT JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario
                    LEFT JOIN roles r ON ur.id_rol = r.id_rol
                    WHERE u.correo = :user
                    GROUP BY u.id_usuario";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':user' => $usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->registrarIntento(null, $usuario, 0);
                return ['error' => 'credenciales'];
            }

            if (!$user['activo']) {
                return ['error' => 'inactivo'];
            }

            if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
                return ['error' => 'bloqueado', 'bloqueado_hasta' => $user['bloqueado_hasta']];
            }

            if (!password_verify($password, $user['contrasena_hash'])) {
                $this->registrarIntento($user['id_usuario'], $usuario, 0);
                $this->incrementarIntentos($user['id_usuario']);
                return ['error' => 'credenciales'];
            }

            $this->registrarIntento($user['id_usuario'], $usuario, 1);
            $this->resetearIntentos($user['id_usuario']);

            return $user;
        } catch (PDOException $e) {
            return ['error' => 'sistema'];
        }
    }

    private function registrarIntento($idUsuario, $correo, $exitoso) {
        $conex = $this->getConex1();
        $sql = "INSERT INTO intentos_login (id_usuario, correoIntento, ip_origen, exitoso) 
                VALUES (:id_usuario, :correo, :ip, :exitoso)";
        $stmt = $conex->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':correo'     => $correo,
            ':ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
            ':exitoso'    => $exitoso
        ]);
    }

    private function incrementarIntentos($idUsuario) {
        $conex = $this->getConex1();
        $sql = "UPDATE usuarios 
                SET intentos_fallidos = intentos_fallidos + 1,
                    bloqueado_hasta = CASE 
                        WHEN intentos_fallidos + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
                        ELSE bloqueado_hasta 
                    END
                WHERE id_usuario = :id";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
    }

    private function resetearIntentos($idUsuario) {
        $conex = $this->getConex1();
        $sql = "UPDATE usuarios 
                SET intentos_fallidos = 0, bloqueado_hasta = NULL 
                WHERE id_usuario = :id";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
    }

    private function estaIpLimitada(): bool {
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
