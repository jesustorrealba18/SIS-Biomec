<?php
namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Notificacion extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    private array $datos = [];
    private array $camposPermitidos = ['id_notificacion', 'id_usuario'];
    
    public function __construct() {
        parent::__construct('sis_seguridad'); 
    }

    public function setDatos(array $datos): self {
        foreach ($datos as $clave => $valor) {
            if (in_array($clave, $this->camposPermitidos)) {
                $this->datos[$clave] = is_string($valor) ? trim($valor) : $valor;
            }
        }
        return $this;
    }

    private function ValidacionBackend(): bool {
        $this->resetearErrores();

        $id_notif = $this->datos['id_notificacion'] ?? '';
        $id_user = $this->datos['id_usuario'] ?? '';

        if (!$this->requerido((string)$id_notif, 'ID Notificación') || 
            !$this->soloNumeros((string)$id_notif, 'ID Notificación')) {
            return false;
        }

        if (!$this->requerido((string)$id_user, 'Usuario') || 
            !$this->soloNumeros((string)$id_user, 'Usuario')) {
            return false;
        }

        $sqlCheck = "SELECT leida FROM notificaciones WHERE id_notificacion = :id_notificacion AND id_usuario = :id_usuario";
        $stmtCheck = $this->getConex1()->prepare($sqlCheck);
        $stmtCheck->execute([
            ':id_notificacion' => (int)$id_notif, 
            ':id_usuario' => (int)$id_user
        ]);
        
        $notificacion = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

        if (!$notificacion) {
            $this->agregarError('Seguridad', 'La notificación no existe o no te pertenece.');
            return false;
        }

        if (isset($notificacion['leida']) && $notificacion['leida'] == 1) {
            return true;
        }

        return empty($this->obtenerErrores());
    } 

    public function marcarcomoLeida(): bool {
        if (!$this->ValidacionBackend()) {
            return false; 
        }
        return $this->marcarLeida();
    }

    private function marcarLeida(): bool {
        try {
            $id_notif = $this->datos['id_notificacion'] ?? '';
            
            $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id_notificacion";
            $stmt = $this->getConex1()->prepare($sql);
            return $stmt->execute([':id_notificacion' => (int)$id_notif]);

        } catch (\Throwable $e) {
            $this->agregarError('Base de Datos', 'Ocurrió un error interno al actualizar.');
            return false;
        }
    }

    public static function enviar(int $id_usuario, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo', ?string $enlace_url = null): bool {
        try {
            $instNoti = new self();
            $sql = "INSERT INTO notificaciones (id_usuario, titulo, mensaje, icono, color, enlace_url) 
                    VALUES (:id_usuario, :titulo, :mensaje, :icono, :color, :enlace_url)";
            $stmt = $instNoti->getConex1()->prepare($sql);
            return $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':titulo' => $titulo,
                ':mensaje' => $mensaje,
                ':icono' => $icono,
                ':color' => $color,
                ':enlace_url' => $enlace_url
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ============================================================
    // MÉTODOS AUXILIARES - OPCIÓN 3: Buscar por CORREO
    // ============================================================

    public static function obtenerIdUsuarioPorCorreo(?string $correo): ?int {
        if (empty($correo)) {
            return null;
        }
        
        try {
            $instNoti = new self();
            $sql = "SELECT id_usuario FROM usuarios WHERE correo = :correo AND activo = 1";
            $stmt = $instNoti->getConex1()->prepare($sql);
            $stmt->execute([':correo' => trim($correo)]);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $resultado ? (int)$resultado['id_usuario'] : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public static function obtenerCorreoEntrenador(int $id_entrenador): ?string {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            $sql = "SELECT correo FROM entrenador WHERE id_entrenador = :id_entrenador";
            $stmt = $dbNegocio->getConex1()->prepare($sql);
            $stmt->execute([':id_entrenador' => $id_entrenador]);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $resultado ? $resultado['correo'] : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public static function obtenerCorreoAtleta(int $id_atleta): ?string {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            $sql = "SELECT correo FROM atletas WHERE id_atleta = :id_atleta AND estado = 1";
            $stmt = $dbNegocio->getConex1()->prepare($sql);
            $stmt->execute([':id_atleta' => $id_atleta]);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $resultado ? $resultado['correo'] : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public static function obtenerIdUsuarioEntrenador(int $id_entrenador): ?int {
        $correo = self::obtenerCorreoEntrenador($id_entrenador);
        return $correo ? self::obtenerIdUsuarioPorCorreo($correo) : null;
    }

    public static function obtenerIdUsuarioAtleta(int $id_atleta): ?int {
        $correo = self::obtenerCorreoAtleta($id_atleta);
        return $correo ? self::obtenerIdUsuarioPorCorreo($correo) : null;
    }

    // ============================================================
    // MÉTODOS DE NOTIFICACIONES EXISTENTES
    // ============================================================

    public static function notificarAtletaYRepresentante(int $id_atleta, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo', ?string $enlace_url = null): void {
        try {
            $dbNegocio = new Conexion('sis_natacion'); 

            $sqlAtleta = "SELECT correo, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad 
                          FROM atletas 
                          WHERE id_atleta = :id_atleta";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtleta);
            $stmtA->execute([':id_atleta' => $id_atleta]);
            $userAtleta = $stmtA->fetch(\PDO::FETCH_ASSOC);

            if ($userAtleta) {
                $id_usuario_atleta = self::obtenerIdUsuarioPorCorreo($userAtleta['correo']);
                if ($id_usuario_atleta) {
                    self::enviar($id_usuario_atleta, $titulo, $mensaje, $icono, $color, $enlace_url);
                }

                if ($userAtleta['edad'] < 18) {
                    $sqlRep = "SELECT r.correo 
                               FROM representantes r 
                               INNER JOIN atleta_representante ar ON r.id_representante = ar.id_representante 
                               WHERE ar.id_atleta = :id_atleta AND r.estado = 'Activo'";
                    $stmtR = $dbNegocio->getConex1()->prepare($sqlRep);
                    $stmtR->execute([':id_atleta' => $id_atleta]);
                    $representantes = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($representantes as $rep) {
                        $id_usuario_rep = self::obtenerIdUsuarioPorCorreo($rep['correo']);
                        if ($id_usuario_rep) {
                            self::enviar($id_usuario_rep, "Atleta a tu cargo: " . $titulo, $mensaje, $icono, $color, $enlace_url);
                        }
                    }
                }
            }

        } catch (\PDOException $e) {
        }
    }

    public static function notificarEntrenador(int $id_atleta, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo', ?string $enlace_url = null): void {
        try {
            $dbNegocio = new Conexion('sis_natacion'); 
            
            $sql = "SELECT e.correo 
                    FROM atletas a
                    INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                    INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo 
                    INNER JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                    WHERE a.id_atleta = :id_atleta";
            
            $stmt = $dbNegocio->getConex1()->prepare($sql);
            $stmt->execute([':id_atleta' => $id_atleta]);
            $entrenador = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($entrenador && !empty($entrenador['correo'])) {
                $id_usuario_entrenador = self::obtenerIdUsuarioPorCorreo($entrenador['correo']);
                if ($id_usuario_entrenador) {
                    self::enviar($id_usuario_entrenador, "Atleta de tu grupo: " . $titulo, $mensaje, $icono, $color, $enlace_url);
                }
            }
        } catch (\PDOException $e) {
        }
    }

    public static function notificarStaffMedicoYAdmin(string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo', ?string $enlace_url = null): void {
        try {
            $instNoti = new self();
            
            $sql = "SELECT DISTINCT id_usuario 
                    FROM usuario_roles 
                    WHERE id_rol IN (1, 3)";
            $stmt = $instNoti->getConex1()->query($sql);
            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($usuarios as $user) {
                self::enviar((int)$user['id_usuario'], "Clínica: " . $titulo, $mensaje, $icono, $color, $enlace_url);
            }
        } catch (\PDOException $e) {
        }
    }

    public static function NotificarAlertaBiologica(int $id_atleta, string $mensaje_alerta, string $modulo = 'Antropometría', ?string $enlace = null): void {
        try {
            $titulo = "⚠️ ALERTA CLÍNICA: " . $modulo;
            $icono = "fa-exclamation-triangle";
            $color = "red";

            self::notificarStaffMedicoYAdmin($titulo, $mensaje_alerta, $icono, $color, $enlace);
            self::notificarEntrenador($id_atleta, $titulo, $mensaje_alerta, $icono, $color, $enlace);
        } catch (\Throwable $th) {
        }
    }

    // ============================================================
    // NOTIFICACIONES DEL MÓDULO DE GRUPOS
    // ============================================================

    public static function notificarAsignacionGrupo(int $id_grupo, array $id_atletas, string $accion = 'ASIGNAR'): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sqlGrupo = "SELECT g.nombre, g.descripcion, 
                                CONCAT(e.nombres, ' ', e.apellidos) as entrenador_nombre,
                                e.correo as entrenador_correo,
                                e.id_entrenador
                         FROM grupos_entrenamiento g
                         LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                         WHERE g.id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);

            if (!$grupo) {
                return;
            }

            $nombreGrupo = $grupo['nombre'] ?? 'Grupo de entrenamiento';

            if (!empty($grupo['entrenador_correo'])) {
                $id_usuario_entrenador = self::obtenerIdUsuarioPorCorreo($grupo['entrenador_correo']);
                
                if ($id_usuario_entrenador) {
                    $tituloEntrenador = match($accion) {
                        'ASIGNAR' => "Nuevos atletas asignados a tu grupo",
                        'DESASIGNAR' => "Atletas removidos de tu grupo",
                        'CREAR' => "Nuevo grupo de entrenamiento creado",
                        default => "Actualización de tu grupo de entrenamiento"
                    };
                    
                    $cantidad = count($id_atletas);
                    $mensajeEntrenador = match($accion) {
                        'ASIGNAR' => "Se han asignado {$cantidad} atleta(s) a tu grupo \"{$nombreGrupo}\". Revisa tu planilla de entrenamiento.",
                        'DESASIGNAR' => "Se han removido {$cantidad} atleta(s) del grupo \"{$nombreGrupo}\".",
                        'CREAR' => "Se ha creado el grupo \"{$nombreGrupo}\". Ya puedes comenzar a asignar atletas.",
                        default => "Actualización en el grupo \"{$nombreGrupo}\"."
                    };
                    
                    self::enviar(
                        $id_usuario_entrenador,
                        $tituloEntrenador,
                        $mensajeEntrenador,
                        'fa-users-cog',
                        'indigo',
                        "?p=grupo&accion=ver&id={$id_grupo}"
                    );
                }
            }

            if ($accion === 'ASIGNAR' || $accion === 'CREAR') {
                foreach ($id_atletas as $id_atleta) {
                    $correo_atleta = self::obtenerCorreoAtleta($id_atleta);
                    $id_usuario_atleta = $correo_atleta ? self::obtenerIdUsuarioPorCorreo($correo_atleta) : null;

                    if ($id_usuario_atleta) {
                        $tituloAtleta = match($accion) {
                            'ASIGNAR' => "¡Asignado a un nuevo grupo!",
                            'CREAR' => "Nuevo grupo de entrenamiento",
                            default => "Actualización de tu grupo"
                        };
                        
                        $mensajeAtleta = match($accion) {
                            'ASIGNAR' => "Has sido asignado al grupo \"{$nombreGrupo}\". Prepárate para los próximos entrenamientos.",
                            'CREAR' => "Se ha creado el grupo \"{$nombreGrupo}\". Tu entrenador te asignará próximamente.",
                            default => "Hay novedades en el grupo \"{$nombreGrupo}\"."
                        };

                        self::enviar(
                            $id_usuario_atleta,
                            $tituloAtleta,
                            $mensajeAtleta,
                            'fa-swimmer',
                            'emerald',
                            "?p=grupo&accion=ver&id={$id_grupo}"
                        );
                    }

                    self::notificarRepresentanteGrupo($id_atleta, $nombreGrupo, $accion);
                }
            }

        } catch (\PDOException $e) {
        } catch (\Throwable $e) {
        }
    }

    private static function notificarRepresentanteGrupo(int $id_atleta, string $nombreGrupo, string $accion): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sqlEdad = "SELECT TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad 
                        FROM atletas WHERE id_atleta = :id_atleta";
            $stmtE = $dbNegocio->getConex1()->prepare($sqlEdad);
            $stmtE->execute([':id_atleta' => $id_atleta]);
            $edad = $stmtE->fetchColumn();

            if ($edad < 18) {
                $sqlRep = "SELECT r.correo, r.nombres, r.apellidos
                           FROM representantes r 
                           INNER JOIN atleta_representante ar ON r.id_representante = ar.id_representante 
                           WHERE ar.id_atleta = :id_atleta AND r.estado = 'Activo'";
                $stmtR = $dbNegocio->getConex1()->prepare($sqlRep);
                $stmtR->execute([':id_atleta' => $id_atleta]);
                $representantes = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($representantes as $rep) {
                    $id_usuario_rep = self::obtenerIdUsuarioPorCorreo($rep['correo']);
                    if ($id_usuario_rep) {
                        self::enviar(
                            $id_usuario_rep,
                            "Atleta asignado a grupo: {$nombreGrupo}",
                            "Tu representado ha sido asignado al grupo de entrenamiento \"{$nombreGrupo}\".",
                            'fa-user-graduate',
                            'amber',
                            "?p=grupo&accion=ver"
                        );
                    }
                }
            }
        } catch (\PDOException $e) {
        }
    }

    public static function notificarGrupoArchivado(int $id_grupo, string $nombreGrupo): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sqlAtletas = "SELECT a.id_atleta, a.correo, CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmt = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmt->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($atletas as $atleta) {
                $id_usuario_atleta = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario_atleta) {
                    self::enviar(
                        $id_usuario_atleta,
                        "Grupo de entrenamiento desactivado",
                        "El grupo \"{$nombreGrupo}\" ha sido desactivado. Pronto serás reasignado a un nuevo grupo. Contacta a tu entrenador para más información.",
                        'fa-archive',
                        'gray',
                        "?p=grupo"
                    );
                }
            }

            $sqlEntrenador = "SELECT e.correo, CONCAT(e.nombres, ' ', e.apellidos) as nombre
                              FROM entrenador e
                              INNER JOIN grupos_entrenamiento g ON g.id_entrenador = e.id_entrenador
                              WHERE g.id_grupo = :id_grupo";
            $stmtEnt = $dbNegocio->getConex1()->prepare($sqlEntrenador);
            $stmtEnt->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmtEnt->fetch(\PDO::FETCH_ASSOC);

            if ($entrenador && !empty($entrenador['correo'])) {
                $id_usuario_entrenador = self::obtenerIdUsuarioPorCorreo($entrenador['correo']);
                if ($id_usuario_entrenador) {
                    self::enviar(
                        $id_usuario_entrenador,
                        "Grupo desactivado",
                        "Tu grupo \"{$nombreGrupo}\" ha sido desactivado. Los atletas han sido notificados y serán reasignados.",
                        'fa-archive',
                        'orange',
                        "?p=grupo"
                    );
                }
            }

        } catch (\PDOException $e) {
        }
    }

    public static function notificarSesionCreada(int $id_sesion, int $id_grupo, string $fecha, string $tipo_sesion): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sqlGrupo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);
            $nombreGrupo = $grupo['nombre'] ?? 'tu grupo';

            $sqlAtletas = "SELECT a.correo, a.id_atleta
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmtA->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmtA->fetchAll(\PDO::FETCH_ASSOC);
            
            $fecha_formateada = date('d/m/Y', strtotime($fecha));
            $tipos = [
                'Tecnica' => 'Técnica',
                'Resistencia' => 'Resistencia',
                'Velocidad' => 'Velocidad',
                'Recuperacion' => 'Recuperación',
                'Fuerza' => 'Fuerza',
                'Flexibilidad' => 'Flexibilidad'
            ];
            $tipoLabel = $tipos[$tipo_sesion] ?? $tipo_sesion;
            
            foreach ($atletas as $atleta) {
                $id_usuario = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario) {
                    self::enviar(
                        $id_usuario,
                        "Nueva sesión planificada",
                        "Se ha planificado una sesión de {$tipoLabel} para el grupo \"{$nombreGrupo}\" el día {$fecha_formateada}. Prepárate para el entrenamiento.",
                        'fa-calendar-plus',
                        'emerald',
                        "?p=sesiones&accion=ver&id={$id_sesion}"
                    );
                }
            }

            $sqlEntrenador = "SELECT e.correo 
                              FROM entrenador e
                              INNER JOIN grupos_entrenamiento g ON g.id_entrenador = e.id_entrenador
                              WHERE g.id_grupo = :id_grupo";
            $stmtE = $dbNegocio->getConex1()->prepare($sqlEntrenador);
            $stmtE->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmtE->fetch(\PDO::FETCH_ASSOC);
            
            if ($entrenador && !empty($entrenador['correo'])) {
                $id_usuario_ent = self::obtenerIdUsuarioPorCorreo($entrenador['correo']);
                if ($id_usuario_ent) {
                    self::enviar(
                        $id_usuario_ent,
                        "Sesión creada exitosamente",
                        "Has creado una sesión de {$tipoLabel} para el grupo \"{$nombreGrupo}\" el día {$fecha_formateada}. Los atletas han sido notificados.",
                        'fa-check-circle',
                        'indigo',
                        "?p=sesiones&accion=ver&id={$id_sesion}"
                    );
                }
            }
            
        } catch (\PDOException $e) {
        }
    }

    public static function notificarSesionIniciada(int $id_sesion, int $id_grupo): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');

            $sqlGrupo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);
            $nombreGrupo = $grupo['nombre'] ?? 'tu grupo';

            $sqlAtletas = "SELECT a.correo
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmtA->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmtA->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($atletas as $atleta) {
                $id_usuario = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario) {
                    self::enviar(
                        $id_usuario,
                        "¡La sesión ha comenzado!",
                        "La sesión de entrenamiento para el grupo \"{$nombreGrupo}\" ha comenzado. ¡A darle con todo!",
                        'fa-play-circle',
                        'cyan',
                        "?p=sesiones&accion=ver&id={$id_sesion}"
                    );
                }
            }
            
        } catch (\PDOException $e) {
        }
    }

    public static function notificarSesionCompletada(int $id_sesion, int $id_grupo, int $volumen_ejecutado): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sqlGrupo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);
            $nombreGrupo = $grupo['nombre'] ?? 'tu grupo';
            
            $sqlAtletas = "SELECT a.correo
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmtA->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmtA->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($atletas as $atleta) {
                $id_usuario = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario) {
                    self::enviar(
                        $id_usuario,
                        "Sesión completada",
                        "La sesión del grupo \"{$nombreGrupo}\" ha finalizado. Volumen total: {$volumen_ejecutado}m. ¡Buen trabajo!",
                        'fa-flag-checkered',
                        'emerald',
                        "?p=sesiones&accion=ver&id={$id_sesion}"
                    );
                }
            }
            
            $sqlEntrenador = "SELECT e.correo 
                              FROM entrenador e
                              INNER JOIN grupos_entrenamiento g ON g.id_entrenador = e.id_entrenador
                              WHERE g.id_grupo = :id_grupo";
            $stmtE = $dbNegocio->getConex1()->prepare($sqlEntrenador);
            $stmtE->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmtE->fetch(\PDO::FETCH_ASSOC);
            
            if ($entrenador && !empty($entrenador['correo'])) {
                $id_usuario_ent = self::obtenerIdUsuarioPorCorreo($entrenador['correo']);
                if ($id_usuario_ent) {
                    self::enviar(
                        $id_usuario_ent,
                        "Sesión completada exitosamente",
                        "Has completado la sesión del grupo \"{$nombreGrupo}\". Volumen ejecutado: {$volumen_ejecutado}m. Los atletas han sido notificados.",
                        'fa-check-double',
                        'indigo',
                        "?p=sesiones&accion=ver&id={$id_sesion}"
                    );
                }
            }
            
        } catch (\PDOException $e) {
        }
    }

    public static function notificarSesionCancelada(int $id_sesion, int $id_grupo, string $fecha): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sqlGrupo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);
            $nombreGrupo = $grupo['nombre'] ?? 'tu grupo';
            
            $fecha_formateada = date('d/m/Y', strtotime($fecha));
            
            $sqlAtletas = "SELECT a.correo
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmtA->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmtA->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($atletas as $atleta) {
                $id_usuario = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario) {
                    self::enviar(
                        $id_usuario,
                        "Sesión cancelada",
                        "La sesión del grupo \"{$nombreGrupo}\" programada para el {$fecha_formateada} ha sido cancelada. Estaremos atentos a la reprogramación.",
                        'fa-times-circle',
                        'red',
                        "?p=sesiones"
                    );
                }
            }
            
        } catch (\PDOException $e) {
        }
    }

    public static function notificarSesionEditada(int $id_sesion, int $id_grupo, string $fecha, string $tipo_sesion): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sqlGrupo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);
            $nombreGrupo = $grupo['nombre'] ?? 'tu grupo';
            
            $fecha_formateada = date('d/m/Y', strtotime($fecha));
            $tipos = [
                'Tecnica' => 'Técnica',
                'Resistencia' => 'Resistencia',
                'Velocidad' => 'Velocidad',
                'Recuperacion' => 'Recuperación',
                'Fuerza' => 'Fuerza',
                'Flexibilidad' => 'Flexibilidad',
                'Competencia' => 'Competencia'
            ];
            $tipoLabel = $tipos[$tipo_sesion] ?? $tipo_sesion;
            
            $sqlAtletas = "SELECT a.correo
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmtA->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmtA->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($atletas as $atleta) {
                $id_usuario = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario) {
                    self::enviar(
                        $id_usuario,
                        "Sesión actualizada",
                        "La sesión de {$tipoLabel} para el grupo \"{$nombreGrupo}\" del día {$fecha_formateada} ha sido modificada. Revisa los detalles.",
                        'fa-edit',
                        'amber',
                        "?p=sesiones&accion=ver&id={$id_sesion}"
                    );
                }
            }
            
        } catch (\PDOException $e) {
        }
    }

    public static function notificarAsignacionCarrilCreada(int $id_asignacion, int $id_grupo, int $carril_numero, string $dia_semana, string $hora_inicio, string $hora_fin, string $fecha_inicio): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sqlGrupo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);
            $nombreGrupo = $grupo['nombre'] ?? 'tu grupo';
            
            $sqlAtletas = "SELECT a.correo, a.id_atleta
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmtA->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmtA->fetchAll(\PDO::FETCH_ASSOC);
            
            $fecha_formateada = date('d/m/Y', strtotime($fecha_inicio));
            $hora_inicio_formateada = date('h:i A', strtotime($hora_inicio));
            $hora_fin_formateada = date('h:i A', strtotime($hora_fin));
            
            $titulo = "Asignación de Carril";
            $mensaje = "Tu grupo \"{$nombreGrupo}\" ha sido asignado al Carril {$carril_numero} los {$dia_semana} de {$hora_inicio_formateada} a {$hora_fin_formateada} (vigente desde {$fecha_formateada}).";
            
            foreach ($atletas as $atleta) {
                $id_usuario = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario) {
                    self::enviar(
                        $id_usuario,
                        $titulo,
                        $mensaje,
                        'fa-bell',
                        'emerald',
                        "?p=asignacion"
                    );
                }
            }

            $sqlEntrenador = "SELECT e.correo 
                              FROM entrenador e
                              INNER JOIN grupos_entrenamiento g ON g.id_entrenador = e.id_entrenador
                              WHERE g.id_grupo = :id_grupo";
            $stmtE = $dbNegocio->getConex1()->prepare($sqlEntrenador);
            $stmtE->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmtE->fetch(\PDO::FETCH_ASSOC);
            
            if ($entrenador && !empty($entrenador['correo'])) {
                $id_usuario_ent = self::obtenerIdUsuarioPorCorreo($entrenador['correo']);
                if ($id_usuario_ent) {
                    self::enviar(
                        $id_usuario_ent,
                        "Nueva Asignación de Carril",
                        "Tu grupo \"{$nombreGrupo}\" ha sido asignado al Carril {$carril_numero} los {$dia_semana} de {$hora_inicio_formateada} a {$hora_fin_formateada}. Los atletas han sido notificados.",
                        'fa-bell',
                        'purple',
                        "?p=asignacion"
                    );
                }
            }
            
        } catch (\PDOException $e) {
        }
    }


    public static function notificarAsignacionCarrilFinalizada(int $id_grupo, int $carril_numero): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');

            $sqlGrupo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);
            $nombreGrupo = $grupo['nombre'] ?? 'tu grupo';

            $sqlAtletas = "SELECT a.correo
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmtA->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmtA->fetchAll(\PDO::FETCH_ASSOC);
            
            $titulo = "Asignación Finalizada";
            $mensaje = "La asignación del Carril {$carril_numero} para el grupo \"{$nombreGrupo}\" ha finalizado.";
            
            foreach ($atletas as $atleta) {
                $id_usuario = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario) {
                    self::enviar(
                        $id_usuario,
                        $titulo,
                        $mensaje,
                        'fa-flag-checkered',
                        'amber',
                        "?p=asignacion"
                    );
                }
            }

            $sqlEntrenador = "SELECT e.correo 
                              FROM entrenador e
                              INNER JOIN grupos_entrenamiento g ON g.id_entrenador = e.id_entrenador
                              WHERE g.id_grupo = :id_grupo";
            $stmtE = $dbNegocio->getConex1()->prepare($sqlEntrenador);
            $stmtE->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmtE->fetch(\PDO::FETCH_ASSOC);
            
            if ($entrenador && !empty($entrenador['correo'])) {
                $id_usuario_ent = self::obtenerIdUsuarioPorCorreo($entrenador['correo']);
                if ($id_usuario_ent) {
                    self::enviar(
                        $id_usuario_ent,
                        "Fin de Asignación",
                        "La asignación del Carril {$carril_numero} para tu grupo \"{$nombreGrupo}\" ha finalizado.",
                        'fa-flag-checkered',
                        'purple',
                        "?p=asignacion"
                    );
                }
            }
            
        } catch (\PDOException $e) {
        }
    }

    public static function notificarAsignacionCarrilEditada(int $id_asignacion, int $id_grupo, int $carril_numero, string $dia_semana, string $hora_inicio, string $hora_fin): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
 
            $sqlGrupo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);
            $nombreGrupo = $grupo['nombre'] ?? 'tu grupo';
            
            $sqlAtletas = "SELECT a.correo
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmtA->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmtA->fetchAll(\PDO::FETCH_ASSOC);
            
            $hora_inicio_formateada = date('h:i A', strtotime($hora_inicio));
            $hora_fin_formateada = date('h:i A', strtotime($hora_fin));
            
            $titulo = "Asignación de Carril Actualizada";
            $mensaje = "La asignación del Carril {$carril_numero} para el grupo \"{$nombreGrupo}\" ha sido modificada. Nuevo horario: {$dia_semana} de {$hora_inicio_formateada} a {$hora_fin_formateada}.";
            
            foreach ($atletas as $atleta) {
                $id_usuario = self::obtenerIdUsuarioPorCorreo($atleta['correo']);
                if ($id_usuario) {
                    self::enviar(
                        $id_usuario,
                        $titulo,
                        $mensaje,
                        'fa-edit',
                        'amber',
                        "?p=asignacion"
                    );
                }
            }
            
            $sqlEntrenador = "SELECT e.correo 
                              FROM entrenador e
                              INNER JOIN grupos_entrenamiento g ON g.id_entrenador = e.id_entrenador
                              WHERE g.id_grupo = :id_grupo";
            $stmtE = $dbNegocio->getConex1()->prepare($sqlEntrenador);
            $stmtE->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmtE->fetch(\PDO::FETCH_ASSOC);
            
            if ($entrenador && !empty($entrenador['correo'])) {
                $id_usuario_ent = self::obtenerIdUsuarioPorCorreo($entrenador['correo']);
                if ($id_usuario_ent) {
                    self::enviar(
                        $id_usuario_ent,
                        "Asignación de Carril Actualizada",
                        "La asignación del Carril {$carril_numero} para tu grupo \"{$nombreGrupo}\" ha sido modificada. Nuevo horario: {$dia_semana} de {$hora_inicio_formateada} a {$hora_fin_formateada}.",
                        'fa-edit',
                        'purple',
                        "?p=asignacion"
                    );
                }
            }
            
        } catch (\PDOException $e) {
        }
    }

    // ============================================================
    // MÉTODOS DE LISTADO Y CONSULTA
    // ============================================================

    public static function listarPorUsuario(int $id_usuario, int $limite = 10): array {
        try {
            $instNoti = new self();
            $sql = "SELECT id_notificacion, titulo, mensaje, icono, color, leida, fecha, enlace_url 
                    FROM notificaciones 
                    WHERE id_usuario = :id_usuario 
                    ORDER BY fecha DESC LIMIT :limite";
            
            $stmt = $instNoti->getConex1()->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function contarNoLeidas(int $id_usuario): int {
        try {
            $instNoti = new self();
            $sql = "SELECT COUNT(*) FROM notificaciones WHERE id_usuario = :id_usuario AND leida = 0";
            $stmt = $instNoti->getConex1()->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // ============================================================
    // DESPACHADORES DE OTROS MÓDULOS
    // ============================================================

    public static function NotificarAtletas(string $accion, array $data, int $id_atleta): void {
        try {
            $deepLink = "?p=atleta";
            $nombres = trim(($data['nombres'] ?? '') . ' ' . ($data['apellidos'] ?? ''));
            $cedula = $data['cedula'] ?? '';

            switch ($accion) {
                case 'CREATE':
                    $titulo = "Atleta Registrado";
                    $mensaje = "Se ha registrado un nuevo atleta: {$nombres} (C.I: {$cedula}).";
                    $icono = "fa-user-plus";
                    $color = "emerald";
                    break;

                case 'UPDATE':
                    $titulo = "Datos de Atleta Actualizados";
                    $mensaje = "Se actualizaron los datos del atleta {$nombres}.";
                    $icono = "fa-user-edit";
                    $color = "amber";
                    $deepLink = "?p=atleta";
                    break;

                case 'DELETE':
                    $titulo = "Atleta Desactivado";
                    $mensaje = "El atleta {$nombres} ha sido marcado como inactivo en el sistema.";
                    $icono = "fa-user-slash";
                    $color = "red";
                    break;

                default:
                    return;
            }

            self::notificarAtletaYRepresentante($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);

        } catch (\Throwable $th) {
        }
    }

    public static function NotificarMarcas(string $accion, array $data, int $id_marca): void {
        try {
            $deepLink = "?p=marcas&h=" . $id_marca;
            $distancia = $data['distancia_m'] ?? '';
            $estilo = $data['estilo'] ?? '';
            $tiempo = $data['tiempo_final_seg'] ?? '';

            switch ($accion) {
                case 'CREATE':
                    $titulo = "¡Nueva Marca Registrada!";
                    $mensaje = "Se ha registrado un tiempo de {$tiempo}s en {$distancia}m {$estilo}.";
                    $icono = "fa-stopwatch";
                    $color = "emerald";
                    break;

                case 'UPDATE':
                    $titulo = "¡Marca Actualizada!";
                    $mensaje = "El entrenador ha actualizado los datos de tu marca en {$distancia}m {$estilo}.";
                    $icono = "fa-edit";
                    $color = "amber";
                    break;

                case 'DELETE':
                    $titulo = "Marca Desactivada";
                    $mensaje = "Se ha retirado o deshabilitado un registro de marca técnica del sistema.";
                    $icono = "fa-trash-alt";
                    $color = "red";
                    $deepLink = "?p=marcas&estado=Inactivo&h=" . $id_marca;
                    break;

                case 'RESTORE':
                    $titulo = "Marca Restaurada";
                    $mensaje = "Se ha restaurado y reactivado una marca previamente deshabilitada en el historial.";
                    $icono = "fa-history";
                    $color = "indigo";
                    break;

                default:
                    return;
            }

            self::notificarAtletaYRepresentante((int)$data['id_atleta'], $titulo, $mensaje, $icono, $color, $deepLink);

        } catch (\Throwable $th) {
        }
    }

    public static function NotificarEventos(string $accion, array $data, ?int $id_evento = null, ?int $id_atleta = null): void {
        try {
            $deepLink = "?p=eventos";
            if ($id_evento) {
                $deepLink = "?p=eventos&accion=obtenerDetalle&id=" . $id_evento;
            }

            $nombreEvento = $data['nombre'] ?? 'Evento';
            $tipo = $data['tipo'] ?? '';
            $sede = $data['sede'] ?? '';
            $nuevoEstado = $data['nuevo_estado'] ?? '';
            $estilo = $data['estilo'] ?? '';
            $distancia = $data['distancia'] ?? '';

            switch ($accion) {
                case 'CREATE':
                    $titulo = "Nuevo Evento Registrado";
                    $mensaje = "Se ha creado el evento \"{$nombreEvento}\" ({$tipo})." . ($sede ? " Sede: {$sede}." : '');
                    $icono = "fa-calendar-plus";
                    $color = "emerald";
                    break;

                case 'UPDATE':
                    $titulo = "Evento Actualizado";
                    $mensaje = "Se han actualizado los datos del evento \"{$nombreEvento}\".";
                    $icono = "fa-edit";
                    $color = "amber";
                    break;

                case 'ESTADO':
                    $titulo = "Estado de Evento Cambiado";
                    $mensaje = "El evento \"{$nombreEvento}\" cambio a estado: {$nuevoEstado}.";
                    $icono = "fa-exchange-alt";
                    $color = "indigo";
                    break;

                case 'INSCRIPCION':
                    $titulo = "Inscrito en Competencia";
                    $mensaje = "Has sido inscrito en el evento \"{$nombreEvento}\".";
                    $icono = "fa-user-check";
                    $color = "cyan";
                    break;

                case 'METAS':
                    $titulo = "Meta Competitiva Asignada";
                    $mensaje = "Se te asigno una meta en {$distancia}m {$estilo} para el evento \"{$nombreEvento}\".";
                    $icono = "fa-bullseye";
                    $color = "amber";
                    break;

                case 'QUITAR_INSCRIPCION':
                    $titulo = "Inscripcion Removida";
                    $mensaje = "Tu inscripcion al evento \"{$nombreEvento}\" ha sido eliminada.";
                    $icono = "fa-user-minus";
                    $color = "red";
                    break;

                case 'DELETE_META':
                    $titulo = "Meta Competitiva Eliminada";
                    $mensaje = "Se ha eliminado una meta competitiva del sistema.";
                    $icono = "fa-trash-alt";
                    $color = "red";
                    $deepLink = "?p=eventos";
                    break;

                default:
                    return;
            }

            if ($id_atleta && $id_atleta > 0) {
                self::notificarAtletaYRepresentante($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);
            } else {
                $id_usuario = $_SESSION['id'] ?? 0;
                if ($id_usuario > 0) {
                    self::enviar($id_usuario, $titulo, $mensaje, $icono, $color, $deepLink);
                }
            }

        } catch (\Throwable $th) {
        }
    }

    public static function NotificarObservaciones(string $accion, array $data, ?int $id_atleta = null): void {
        try {
            $deepLink = "?p=observacionesTecnicas";
            $calificacion = $data['calificacion'] ?? '';

            $labels = [1 => 'Necesita trabajo', 2 => 'Regular', 3 => 'Bueno', 4 => 'Muy bueno', 5 => 'Excelente'];
            $textoCalif = $labels[(int)$calificacion] ?? '';

            switch ($accion) {
                case 'CREATE':
                    $titulo = "Nueva Observacion Tecnica";
                    $mensaje = "Se ha registrado una observacion tecnica sobre tu rendimiento." . ($textoCalif ? " Calificacion: {$textoCalif} ({$calificacion}/5)." : '');
                    $icono = "fa-clipboard-check";
                    $color = "emerald";
                    break;

                case 'UPDATE':
                    $titulo = "Observacion Tecnica Actualizada";
                    $mensaje = "Se ha actualizado una observacion tecnica sobre tu rendimiento." . ($textoCalif ? " Calificacion: {$textoCalif} ({$calificacion}/5)." : '');
                    $icono = "fa-edit";
                    $color = "amber";
                    break;

                case 'DELETE':
                    $titulo = "Observacion Tecnica Eliminada";
                    $mensaje = "Se ha eliminado una observacion tecnica del sistema.";
                    $icono = "fa-trash-alt";
                    $color = "red";
                    break;

                default:
                    return;
            }

            if ($id_atleta && $id_atleta > 0) {
                self::notificarAtletaYRepresentante($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);
            } else {
                $id_usuario = $_SESSION['id'] ?? 0;
                if ($id_usuario > 0) {
                    self::enviar($id_usuario, $titulo, $mensaje, $icono, $color, $deepLink);
                }
            }

        } catch (\Throwable $th) {
        }
    }

    public static function NotificarPeriodizacion(string $accion, array $data, ?int $id_usuario_destino = null, ?int $id_macrociclo = null): void {
        try {
            $deepLink = "?p=periodizacion";
            if ($id_macrociclo) {
                $deepLink = "?p=periodizacion&h=" . $id_macrociclo;
            }

            $nombreMacro = $data['nombre'] ?? 'Macrociclo';
            $grupo = $data['grupo_nombre'] ?? '';
            $totalSemanas = $data['total_semanas'] ?? '';
            $nuevoEstado = $data['nuevo_estado'] ?? '';

            switch ($accion) {
                case 'CREATE':
                    $titulo = "Nuevo Macrociclo Creado";
                    $mensaje = "Se ha creado el macrociclo \"{$nombreMacro}\" para {$grupo}.";
                    $icono = "fa-project-diagram";
                    $color = "emerald";
                    break;

                case 'UPDATE':
                    $titulo = "Macrociclo Actualizado";
                    $mensaje = "Se han actualizado los datos del macrociclo \"{$nombreMacro}\".";
                    $icono = "fa-edit";
                    $color = "amber";
                    break;

                case 'GENERAR':
                    $titulo = "Plan ATR Generado";
                    $mensaje = "Se generó el plan de periodización para \"{$nombreMacro}\" ({$totalSemanas} semanas).";
                    $icono = "fa-magic";
                    $color = "cyan";
                    break;

                case 'ESTADO':
                    $titulo = "Estado de Macrociclo Cambiado";
                    $mensaje = "El macrociclo \"{$nombreMacro}\" cambió a estado: {$nuevoEstado}.";
                    $icono = "fa-exchange-alt";
                    $color = "indigo";
                    break;

                case 'DELETE_MESO':
                    $titulo = "Mesociclo Eliminado";
                    $mensaje = "Se ha eliminado un mesociclo del macrociclo \"{$nombreMacro}\".";
                    $icono = "fa-trash-alt";
                    $color = "red";
                    break;

                default:
                    return;
            }

            if ($id_usuario_destino && $id_usuario_destino > 0) {
                self::enviar($id_usuario_destino, $titulo, $mensaje, $icono, $color, $deepLink);
            }

        } catch (\Throwable $th) {
        }
    }

    public static function NotificarLesiones(string $accion, array $data, int $id_atleta, ?int $id_lesion = null): void {
        try {
            $deepLink = "?p=lesiones";
            if ($id_lesion) {
                $deepLink .= "&id=" . $id_lesion; 
            }

            $zona = $data['zona_anatomica'] ?? 'no especificada';
            $tipo = $data['tipo'] ?? 'lesión';
            $estado = $data['estado'] ?? 'Activa';

            switch ($accion) {
                case 'CREATE':
                    $titulo = "Nuevo Informe Médico Registrado";
                    $mensaje = "Se ha registrado un diagnóstico de {$tipo} en la zona: {$zona}.";
                    $icono = "fa-notes-medical";
                    $color = "emerald";
                    break;

                case 'UPDATE':
                    $titulo = "Evolución Clínica Actualizada";
                    $mensaje = "Se ha actualizado el estado de la lesión en {$zona}. Nuevo estado: {$estado}.";
                    $icono = "fa-laptop-medical";
                    $color = "amber";
                    break;

                case 'DELETE':
                    $titulo = "Informe Clínico Anulado";
                    $mensaje = "Un registro de lesión ha sido movido a la papelera.";
                    $icono = "fa-trash-alt";
                    $color = "red";
                    $deepLink = "?p=lesiones&modo=papelera";
                    break;

                case 'RESTORE':
                    $titulo = "Informe Clínico Restaurado";
                    $mensaje = "Se ha restaurado un registro clínico del historial médico.";
                    $icono = "fa-briefcase-medical";
                    $color = "indigo";
                    break;

                default:
                    return;
            }

            self::notificarAtletaYRepresentante($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);
            self::notificarEntrenador($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);
            self::notificarStaffMedicoYAdmin($titulo, $mensaje, $icono, $color, $deepLink);

        } catch (\Throwable $th) {
        }
    }

    public static function NotificarAntropometria(string $accion, array $data, int $id_atleta, ?int $id_medicion = null): void {
        try {
            $deepLink = "?p=antropometria";
            if ($id_medicion) {
                $deepLink .= "&id=" . $id_medicion; 
            }

            $peso = $data['peso_kg'] ?? $data['peso'] ?? '--';
            $talla = $data['talla_cm'] ?? $data['talla'] ?? '--';

            switch ($accion) {
                case 'CREATE':
                    $titulo = "Nueva Evaluación Antropométrica";
                    $mensaje = "Se ha registrado una nueva medición: Peso {$peso}kg, Talla {$talla}cm.";
                    $icono = "fa-weight";
                    $color = "emerald";
                    break;

                case 'UPDATE':
                    $titulo = "Evaluación Antropométrica Actualizada";
                    $mensaje = "Se han corregido los datos de la medición: Peso {$peso}kg, Talla {$talla}cm.";
                    $icono = "fa-edit";
                    $color = "amber";
                    break;

                case 'DELETE':
                    $titulo = "Medición Anulada";
                    $mensaje = "Un registro antropométrico ha sido movido a la papelera.";
                    $icono = "fa-trash-alt";
                    $color = "red";
                    $deepLink = "?p=antropometria&modo=papelera";
                    break;

                case 'RESTORE':
                    $titulo = "Medición Restaurada";
                    $mensaje = "Se ha restaurado un registro antropométrico en el historial.";
                    $icono = "fa-undo";
                    $color = "indigo";
                    break;

                default:
                    return;
            }

            self::notificarAtletaYRepresentante($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);
            self::notificarEntrenador($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);
            self::notificarStaffMedicoYAdmin($titulo, $mensaje, $icono, $color, $deepLink);

        } catch (\Throwable $th) {
        }
    }
}