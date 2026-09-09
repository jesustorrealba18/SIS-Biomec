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
            error_log("Error crítico en marcarLeida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 1. MÉTODO CORE: Guarda la notificación en sis_seguridad
     */
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
            error_log("Error Notificacion (Seguridad): " . $e->getMessage());
            return false;
        }
    }

    /**
     * 2. MÉTODO INTELIGENTE: Busca en sis_natacion y escribe en sis_seguridad
     */
    public static function notificarAtletaYRepresentante(int $id_atleta, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo', ?string $enlace_url = null): void {
        try {
            $dbNegocio = new Conexion('sis_natacion'); 

            $sqlAtleta = "SELECT id_usuario, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad 
                          FROM atletas 
                          WHERE id_atleta = :id_atleta";
            $stmtA = $dbNegocio->getConex1()->prepare($sqlAtleta);
            $stmtA->execute([':id_atleta' => $id_atleta]);
            $userAtleta = $stmtA->fetch(\PDO::FETCH_ASSOC);

            if ($userAtleta) {
                // 1. Notificar al Atleta (usando id_usuario directamente)
                if (!empty($userAtleta['id_usuario'])) {
                    self::enviar((int)$userAtleta['id_usuario'], $titulo, $mensaje, $icono, $color, $enlace_url);
                }

                // 2. REGLA DE NEGOCIO: Solo notificar al representante si el atleta es menor de 18 años
                if ($userAtleta['edad'] < 18) {
                    $sqlRep = "SELECT r.id_usuario 
                               FROM representantes r 
                               INNER JOIN atleta_representante ar ON r.id_representante = ar.id_representante 
                               WHERE ar.id_atleta = :id_atleta AND r.id_usuario IS NOT NULL";
                    $stmtR = $dbNegocio->getConex1()->prepare($sqlRep);
                    $stmtR->execute([':id_atleta' => $id_atleta]);
                    $representantes = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($representantes as $rep) {
                        self::enviar((int)$rep['id_usuario'], "Atleta a tu cargo: " . $titulo, $mensaje, $icono, $color, $enlace_url);
                    }
                }
            }

        } catch (\PDOException $e) {
            error_log("Error Routing Notificacion: " . $e->getMessage());
        }
    }

    /**
     * 3. Notificar al Entrenador del Atleta (vía Grupo de Entrenamiento)
     */
    public static function notificarEntrenador(int $id_atleta, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo', ?string $enlace_url = null): void {
        try {
            $dbNegocio = new Conexion('sis_natacion'); 
            
            $sql = "SELECT e.id_usuario 
                    FROM atletas a
                    INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                    INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo 
                    INNER JOIN entrenadores e ON g.id_entrenador = e.id_entrenador
                    WHERE a.id_atleta = :id_atleta AND e.id_usuario IS NOT NULL";
            
            $stmt = $dbNegocio->getConex1()->prepare($sql);
            $stmt->execute([':id_atleta' => $id_atleta]);
            $entrenador = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($entrenador && !empty($entrenador['id_usuario'])) {
                self::enviar((int)$entrenador['id_usuario'], "Atleta de tu grupo: " . $titulo, $mensaje, $icono, $color, $enlace_url);
            }
        } catch (\PDOException $e) {
            error_log("Error Routing Notificacion Entrenador: " . $e->getMessage());
        }
    }

    /**
     * 4. Notificar a todo el Staff Médico (rol 3) y Administradores (rol 1)
     */
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
            error_log("Error Routing Notificacion Staff: " . $e->getMessage());
        }
    }

<<<<<<< HEAD
    /**
     * DISPARADOR DE EMERGENCIAS (ALERTA BIOLÓGICA ROJA)
     */
    public static function NotificarAlertaBiologica(int $id_atleta, string $mensaje_alerta, string $modulo = 'Antropometría', ?string $enlace = null): void {
        try {
            $titulo = "⚠️ ALERTA CLÍNICA: " . $modulo;
            $icono = "fa-exclamation-triangle";
            $color = "red"; // Urgencia

            // Notificamos al Médico y Administrador
            self::notificarStaffMedicoYAdmin($titulo, $mensaje_alerta, $icono, $color, $enlace);
            
            // Notificamos al Entrenador del Atleta
            self::notificarEntrenador($id_atleta, $titulo, $mensaje_alerta, $icono, $color, $enlace);

            // Opcional: Podrías notificar al representante, pero para evitar alarmar a los padres antes de un chequeo médico, 
            // se recomienda que la alerta roja se quede en el cuerpo técnico.

        } catch (\Throwable $th) {
            error_log("Error despachando alerta biológica: " . $th->getMessage());
        }
    }

    /**
     * DESPACHADOR CENTRALIZADO PARA EL MÓDULO DE MARCAS
     */
=======
    public static function notificarAsignacionGrupo(int $id_grupo, array $id_atletas, string $accion = 'ASIGNAR'): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            $sqlGrupo = "SELECT g.nombre, g.descripcion, 
                                CONCAT(e.nombres, ' ', e.apellidos) as entrenador_nombre,
                                e.id_usuario
                         FROM grupos_entrenamiento g
                         LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                         WHERE g.id_grupo = :id_grupo";
            $stmtG = $dbNegocio->getConex1()->prepare($sqlGrupo);
            $stmtG->execute([':id_grupo' => $id_grupo]);
            $grupo = $stmtG->fetch(\PDO::FETCH_ASSOC);

            if (!$grupo) {
                error_log("Grupo no encontrado para notificación: id_grupo={$id_grupo}");
                return;
            }

            $nombreGrupo = $grupo['nombre'] ?? 'Grupo de entrenamiento';

            if (!empty($grupo['id_usuario'])) {
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
                    (int)$grupo['id_usuario'],
                    $tituloEntrenador,
                    $mensajeEntrenador,
                    'fa-users-cog',
                    'indigo',
                    "?p=grupo&accion=ver&id={$id_grupo}"
                );
            }

            if ($accion === 'ASIGNAR' || $accion === 'CREAR') {
                foreach ($id_atletas as $id_atleta) {
                    $sqlAtleta = "SELECT id_usuario FROM atletas WHERE id_atleta = :id_atleta AND estado = 1";
                    $stmtA = $dbNegocio->getConex1()->prepare($sqlAtleta);
                    $stmtA->execute([':id_atleta' => $id_atleta]);
                    $atleta = $stmtA->fetch(\PDO::FETCH_ASSOC);

                    if ($atleta && !empty($atleta['id_usuario'])) {
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
                            (int)$atleta['id_usuario'],
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
            error_log("Error Routing Notificacion Grupo: " . $e->getMessage());
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
                $sqlRep = "SELECT r.id_usuario 
                           FROM representantes r 
                           INNER JOIN atleta_representante ar ON r.id_representante = ar.id_representante 
                           WHERE ar.id_atleta = :id_atleta AND r.id_usuario IS NOT NULL";
                $stmtR = $dbNegocio->getConex1()->prepare($sqlRep);
                $stmtR->execute([':id_atleta' => $id_atleta]);
                $representantes = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($representantes as $rep) {
                    self::enviar(
                        (int)$rep['id_usuario'],
                        "Atleta asignado a grupo: {$nombreGrupo}",
                        "Tu representado ha sido asignado al grupo de entrenamiento \"{$nombreGrupo}\".",
                        'fa-user-graduate',
                        'amber',
                        "?p=grupo&accion=ver"
                    );
                }
            }
        } catch (\PDOException $e) {
            error_log("Error notificando representante de grupo: " . $e->getMessage());
        }
    }

    public static function notificarGrupoArchivado(int $id_grupo, string $nombreGrupo): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
        
            $sqlAtletas = "SELECT a.id_usuario, CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta
                           FROM atletas a
                           INNER JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.estado = 1";
            $stmt = $dbNegocio->getConex1()->prepare($sqlAtletas);
            $stmt->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($atletas)) {
                self::notificarEntrenadorGrupoArchivado($id_grupo, $nombreGrupo);
                return;
            }

            foreach ($atletas as $atleta) {
                if (!empty($atleta['id_usuario'])) {
                    self::enviar(
                        (int)$atleta['id_usuario'],
                        "Grupo de entrenamiento desactivado",
                        "El grupo \"{$nombreGrupo}\" ha sido desactivado. Pronto serás reasignado a un nuevo grupo. Contacta a tu entrenador para más información.",
                        'fa-archive',
                        'gray',
                        "?p=grupo"
                    );
                }
            }

            foreach ($atletas as $atleta) {
                $sqlEdad = "SELECT TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad 
                            FROM atletas WHERE id_atleta = :id_atleta";
                $stmtE = $dbNegocio->getConex1()->prepare($sqlEdad);
                $stmtE->execute([':id_atleta' => $atleta['id_atleta'] ?? 0]);
                $edad = $stmtE->fetchColumn();

                if ($edad < 18) {
                    $sqlRep = "SELECT r.id_usuario 
                               FROM representantes r 
                               INNER JOIN atleta_representante ar ON r.id_representante = ar.id_representante 
                               WHERE ar.id_atleta = :id_atleta AND r.id_usuario IS NOT NULL";
                    $stmtR = $dbNegocio->getConex1()->prepare($sqlRep);
                    $stmtR->execute([':id_atleta' => $atleta['id_atleta'] ?? 0]);
                    $representantes = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($representantes as $rep) {
                        self::enviar(
                            (int)$rep['id_usuario'],
                            "Grupo de entrenamiento desactivado",
                            "El grupo de entrenamiento \"{$nombreGrupo}\" de tu representado ha sido desactivado. Pronto será reasignado.",
                            'fa-archive',
                            'gray',
                            "?p=grupo"
                        );
                    }
                }
            }

            self::notificarEntrenadorGrupoArchivado($id_grupo, $nombreGrupo);

        } catch (\PDOException $e) {
            error_log("Error notificando grupo archivado: " . $e->getMessage());
        }
    }

    private static function notificarEntrenadorGrupoArchivado(int $id_grupo, string $nombreGrupo): void {
        try {
            $dbNegocio = new Conexion('sis_natacion');
            
            $sql = "SELECT e.id_usuario, CONCAT(e.nombres, ' ', e.apellidos) as nombre
                    FROM entrenador e
                    INNER JOIN grupos_entrenamiento g ON g.id_entrenador = e.id_entrenador
                    WHERE g.id_grupo = :id_grupo AND e.id_usuario IS NOT NULL";
            $stmt = $dbNegocio->getConex1()->prepare($sql);
            $stmt->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($entrenador && !empty($entrenador['id_usuario'])) {
                self::enviar(
                    (int)$entrenador['id_usuario'],
                    "Grupo desactivado",
                    "Tu grupo \"{$nombreGrupo}\" ha sido desactivado. Los atletas han sido notificados y serán reasignados.",
                    'fa-archive',
                    'orange',
                    "?p=grupo"
                );
            }
        } catch (\PDOException $e) {
            error_log("Error notificando entrenador grupo archivado: " . $e->getMessage());
        }
    }

  
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
            error_log("Error al listar notificaciones: " . $e->getMessage());
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

>>>>>>> eb50f6eab47652c61cbb66c5c0b16eca945d9638
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
            error_log("Aviso Crítico en Notificaciones: Falló despacho de atletas [{$accion}]: " . $th->getMessage());
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
            error_log("Aviso Crítico en Notificaciones: Falló despacho de marcas [{$accion}]: " . $th->getMessage());
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
            error_log("Aviso Critico en Notificaciones: Fallo despacho de eventos [{$accion}]: " . $th->getMessage());
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
            error_log("Aviso Critico en Notificaciones: Fallo despacho de observaciones [{$accion}]: " . $th->getMessage());
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
            error_log("Aviso Crítico en Notificaciones: Falló despacho de periodizacion [{$accion}]: " . $th->getMessage());
        }
    }

    /**
     * DESPACHADOR CENTRALIZADO PARA EL MÓDULO DE LESIONES
     */
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

            // 1. Notificar al Atleta y Representante
            self::notificarAtletaYRepresentante($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);

            // 2. Notificar al Entrenador del grupo
            self::notificarEntrenador($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);

            // 3. Notificar a Médicos y Administradores
            self::notificarStaffMedicoYAdmin($titulo, $mensaje, $icono, $color, $deepLink);

        } catch (\Throwable $th) {
            error_log("Aviso Crítico en Notificaciones: Falló despacho de lesiones [{$accion}]: " . $th->getMessage());
        }
    }

    /**
     * DESPACHADOR CENTRALIZADO PARA EL MÓDULO DE ANTROPOMETRÍA
     */
    public static function NotificarAntropometria(string $accion, array $data, int $id_atleta, ?int $id_medicion = null): void {
        try {
            $deepLink = "?p=antropometria";
            if ($id_medicion) {
                $deepLink .= "&id=" . $id_medicion; 
            }

            // Normalizamos las variables (soportando datos del payload o de la DB)
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

            // 1. Notificar al Atleta y Representante
            self::notificarAtletaYRepresentante($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);

            // 2. Notificar al Entrenador del grupo
            self::notificarEntrenador($id_atleta, $titulo, $mensaje, $icono, $color, $deepLink);

            // 3. Notificar a Médicos y Administradores
            self::notificarStaffMedicoYAdmin($titulo, $mensaje, $icono, $color, $deepLink);

        } catch (\Throwable $th) {
            error_log("Aviso Crítico en Notificaciones: Falló despacho de antropometria [{$accion}]: " . $th->getMessage());
        }
    }
}