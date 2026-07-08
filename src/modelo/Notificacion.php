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

         // 1. EXTRAER DATOS ENCAPSULADOS
        $id_notif = $this->datos['id_notificacion'] ?? '';
        $id_user = $this->datos['id_usuario'] ?? '';

        // 2. VALIDACIONES DE FORMATO (Usando tu Trait)
        if (!$this->requerido((string)$id_notif, 'ID Notificación') || 
            !$this->soloNumeros((string)$id_notif, 'ID Notificación')) {
            return false;
        }

        if (!$this->requerido((string)$id_user, 'Usuario') || 
            !$this->soloNumeros((string)$id_user, 'Usuario')) {
            return false;
        }

          // 3. VALIDACIÓN DE EXISTENCIA Y PROPIEDAD
            $sqlCheck = "SELECT leida FROM notificaciones WHERE id_notificacion = :id_notificacion AND id_usuario = :id_usuario";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':id_notificacion' => (int)$id_notif, 
                ':id_usuario' => (int)$id_user
            ]);
            
            $notificacion = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

            if (!$notificacion) {
                $this->agregarError('Seguridad', 'La notificación no existe o no te pertenece.');
               // error_log("ALERTA SEGURIDAD: Manipulación detectada. Usuario {$id_user} intentó alterar notificación {$id_notif}.");
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
        

            // 4. EJECUCIÓN SEGURA
            $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id_notificacion";
            $stmt = $this->pdo->prepare($sql);
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
            $instNoti = new self(); // Se conecta a sis_seguridad
            $sql = "INSERT INTO notificaciones (id_usuario, titulo, mensaje, icono, color, enlace_url) 
                    VALUES (:id_usuario, :titulo, :mensaje, :icono, :color, :enlace_url)";
            $stmt = $instNoti->pdo->prepare($sql);
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
            // Para BUSCAR a los usuarios, necesitamos conectarnos temporalmente a sis_natacion
            $dbNegocio = new Conexion('sis_natacion'); 

            // A) Buscar el id_usuario del Atleta Y SU EDAD
            $sqlAtleta = "SELECT id_usuario, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad 
                          FROM atletas 
                          WHERE id_atleta = :id_atleta";
            $stmtA = $dbNegocio->pdo->prepare($sqlAtleta);
            $stmtA->execute([':id_atleta' => $id_atleta]);
            $userAtleta = $stmtA->fetch(\PDO::FETCH_ASSOC);

            if ($userAtleta) {
                // 1. Notificar al Atleta (Si tiene un usuario web asignado)
                if (!empty($userAtleta['id_usuario'])) {
                    self::enviar($userAtleta['id_usuario'], $titulo, $mensaje, $icono, $color, $enlace_url);
                }

                // 2. REGLA DE NEGOCIO: Solo notificar al representante si el atleta es menor de 18 años
                if ($userAtleta['edad'] < 18) {
                    $sqlRep = "SELECT r.id_usuario 
                               FROM representantes r 
                               INNER JOIN atleta_representante ar ON r.id_representante = ar.id_representante 
                               WHERE ar.id_atleta = :id_atleta AND r.id_usuario IS NOT NULL";
                    $stmtR = $dbNegocio->pdo->prepare($sqlRep);
                    $stmtR->execute([':id_atleta' => $id_atleta]);
                    $representantes = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

                    // Enviamos copia a cada representante
                    foreach ($representantes as $rep) {
                        // Le cambiamos un poco el título para que sepa que es sobre su representado
                        self::enviar($rep['id_usuario'], "Atleta a tu cargo: " . $titulo, $mensaje, $icono, $color, $enlace_url);
                    }
                }
            }

        } catch (\PDOException $e) {
            error_log("Error Routing Notificacion: " . $e->getMessage());
        }
    }


    /**
     * Obtiene la lista de notificaciones de un usuario
     */
    public static function listarPorUsuario(int $id_usuario, int $limite = 10): array {
        try {
            $instNoti = new self(); // Se conecta a sis_seguridad automáticamente
            $sql = "SELECT id_notificacion, titulo, mensaje, icono, color, leida, fecha, enlace_url 
                    FROM notificaciones 
                    WHERE id_usuario = :id_usuario 
                    ORDER BY fecha DESC LIMIT :limite";
            
            $stmt = $instNoti->pdo->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar notificaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta cuántas notificaciones no ha leído el usuario
     */
    public static function contarNoLeidas(int $id_usuario): int {
        try {
            $instNoti = new self();
            $sql = "SELECT COUNT(*) FROM notificaciones WHERE id_usuario = :id_usuario AND leida = 0";
            $stmt = $instNoti->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * DESPACHADOR CENTRALIZADO PARA EL MÓDULO DE MARCAS
     * Centraliza textos, colores, iconos y el bloque try-catch (Cumple SRP y DRY)
     */
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

            // Evaluamos la acción para construir dinámicamente la notificación
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
                   // $deepLink = "?p=marcas"; // Las marcas inactivas no se iluminan, va al listado limpio 
                    $deepLink = "?p=marcas&estado=Inactivo&h=" . $id_marca;
                    break;

                case 'RESTORE':
                    $titulo = "Marca Restaurada";
                    $mensaje = "Se ha restaurado y reactivado una marca previamente deshabilitada en el historial.";
                    $icono = "fa-history";
                    $color = "indigo";
                    break;

                default:
                    return; // Acción no soportada, salimos pacíficamente
            }

            // Invocamos al enrutador que ya programamos con la lógica de la edad
            self::notificarAtletaYRepresentante((int)$data['id_atleta'], $titulo, $mensaje, $icono, $color, $deepLink);

        } catch (\Throwable $th) {
            // El try-catch vive AQUÍ. Si falla el envío, no rompe el flujo del negocio
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


}