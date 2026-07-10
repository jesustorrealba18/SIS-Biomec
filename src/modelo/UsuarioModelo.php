<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class UsuarioModelo extends Conexion {
    use ValidacionesTrait;

    private array $datos = [];

     /**
     * Lista blanca de campos permitidos (Protección contra Asignación Masiva)
     */
    private array $camposPermitidos = [
        'id_usuario', 'clave', 'valor', 'accion'
    ];

    public function __construct() {
        parent::__construct('sis_seguridad');
    }

    public function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo])) {
                if (is_array($payload[$campo])) {
                    $this->datos[$campo] = $payload[$campo];
                } 
                elseif ($payload[$campo] !== '') {
                    $this->datos[$campo] = $payload[$campo];
                } else {
                    $this->datos[$campo] = null;
                }
            } else {
                $this->datos[$campo] = null;
            }
        }
    }

    public function getCampo(string $clave) {
        return $this->datos[$clave] ?? null;
    }

    public function obtenerDatos(): array {
        return $this->datos;
    }


    public function validarDatos(array $datos, ?string $excluirCorreo = null): array {
        $this->resetearErrores();

        $nombres   = $datos['nombres'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $cedula    = $datos['cedula'] ?? '';
        $correo    = $datos['correo'] ?? '';

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');
        $this->longitud($nombres, 'nombres', 2, 100);

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');
        $this->longitud($apellidos, 'apellidos', 2, 100);

        if (!empty($cedula)) {
            $this->cedula($cedula, 'cedula');
            $this->longitud($cedula, 'cedula', 5, 20);
            $this->unico($this->getConex1(), $cedula, 'usuarios', 'cedula', null, 'id_usuario');
        }

        $this->requerido($correo, 'correo');
        $this->correoValido($correo, 'correo');

        if ($excluirCorreo === null) {
            $this->unico($this->getConex1(), $correo, 'usuarios', 'correo');
        } else {
            $this->unico($this->getConex1(), $correo, 'usuarios', 'correo', (int)$excluirCorreo, 'id_usuario');
        }

        return $this->obtenerErrores();
    }

    public function validarContrasena(string $contrasena): array {
        $this->resetearErrores();
        if (strlen(trim($contrasena)) < 6) {
            $this->agregarError('contrasena', 'La contrasena debe tener al menos 6 caracteres.');
        }
        if (strlen(trim($contrasena)) > 128) {
            $this->agregarError('contrasena', 'La contrasena no puede superar 128 caracteres.');
        }
        return $this->obtenerErrores();
    }

    public function listarUsuarios(): array {
        $conex = $this->getConex1();
        $sql = "SELECT * FROM v_usuario_completo ORDER BY id_usuario DESC";
        $stmt = $conex->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): ?array {
        $conex = $this->getConex1();
        $sql = "SELECT u.id_usuario, u.cedula, u.nombres, u.apellidos, u.correo,
                       u.activo, u.bloqueado_hasta, u.intentos_fallidos,
                       u.fecha_creacion,
                       GROUP_CONCAT(r.id_rol) AS roles_ids,
                       GROUP_CONCAT(r.nombre SEPARATOR ', ') AS roles
                FROM usuarios u
                LEFT JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario
                LEFT JOIN roles r ON ur.id_rol = r.id_rol
                WHERE u.id_usuario = :id
                GROUP BY u.id_usuario";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function crearUsuario(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $hash = password_hash($datos['contrasena'], PASSWORD_BCRYPT, ['cost' => 10]);

            $sql = "INSERT INTO usuarios (cedula, nombres, apellidos, correo, contrasena_hash)
                    VALUES (:cedula, :nombres, :apellidos, :correo, :hash)";
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula'    => $datos['cedula'] ?: null,
                ':nombres'   => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':correo'    => $datos['correo'],
                ':hash'      => $hash
            ]);

            $idUsuario = (int)$conex->lastInsertId();

            if (!empty($datos['roles'])) {
                $sqlRole = "INSERT INTO usuario_roles (id_usuario, id_rol) VALUES (:usr, :rol)";
                $stmtRole = $conex->prepare($sqlRole);
                foreach ($datos['roles'] as $idRol) {
                    $stmtRole->execute([':usr' => $idUsuario, ':rol' => (int)$idRol]);
                }
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("ERROR crearUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarUsuario(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "UPDATE usuarios SET cedula = :cedula, nombres = :nombres,
                    apellidos = :apellidos, correo = :correo
                    WHERE id_usuario = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula'    => $datos['cedula'] ?: null,
                ':nombres'   => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':correo'    => $datos['correo'],
                ':id'        => (int)$datos['id_usuario']
            ]);

            $this->sincronizarRoles($conex, (int)$datos['id_usuario'], $datos['roles'] ?? []);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("ERROR actualizarUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function toggleActivo(int $id, bool $estado): bool {
        $conex = $this->getConex1();
        $sql = "UPDATE usuarios SET activo = :estado WHERE id_usuario = :id";
        $stmt = $conex->prepare($sql);
        return $stmt->execute([':estado' => (int)$estado, ':id' => $id]);
    }

    public function resetearContrasena(int $id, string $nuevaPass): bool {
        $conex = $this->getConex1();
        $hash = password_hash($nuevaPass, PASSWORD_BCRYPT, ['cost' => 10]);
        $sql = "UPDATE usuarios SET contrasena_hash = :hash, intentos_fallidos = 0,
                bloqueado_hasta = NULL WHERE id_usuario = :id";
        $stmt = $conex->prepare($sql);
        return $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    private function sincronizarRoles(PDO $conex, int $idUsuario, array $rolesIds): void {
        $conex->prepare("DELETE FROM usuario_roles WHERE id_usuario = :id")
              ->execute([':id' => $idUsuario]);

        if (!empty($rolesIds)) {
            $stmt = $conex->prepare("INSERT INTO usuario_roles (id_usuario, id_rol) VALUES (:usr, :rol)");
            foreach ($rolesIds as $idRol) {
                $stmt->execute([':usr' => $idUsuario, ':rol' => (int)$idRol]);
            }
        }
    }

    public function eliminarUsuario(int $id): bool {
        $conex = $this->getConex1();
        $this->resetearErrores();

        if ($id <= 0) {
            $this->agregarError('id_usuario', 'ID de usuario no valido.');
            return false;
        }

        $stmt = $conex->prepare("SELECT COUNT(*) FROM metas_competitivas WHERE id_usuario_creador = :id");
        $stmt->execute([':id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->agregarError('id_usuario', 'No se puede eliminar un usuario que ha creado registros de metas competitivas.');
            return false;
        }

        try {
            $conex->beginTransaction();
            $conex->prepare("DELETE FROM usuario_roles WHERE id_usuario = :id")->execute([':id' => $id]);
            $conex->prepare("DELETE FROM usuarios WHERE id_usuario = :id")->execute([':id' => $id]);
            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("ERROR eliminarUsuario: " . $e->getMessage());
            return false;
        }
    }


    // =====================================================================
    // INYECCIÓN SGRD: MANEJO DE PREFERENCIAS (JSON)
    // =====================================================================
    
  /**
     * MÉTODO PÚBLICO: Orquestador sin parámetros (Lee del estado interno).
     */
    public function guardarPreferencia(): bool {
        // Leemos todo del arreglo encapsulado $this->datos
        $idUsuario = (int) $this->getCampo('id_usuario');
        $clave = $this->getCampo('clave');
        $valor = $this->getCampo('valor');

        if ($idUsuario <= 0 || empty($clave) || empty($valor)) {
            return false;
        }
        
        return $this->actualizarJsonPreferencias();
    }

    /**
     * MÉTODO PRIVADO: Transacción SQL limpia y blindada.
     */
    private function actualizarJsonPreferencias(): bool {
        $idUsuario = (int) $this->getCampo('id_usuario');
        $clave = $this->getCampo('clave');
        $valor = $this->getCampo('valor');

        $clavesPermitidas = ['tema', 'crono_mode'];
        
        if (!in_array($clave, $clavesPermitidas)) {
            error_log("SGRD Seguridad: Intento de guardar preferencia no permitida -> " . $clave);
            return false;
        }

        try {
            $conex = $this->getConex1();
            $path = '$.' . $clave;
            
            $sql = "UPDATE usuarios 
                    SET preferencias = JSON_SET(COALESCE(preferencias, '{}'), :path, :valor) 
                    WHERE id_usuario = :id_usuario";
            
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':path', $path, PDO::PARAM_STR);
            $stmt->bindValue(':valor', $valor, PDO::PARAM_STR);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("SGRD Error: Fallo actualizando JSON -> " . $e->getMessage());
            return false;
        }
    }


}