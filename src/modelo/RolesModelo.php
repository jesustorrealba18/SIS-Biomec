<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class RolesModelo extends Conexion {
    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_seguridad');
    }

    public function validarDatos(array $datos): array {
        $this->resetearErrores();

        $nombre = $datos['nombre'] ?? '';
        $this->requerido($nombre, 'nombre');
        $this->longitud($nombre, 'nombre', 2, 50);

        return $this->obtenerErrores();
    }

    public function listarRoles(): array {
        $conex = $this->getConex1();
        $sql = "SELECT r.id_rol, r.nombre, r.descripcion, r.activo,
                       COUNT(rp.id_permiso) AS total_permisos
                FROM roles r
                LEFT JOIN rol_permisos rp ON r.id_rol = rp.id_rol
                GROUP BY r.id_rol
                ORDER BY r.id_rol";
        $stmt = $conex->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): ?array {
        $conex = $this->getConex1();
        $sql = "SELECT * FROM roles WHERE id_rol = :id";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function crearRol(array $datos): bool {
        $conex = $this->getConex1();
        $sql = "INSERT INTO roles (nombre, descripcion) VALUES (:nombre, :desc)";
        $stmt = $conex->prepare($sql);
        return $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':desc'   => $datos['descripcion'] ?? null
        ]);
    }

    public function editarRol(array $datos): bool {
        $conex = $this->getConex1();
        $sql = "UPDATE roles SET nombre = :nombre, descripcion = :desc WHERE id_rol = :id";
        $stmt = $conex->prepare($sql);
        return $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':desc'   => $datos['descripcion'] ?? null,
            ':id'     => (int)$datos['id_rol']
        ]);
    }

    public function toggleActivo(int $id, bool $estado): bool {
        $conex = $this->getConex1();
        $sql = "UPDATE roles SET activo = :estado WHERE id_rol = :id";
        $stmt = $conex->prepare($sql);
        return $stmt->execute([':estado' => (int)$estado, ':id' => $id]);
    }

    public function obtenerPermisosRol(int $idRol): array {
        $conex = $this->getConex1();
        $sql = "SELECT id_permiso FROM rol_permisos WHERE id_rol = :id";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $idRol]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function actualizarPermisosRol(int $idRol, array $permisosIds): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $conex->prepare("DELETE FROM rol_permisos WHERE id_rol = :id")
                  ->execute([':id' => $idRol]);

            if (!empty($permisosIds)) {
                $stmt = $conex->prepare("INSERT INTO rol_permisos (id_rol, id_permiso) VALUES (:rol, :perm)");
                foreach ($permisosIds as $idPerm) {
                    $stmt->execute([':rol' => $idRol, ':perm' => (int)$idPerm]);
                }
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("ERROR actualizarPermisosRol: " . $e->getMessage());
            return false;
        }
    }

    public function listarPermisosAgrupados(): array {
        $conex = $this->getConex1();
        $sql = "SELECT id_permiso, modulo, accion, descripcion
                FROM permisos ORDER BY modulo, accion";
        $stmt = $conex->query($sql);
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $agrupados = [];
        foreach ($todos as $p) {
            $agrupados[$p['modulo']][] = $p;
        }
        return $agrupados;
    }

    public function eliminarRol(int $id): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();
            $conex->prepare("DELETE FROM rol_permisos WHERE id_rol = :id")->execute([':id' => $id]);
            $conex->prepare("DELETE FROM usuario_roles WHERE id_rol = :id")->execute([':id' => $id]);
            $conex->prepare("DELETE FROM roles WHERE id_rol = :id")->execute([':id' => $id]);
            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("ERROR eliminarRol: " . $e->getMessage());
            return false;
        }
    }
}
