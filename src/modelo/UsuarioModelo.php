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

    public function obtenerPerfilModular(int $id_usuario): ?array {
        try {
            $perfil = [
                'usuario' => null,
                'roles' => [],
                'atleta' => null,
                'representante' => null,
                'entrenador' => null
            ];

            // 1. Datos Base de Identidad (Para TODOS)
            $sqlBase = "SELECT id_usuario, cedula, nombres, apellidos, correo, fecha_creacion, activo 
                        FROM sis_seguridad.usuarios WHERE id_usuario = :id LIMIT 1";
            $stmt = $this->pdo->prepare($sqlBase);
            $stmt->bindValue(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $perfil['usuario'] = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$perfil['usuario']) return null;

            // 2. ¿Qué roles tiene en el sistema? (Puede tener varios)
            $sqlRoles = "SELECT r.nombre FROM sis_seguridad.usuario_roles ur
                         INNER JOIN sis_seguridad.roles r ON ur.id_rol = r.id_rol
                         WHERE ur.id_usuario = :id";
            $stmt = $this->pdo->prepare($sqlRoles);
            $stmt->bindValue(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $perfil['roles'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 3. Faceta: ¿Es Atleta?
            $sqlAtleta = "SELECT a.foto, a.sexo, a.telefono, a.token_asistencia, a.estado,
                                 TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) AS edad,
                                 c.nombre AS categoria, dm.grupo_sanguineo, dm.alergias, dm.numero_feveda, dm.club_procedencia
                          FROM sis_natacion.atletas a
                          LEFT JOIN sis_natacion.categorias_feveda c ON a.id_categoria = c.id_categoria
                          LEFT JOIN sis_natacion.atleta_datos_medicos dm ON a.id_atleta = dm.id_atleta
                          WHERE a.id_usuario = :id LIMIT 1";
            $stmt = $this->pdo->prepare($sqlAtleta);
            $stmt->bindValue(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $perfil['atleta'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            // 4. Faceta: ¿Es Representante?
            $sqlRep = "SELECT r.id_representante, r.telefono_principal AS telefono
                       FROM sis_natacion.representantes r WHERE r.id_usuario = :id LIMIT 1";
            $stmt = $this->pdo->prepare($sqlRep);
            $stmt->bindValue(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $rep = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rep) {
                // Buscamos a sus hijos asignados
                $sqlHijos = "SELECT a.nombres, a.apellidos, a.cedula, c.nombre as categoria
                             FROM sis_natacion.atleta_representante ar
                             INNER JOIN sis_natacion.atletas a ON ar.id_atleta = a.id_atleta
                             LEFT JOIN sis_natacion.categorias_feveda c ON a.id_categoria = c.id_categoria
                             WHERE ar.id_representante = :id_rep";
                $stmtH = $this->pdo->prepare($sqlHijos);
                $stmtH->bindValue(':id_rep', $rep['id_representante'], PDO::PARAM_INT);
                $stmtH->execute();
                $rep['hijos'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);
                $perfil['representante'] = $rep;
            }

            // 5. Faceta: ¿Es Entrenador?
            $sqlEnt = "SELECT e.id_entrenador, e.telefono, e.foto
                       FROM sis_natacion.entrenador e WHERE e.id_usuario = :id LIMIT 1";
            $stmt = $this->pdo->prepare($sqlEnt);
            $stmt->bindValue(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $ent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ent) {
                // Buscamos sus grupos asignados
                $sqlGrupos = "SELECT nombre, descripcion FROM sis_natacion.grupos_entrenamiento WHERE id_entrenador = :id_ent AND activo = 1";
                $stmtG = $this->pdo->prepare($sqlGrupos);
                $stmtG->bindValue(':id_ent', $ent['id_entrenador'], PDO::PARAM_INT);
                $stmtG->execute();
                $ent['grupos'] = $stmtG->fetchAll(PDO::FETCH_ASSOC);
                $perfil['entrenador'] = $ent;
            }

            return $perfil;
        } catch (PDOException $e) {
            error_log("Error Perfil Modular: " . $e->getMessage());
            return null;
        }
    }


}