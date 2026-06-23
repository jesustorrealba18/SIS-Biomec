<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Representante extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    
    private array $datos = [];

    /**
     * Lista blanca de campos permitidos (Protección contra Asignación Masiva)
     */
   /*  private array $camposPermitidos = [
        'cedula_original','cedula','nombres','apellidos','telefono_principal','telefono_emergencia',
        'correo','parentesco','direccion_residencia','contenedorCheckboxes', 'id_representante', 'id_usuario_local','atletas_ids'
    ]; */

    private array $camposPermitidos = [
        'cedula_original', 'cedula', 'nombres', 'apellidos', 'telefono_principal', 
        'telefono_emergencia', 'correo', 'parentesco', 'direccion_residencia', 
        'id_representante', 'atletas_ids', 'accion',
        // ¡AGREGADOS PARA QUE NO SE BORREN AL LIMPIAR EL PAYLOAD!
        'autorizacion_medica', 'fecha_aut_medica', 
        'autorizacion_imagen', 'fecha_aut_imagen', 
        'recibe_notificaciones', 
        'aut_medica', 'aut_imagen'
    ];

    
    /**
     * Mapea el payload externo  filtrando campos basura.
     * Soporta de forma segura variables escalares y arreglos estructurados (Splits).
     */
    public function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo])) {
                // Si el campo es un arreglo (como los splits), lo guardamos directo sin comparar con string
                if (is_array($payload[$campo])) {
                    $this->datos[$campo] = $payload[$campo];
                } 
                // Si es un dato normal, validamos que no esté vacío
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

   
  private function validarDatos(): bool {
        $this->resetearErrores();

        $cedula =  $this->datos['cedula'] ?? '';
        $nombres =  $this->datos['nombres'] ?? '';
        $apellidos =  $this->datos['apellidos'] ?? '';
        $excluirCedula =  $this->datos['cedula_original'] ?? '';



        $this->requerido($cedula, 'cedula');
        $this->soloNumeros($cedula, 'cedula');
        
        if (!$excluirCedula) {
            // Validamos contra la tabla real 'representantes'
            $this->unico($this->pdo, $cedula, 'representantes', 'cedula');
        }

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');

        return empty($this->obtenerErrores());
    }


/*     private function validarDatos(array $datos, ?string $excluirCedula = null): array {
        $this->resetearErrores();

        $cedula = $datos['cedula'] ?? '';
        $nombres = $datos['nombres'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';

        $this->requerido($cedula, 'cedula');
        $this->soloNumeros($cedula, 'cedula');
        
        if (!$excluirCedula) {
            // Validamos contra la tabla real 'representantes'
            $this->unico($this->pdo, $cedula, 'representantes', 'cedula');
        }

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');

        return $this->obtenerErrores();
    } */


    public function Registrar(): bool {
       
        if (!$this->validarDatos()) {
            return false; 
        } 
        return $this->registrarRepresentante();
    }

    public function Actualizar(): bool {
       
        if (!$this->validarDatos()) {
            return false; 
        } 
        return $this->actualizarRepresentante();
    }

    public function Eliminar(): bool {
       
         
        return $this->eliminarRepresentante();
    }    

    public function Reactivar(): bool {
       
         
        return $this->reactivarRepresentante();
    }    




    /**
     * Registra al representante en la tabla principal y lanza la vinculación
     */
/*     private function registrarRepresentante(): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

       
            $sql = "INSERT INTO representantes (
                        cedula, nombres, apellidos, parentesco, 
                        telefono_principal, telefono_secundario, 
                        correo, direccion, id_usuario
                    ) VALUES (
                        :cedula, :nombres, :apellidos, :parentesco, 
                        :tel_prin, :tel_sec, 
                        :correo, :direccion, :id_usuario
                    )";
            
            $stmt = $conex->prepare($sql);
            
            
            $stmt->execute([
                ':cedula'     => $datos['cedula'] ?? '',
                ':nombres'    => $datos['nombres'] ?? '',
                ':apellidos'  => $datos['apellidos'] ?? '',
                ':parentesco' => $datos['parentesco'] ?? '',
                ':tel_prin'   => $datos['telefono_principal'] ?? '',
                ':tel_sec'    => $datos['telefono_emergencia'] ?? '', 
                ':correo'     => $datos['correo'] ?? '',
                ':direccion'  => $datos['direccion_residencia'] ?? '', 
                ':id_usuario' => $_SESSION['id'] ?? 1 
            ]);

            // Capturamos el ID autoincrementable
            $id_representante = $conex->lastInsertId();

            // 2. Vinculamos los atletas marcados en los checkboxes
            if (!empty($datos['atletas_ids']) && is_array($datos['atletas_ids'])) {
                $this->vincularAtletas($conex, $id_representante, $datos);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error BD Representante: " . $e->getMessage());
            return false;
        }
    } */

    private function registrarRepresentante(): bool {
       
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO representantes (
                        cedula, nombres, apellidos, parentesco, 
                        telefono_principal, telefono_secundario, 
                        correo, direccion, id_usuario
                    ) VALUES (
                        :cedula, :nombres, :apellidos, :parentesco, 
                        :tel_prin, :tel_sec, 
                        :correo, :direccion, :id_usuario
                    )";
            
            $stmt = $this->pdo->prepare($sql);
            
            // MAPA DE ENLACE (Nombre del campo en $this->datos -> Tipo PDO)
            $mapa = [
                ':cedula'     => ['cedula', \PDO::PARAM_STR],
                ':nombres'    => ['nombres', \PDO::PARAM_STR],
                ':apellidos'  => ['apellidos', \PDO::PARAM_STR],
                ':parentesco' => ['parentesco', \PDO::PARAM_STR],
                ':tel_prin'   => ['telefono_principal', \PDO::PARAM_STR],
                ':tel_sec'    => ['telefono_emergencia', \PDO::PARAM_STR],
                ':correo'     => ['correo', \PDO::PARAM_STR],
                ':direccion'  => ['direccion_residencia', \PDO::PARAM_STR],
                ':id_usuario' => ['id_usuario_local', \PDO::PARAM_INT] // Usará $locales
            ];

            // Valores locales que no vienen del formulario (o que queremos forzar)
            $locales = [
                'id_usuario_local' => $_SESSION['id'] ?? 1
            ];

            // Ejecutamos la magia del Trait
            $this->autoBind($stmt, $mapa, $this->datos, $locales);
            $stmt->execute();

            // Capturamos el ID autoincrementable
            $id_representante = (int)$this->pdo->lastInsertId();

            // 2. Vinculamos los atletas marcados en los checkboxes
            if (!empty($this->datos['atletas_ids']) && is_array($this->datos['atletas_ids'])) {
                $this->vincularAtletas($this->pdo, $id_representante, $this->datos);
            }

            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();

             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }

             $this->agregarError('integridad', $e->getMessage());

            error_log("Error BD Representante: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tabla intermedia: atleta_representante
     */
  /*    private function vincularAtletas(PDO $conex, int $id_representante, array $datos) {
        $sql = "INSERT INTO atleta_representante (
                    id_atleta, 
                    id_representante, 
                    autorizacion_medica, 
                    fecha_aut_medica, 
                    autorizacion_imagen, 
                    fecha_aut_imagen, 
                    recibe_notificaciones
                ) VALUES (
                    :id_atleta, 
                    :id_representante, 
                    :aut_medica, 
                    :fecha_medica, 
                    :aut_imagen, 
                    :fecha_imagen, 
                    :notificaciones
                )";
                
        $stmt = $conex->prepare($sql);
        
        foreach ($datos['atletas_ids'] as $id_atleta) {
            $stmt->execute([
                ':id_atleta'        => $id_atleta,
                ':id_representante' => $id_representante,
                ':aut_medica'       => $datos['autorizacion_medica'] ?? 'No',
                ':fecha_medica'     => !empty($datos['fecha_aut_medica']) ? $datos['fecha_aut_medica'] : null,
                ':aut_imagen'       => $datos['autorizacion_imagen'] ?? 'No',
                ':fecha_imagen'     => !empty($datos['fecha_aut_imagen']) ? $datos['fecha_aut_imagen'] : null,
                ':notificaciones'   => $datos['recibe_notificaciones'] ?? 'No'
            ]);
        }
    }  */

  /*   private function vincularAtletas(\PDO $conex, int $id_representante, array $datos) {
    $sql = "INSERT INTO atleta_representante (
                id_atleta, 
                id_representante, 
                autorizacion_medica, 
                fecha_aut_medica, 
                autorizacion_imagen, 
                fecha_aut_imagen, 
                recibe_notificaciones
            ) VALUES (
                :id_atleta, 
                :id_representante, 
                :aut_medica, 
                :fecha_medica, 
                :aut_imagen, 
                :fecha_imagen, 
                :notificaciones
            )";
            
    $stmt = $conex->prepare($sql);
    
    $mapa = [
        ':id_atleta'        => ['id_atleta_local', \PDO::PARAM_INT],
        ':id_representante' => ['id_rep_local', \PDO::PARAM_INT],
        ':aut_medica'       => ['aut_medica_local', \PDO::PARAM_INT], 
        ':fecha_medica'     => ['fecha_medica_local', \PDO::PARAM_STR], // STR para fechas
        ':aut_imagen'       => ['aut_img_local', \PDO::PARAM_INT],    
        ':fecha_imagen'     => ['fecha_img_local', \PDO::PARAM_STR],    // STR para fechas
        ':notificaciones'   => ['notif_local', \PDO::PARAM_INT]       
    ];
    
    $fechaHoy = date('Y-m-d'); // Capturamos la fecha del sistema

    foreach ($datos['atletas_ids'] as $id_atleta) {
        // ¿El representante marcó la autorización para ESTE atleta específico?
        $tieneAutMedica = !empty($datos['aut_medica'][$id_atleta]) ? 1 : 0;
        $tieneAutImagen = !empty($datos['aut_imagen'][$id_atleta]) ? 1 : 0;

        $locales = [
            'id_atleta_local'    => $id_atleta,
            'id_rep_local'       => $id_representante,
            
            'aut_medica_local'   => $tieneAutMedica,
            'fecha_medica_local' => $tieneAutMedica ? $fechaHoy : null, // Si autoriza, estampa la fecha
            
            'aut_img_local'      => $tieneAutImagen,
            'fecha_img_local'    => $tieneAutImagen ? $fechaHoy : null, // Si autoriza, estampa la fecha
            
            'notif_local'        => 1 // Por defecto le activamos las notificaciones
        ];
        
        $this->autoBind($stmt, $mapa, $datos, $locales);
        $stmt->execute();
    }
} */

private function vincularAtletas(\PDO $conex, int $id_representante, array $datos) {
    // Fíjate cómo el SQL ahora es puro y limpio. 
    // Las fechas se calcularán solas gracias al Trigger en la Base de Datos.
    $sql = "INSERT INTO atleta_representante (
                id_atleta, 
                id_representante, 
                autorizacion_medica, 
                autorizacion_imagen
            ) VALUES (
                :id_atleta, 
                :id_representante, 
                :aut_medica, 
                :aut_imagen
            )";
            
    $stmt = $conex->prepare($sql);
    
    // El mapa se reduce solo a 4 variables INT
    $mapa = [
        ':id_atleta'        => ['id_atleta_local', \PDO::PARAM_INT],
        ':id_representante' => ['id_rep_local', \PDO::PARAM_INT],
        ':aut_medica'       => ['aut_medica_local', \PDO::PARAM_INT], 
        ':aut_imagen'       => ['aut_img_local', \PDO::PARAM_INT]
    ];
    
    foreach ($datos['atletas_ids'] as $id_atleta) {
        $locales = [
            'id_atleta_local'  => $id_atleta,
            'id_rep_local'     => $id_representante,
            // Solo enviamos 1 o 0. El Trigger decidirá si pone la fecha o no.
            'aut_medica_local' => !empty($datos['aut_medica'][$id_atleta]) ? 1 : 0,
            'aut_img_local'    => !empty($datos['aut_imagen'][$id_atleta]) ? 1 : 0
        ];
        
        $this->autoBind($stmt, $mapa, $datos, $locales);
        $stmt->execute();
    }
}


    /**
     * Tabla intermedia: atleta_representante
     */

/* private function vincularAtletas(\PDO $conex, int $id_representante, array $datos) {
    $sql = "INSERT INTO atleta_representante (
                id_atleta, 
                id_representante, 
                autorizacion_medica, 
                fecha_aut_medica, 
                autorizacion_imagen, 
                fecha_aut_imagen, 
                recibe_notificaciones
            ) VALUES (
                :id_atleta, 
                :id_representante, 
                :aut_medica, 
                :fecha_medica, 
                :aut_imagen, 
                :fecha_imagen, 
                :notificaciones
            )";
            
    $stmt = $conex->prepare($sql);
    
    // CORRECCIÓN: Los booleanos ahora son PARAM_INT
    $mapa = [
        ':id_atleta'        => ['id_atleta_local', \PDO::PARAM_INT],
        ':id_representante' => ['id_rep_local', \PDO::PARAM_INT],
        ':aut_medica'       => ['aut_medica_local', \PDO::PARAM_INT], // <-- Cambiado a INT
        ':fecha_medica'     => ['fecha_aut_medica', \PDO::PARAM_STR],
        ':aut_imagen'       => ['aut_img_local', \PDO::PARAM_INT],    // <-- Cambiado a INT
        ':fecha_imagen'     => ['fecha_aut_imagen', \PDO::PARAM_STR],
        ':notificaciones'   => ['notif_local', \PDO::PARAM_INT]       // <-- Cambiado a INT
    ];
    
    foreach ($datos['atletas_ids'] as $id_atleta) {
        // CORRECCIÓN: Si el campo viene en el POST (checkbox marcado) es 1, si no es 0
        $locales = [
            'id_atleta_local'  => $id_atleta,
            'id_rep_local'     => $id_representante,
            'aut_medica_local' => !empty($datos['autorizacion_medica']) ? 1 : 0, // <-- 1 o 0
            'aut_img_local'    => !empty($datos['autorizacion_imagen']) ? 1 : 0, // <-- 1 o 0
            'notif_local'      => !empty($datos['recibe_notificaciones']) ? 1 : 0 // <-- 1 o 0
        ];
        
        $this->autoBind($stmt, $mapa, $datos, $locales);
        $stmt->execute();
    }
} */

   /*  private function vincularAtletas(\PDO $conex, int $id_representante, array $datos) {
        $sql = "INSERT INTO atleta_representante (
                    id_atleta, 
                    id_representante, 
                    autorizacion_medica, 
                    fecha_aut_medica, 
                    autorizacion_imagen, 
                    fecha_aut_imagen, 
                    recibe_notificaciones
                ) VALUES (
                    :id_atleta, 
                    :id_representante, 
                    :aut_medica, 
                    :fecha_medica, 
                    :aut_imagen, 
                    :fecha_imagen, 
                    :notificaciones
                )";
                
        $stmt = $conex->prepare($sql);
        
        // El mapa se define una sola vez fuera del bucle
        $mapa = [
            ':id_atleta'        => ['id_atleta_local', \PDO::PARAM_INT],
            ':id_representante' => ['id_rep_local', \PDO::PARAM_INT],
            ':aut_medica'       => ['aut_medica_local', \PDO::PARAM_STR],
            ':fecha_medica'     => ['fecha_aut_medica', \PDO::PARAM_STR], // Extrae directo de $datos
            ':aut_imagen'       => ['aut_img_local', \PDO::PARAM_STR],
            ':fecha_imagen'     => ['fecha_aut_imagen', \PDO::PARAM_STR], // Extrae directo de $datos
            ':notificaciones'   => ['notif_local', \PDO::PARAM_STR]
        ];
        
        foreach ($datos['atletas_ids'] as $id_atleta) {
            // Locales para cada iteración del bucle y defaults de los checkbox
            $locales = [
                'id_atleta_local'  => $id_atleta,
                'id_rep_local'     => $id_representante,
                'aut_medica_local' => $datos['autorizacion_medica'] ?? 'No',
                'aut_img_local'    => $datos['autorizacion_imagen'] ?? 'No',
                'notif_local'      => $datos['recibe_notificaciones'] ?? 'No'
            ];
            
            $this->autoBind($stmt, $mapa, $datos, $locales);
            $stmt->execute();
        }
    } */

    /**
 * Actualiza los datos del representante y refresca sus vinculaciones
 */
/* private function actualizarRepresentante(array $datos): bool {
    $conex = $this->pdo;
    try {
        $conex->beginTransaction();

        // 1. Actualizar datos personales en la tabla principal
        $sql = "UPDATE representantes SET 
                    cedula = :cedula, nombres = :nombres, apellidos = :apellidos, 
                    parentesco = :parentesco, telefono_principal = :tel_prin, 
                    telefono_secundario = :tel_sec, correo = :correo, direccion = :direccion
                WHERE id_representante = :id_rep";
                
        $stmt = $conex->prepare($sql);
        $id_representante = (int)($datos['cedula_original'] ?? 0);
        
        $stmt->execute([
            ':cedula'     => $datos['cedula'] ?? '',
            ':nombres'    => $datos['nombres'] ?? '',
            ':apellidos'  => $datos['apellidos'] ?? '',
            ':parentesco' => $datos['parentesco'] ?? '',
            ':tel_prin'   => $datos['telefono_principal'] ?? '',
            ':tel_sec'    => $datos['telefono_emergencia'] ?? '',
            ':correo'     => $datos['correo'] ?? '',
            ':direccion'  => $datos['direccion_residencia'] ?? '',
            ':id_rep'     => $id_representante
        ]);

        // 2. Limpiar vinculaciones anteriores para evitar duplicados o conflictos de llaves
        $sqlDelete = "DELETE FROM atleta_representante WHERE id_representante = :id_rep";
        $stmtDel = $conex->prepare($sqlDelete);
        $stmtDel->execute([':id_rep' => $id_representante]);

        // 3. Insertar las nuevas vinculaciones marcadas en el formulario
        if (!empty($datos['atletas_ids']) && is_array($datos['atletas_ids'])) {
            $this->vincularAtletas($conex, $id_representante, $datos);
        }

        $conex->commit();
        return true;
    } catch (PDOException $e) {
        $conex->rollBack();
        error_log("Error en actualizarRepresentante: " . $e->getMessage());
        return false;
    }
} */


    /**
     * Actualiza los datos del representante y refresca sus vinculaciones
     */
    private function actualizarRepresentante(): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            // 1. Actualizar datos personales en la tabla principal
            $sql = "UPDATE representantes SET 
                        cedula = :cedula, nombres = :nombres, apellidos = :apellidos, 
                        parentesco = :parentesco, telefono_principal = :tel_prin, 
                        telefono_secundario = :tel_sec, correo = :correo, direccion = :direccion
                    WHERE id_representante = :id_rep";
                    
            $stmt = $conex->prepare($sql);
            $id_representante = (int)($this->datos['cedula_original'] ?? 0);
            
            $mapa = [
                ':cedula'     => ['cedula', \PDO::PARAM_STR],
                ':nombres'    => ['nombres', \PDO::PARAM_STR],
                ':apellidos'  => ['apellidos', \PDO::PARAM_STR],
                ':parentesco' => ['parentesco', \PDO::PARAM_STR],
                ':tel_prin'   => ['telefono_principal', \PDO::PARAM_STR],
                ':tel_sec'    => ['telefono_emergencia', \PDO::PARAM_STR],
                ':correo'     => ['correo', \PDO::PARAM_STR],
                ':direccion'  => ['direccion_residencia', \PDO::PARAM_STR],
                ':id_rep'     => ['id_rep_local', \PDO::PARAM_INT]
            ];

            $locales = [
                'id_rep_local' => $id_representante
            ];
            
            $this->autoBind($stmt, $mapa, $this->datos, $locales);
            $stmt->execute();

            // 2. Limpiar vinculaciones anteriores (DELETE usa bindValue directo por ser un solo campo)
            $sqlDelete = "DELETE FROM atleta_representante WHERE id_representante = :id_rep";
            $stmtDel = $conex->prepare($sqlDelete);
            $stmtDel->bindValue(':id_rep', $id_representante, \PDO::PARAM_INT);
            $stmtDel->execute();

            // 3. Insertar las nuevas vinculaciones marcadas en el formulario
            if (!empty($this->datos['atletas_ids']) && is_array($this->datos['atletas_ids'])) {
                $this->vincularAtletas($conex, $id_representante, $this->datos);
            }

            $conex->commit();
            return true;
        } catch (\PDOException $e) {
            $conex->rollBack();
            error_log("Error en actualizarRepresentante: " . $e->getMessage());
            return false;
        }
    }


    private function eliminarRepresentante(): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            // 1. Eliminado Lógico: Ocultamos al representante
            $sqlLogico = "UPDATE representantes SET estado = 'Inactivo' WHERE id_representante = :id";
            $stmtLogico = $conex->prepare($sqlLogico);
            $stmtLogico->bindValue(':id', (int)$this->datos['id_representante'], \PDO::PARAM_INT);
            $stmtLogico->execute();

            // 2. Desvinculación Física: Liberamos a los atletas
            $sqlFisico = "DELETE FROM atleta_representante WHERE id_representante = :id";
            $stmtFisico = $conex->prepare($sqlFisico);
            $stmtFisico->bindValue(':id',(int)$this->datos['id_representante'], \PDO::PARAM_INT);
            $stmtFisico->execute();

            $conex->commit();
            return true;
        } catch (\PDOException $e) {
            $conex->rollBack();
            error_log("Error Eliminando Representante: " . $e->getMessage());
            return false;
        }
    }

    // MÉTODO: Reactivar Representante
    private function reactivarRepresentante(): bool {
        $conex = $this->pdo;
        try {
            // Devolvemos el estado a 'Activo'
            $sql = "UPDATE representantes SET estado = 'Activo' WHERE id_representante = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', (int)$this->datos['id_representante'], \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error Reactivando Representante: " . $e->getMessage());
            return false;
        }
    }       

    // MÉTODO: Listar Representantes (Dinámico)
    public function listarRepresentantes(string $estado = 'Activo'): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        r.id_representante, r.cedula, r.nombres, r.apellidos, 
                        r.telefono_principal, r.parentesco, r.estado,
                        GROUP_CONCAT(CONCAT(a.id_atleta, ':', a.nombres, ' ', a.apellidos) SEPARATOR '|') as atletas_vinculados
                    FROM representantes r
                    LEFT JOIN atleta_representante ar ON r.id_representante = ar.id_representante
                    LEFT JOIN atletas a ON ar.id_atleta = a.id_atleta
                    WHERE r.estado = :estado 
                    GROUP BY r.id_representante
                    ORDER BY r.id_representante DESC";
                    
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':estado', $estado, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error Listando Representantes: " . $e->getMessage());
            return [];
        }
    }
   
    /**
     * Obtiene los datos de un representante por su ID
     */
    public function obtenerPorId(int $id): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT * FROM representantes WHERE id_representante = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (\PDOException $e) {
            error_log("Error en obtenerPorId (Representante): " . $e->getMessage());
            return null;
        }
    }

        // NUEVA FUNCIÓN: Eliminar Híbrido
  /*   private function eliminarRepresentante(int $id): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            // 1. Eliminado Lógico: Ocultamos al papá
            $sqlLogico = "UPDATE representantes SET estado = 'Inactivo' WHERE id_representante = :id";
            $stmtLogico = $conex->prepare($sqlLogico);
            $stmtLogico->execute([':id' => $id]);

            // 2. Desvinculación Física: Liberamos a los atletas para que puedan ser adoptados por otro representante
            $sqlFisico = "DELETE FROM atleta_representante WHERE id_representante = :id";
            $stmtFisico = $conex->prepare($sqlFisico);
            $stmtFisico->execute([':id' => $id]);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error Eliminando: " . $e->getMessage());
            return false;
        }
    }

    // NUEVO MÉTODO: Reactivar Representante
    private function reactivarRepresentante(int $id): bool {
        $conex = $this->pdo;
        try {
            // Simplemente devolvemos el estado a 'Activo'
            // Nota: Los atletas no se revínculan automáticamente porque pudieron ser asignados a otra persona mientras este estuvo inactivo
            $sql = "UPDATE representantes SET estado = 'Activo' WHERE id_representante = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }       

  // MODIFICAR EL MÉTODO DE LISTAR PARA QUE SEA DINÁMICO
    public function listarRepresentantes(string $estado = 'Activo'): array {
        $conex = $this->pdo;
        try {
            // El WHERE ahora usa el parámetro :estado dinámicamente
            $sql = "SELECT 
                        r.id_representante, r.cedula, r.nombres, r.apellidos, 
                        r.telefono_principal, r.parentesco, r.estado,
                        GROUP_CONCAT(CONCAT(a.id_atleta, ':', a.nombres, ' ', a.apellidos) SEPARATOR '|') as atletas_vinculados
                    FROM representantes r
                    LEFT JOIN atleta_representante ar ON r.id_representante = ar.id_representante
                    LEFT JOIN atletas a ON ar.id_atleta = a.id_atleta
                    WHERE r.estado = :estado 
                    GROUP BY r.id_representante
                    ORDER BY r.id_representante DESC";
                    
            $stmt = $conex->prepare($sql);
            $stmt->execute([':estado' => $estado]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
   
   
     * Obtiene los datos de un representante por su ID
    
    public function obtenerPorId(int $id): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT * FROM representantes WHERE id_representante = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    } */


/*     public function listarRepresentantes(): array {
        $conex = $this->getConex1();
        try {
            // Usamos LEFT JOIN para traer a los atletas y GROUP_CONCAT para unirlos en una sola línea separados por un " | "
            $sql = "SELECT 
                        r.id_representante, 
                        r.cedula, 
                        r.nombres, 
                        r.apellidos, 
                        r.telefono_principal, 
                        r.parentesco,
                        GROUP_CONCAT(CONCAT(a.id_atleta, ':', a.nombres, ' ', a.apellidos) SEPARATOR '|') as atletas_vinculados
                    FROM representantes r
                    LEFT JOIN atleta_representante ar ON r.id_representante = ar.id_representante
                    LEFT JOIN atletas a ON ar.id_atleta = a.id_atleta
                    GROUP BY r.id_representante
                    ORDER BY r.id_representante DESC";
                    
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando: " . $e->getMessage());
            return [];
        }
    } */


}

?>