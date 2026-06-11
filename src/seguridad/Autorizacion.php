<?php

namespace GrupoProyecto\SisBiomec\seguridad;

use GrupoProyecto\SisBiomec\modelo\Conexion;
use PDO;

class Autorizacion {

    private static array $mapaRutas = [
        'inicio'        => null,
        'salir'         => null,
        'entrenador'    => ['atletas', 'gestionar'],
        'drills'        => ['drills', 'ver'],
        'atleta'        => ['atletas', 'ver'],
        'eventos'       => ['eventos', 'ver'],
        'marcas'        => ['marcas', 'ver'],
        'periodizacion' => ['periodizacion', 'ver'],
        'antropometria' => ['antropometria', 'ver'],
        'representante' => ['representantes', 'ver'],
        'calendario'    => ['eventos', 'ver'],
        'lesion'        => ['lesiones', 'ver'],
        'bitacora'      => ['seguridad', 'bitacora'],
        'usuarios'      => ['seguridad', 'usuarios'],
        'roles'         => ['seguridad', 'roles'],
        'mantenimiento' => ['seguridad', 'mantenimiento'],
        'categorias'    => ['atletas', 'gestionar'],
        'cargaBienestar'=> ['rpe', 'ver'],
    ];

    public static function cargarPermisos(int $idUsuario): bool {
        try {
            $instancia = new Conexion($_ENV['DB_NAME_SEGURIDAD'] ?? 'sis_seguridad');
            $conex = $instancia->getConex1();

            $sql = "SELECT p.modulo, p.accion
                    FROM usuario_roles ur
                    JOIN rol_permisos rp ON ur.id_rol = rp.id_rol
                    JOIN permisos p ON rp.id_permiso = p.id_permiso
                    WHERE ur.id_usuario = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $idUsuario]);

            $permisos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permisos[] = $row['modulo'] . '.' . $row['accion'];
            }

            $_SESSION['permisos'] = array_unique($permisos);
            return true;
        } catch (\Throwable $e) {
            error_log("ERROR RBAC - Fallo al cargar permisos: " . $e->getMessage());
            $_SESSION['permisos'] = [];
            return false;
        }
    }

    public static function verificar(string $modulo, string $accion): bool {
        if (!isset($_SESSION['permisos']) || empty($_SESSION['permisos'])) {
            return false;
        }
        return in_array($modulo . '.' . $accion, $_SESSION['permisos'], true);
    }

    public static function tieneRol(string $rol): bool {
        $roles = $_SESSION['rol'] ?? '';
        $rolesArray = array_map('trim', explode(',', $roles));
        return in_array($rol, $rolesArray, true);
    }

    public static function tieneAcceso(string $pagina): bool {
        if (!array_key_exists($pagina, self::$mapaRutas)) {
            return false;
        }
        if (self::$mapaRutas[$pagina] === null) {
            return true;
        }
        return self::verificar(self::$mapaRutas[$pagina][0], self::$mapaRutas[$pagina][1]);
    }

    public static function exigir(string $modulo, string $accion): void {
        if (!self::verificar($modulo, $accion)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Sin permisos para esta accion.']);
            exit;
        }
    }
}
