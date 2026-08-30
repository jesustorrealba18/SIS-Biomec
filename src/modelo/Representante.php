<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Representante extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    
    private array $datos = [];

    private array $camposPermitidos = [
        'cedula_original', 'cedula', 'nombres', 'apellidos', 'telefono_principal', 
        'telefono_emergencia', 'correo', 'parentesco', 'direccion_residencia', 
        'id_representante', 'atletas_ids', 'accion',
        'autorizacion_medica', 'fecha_aut_medica', 
        'autorizacion_imagen', 'fecha_aut_imagen', 
        'recibe_notificaciones', 
        'aut_medica', 'aut_imagen'
    ];

    
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

   

private function validarDatos(): bool {
        $this->resetearErrores();

        $cedula        = $this->datos['cedula'] ?? '';
        $nombres       = $this->datos['nombres'] ?? '';
        $apellidos     = $this->datos['apellidos'] ?? '';
        $telPrin       = $this->datos['telefono_principal'] ?? '';
        $telEmer       = $this->datos['telefono_emergencia'] ?? '';
        $correo        = $this->datos['correo'] ?? '';
        $parentesco    = $this->datos['parentesco'] ?? '';
        $direccion     = $this->datos['direccion_residencia'] ?? '';

        $autMedica     = $this->datos['aut_medica'] ?? [];
        $autImagen     = $this->datos['aut_imagen'] ?? [];
        
        $excluirCedula = $this->datos['cedula_original'] ?? ''; 

        // --- VALIDACIÓN DE CÉDULA ---
        
        if ($this->requerido($cedula, 'cedula')) {
            $this->cedula($cedula, 'cedula');
            $this->longitud($cedula, 'cedula', 6, 10);
            
         
        }

        // --- VALIDACIÓN DE NOMBRES ---
        if ($this->requerido($nombres, 'nombres')) {
            $this->soloLetras($nombres, 'nombres');
            $this->longitud($nombres, 'nombres', 3, 40);
        }

        // --- VALIDACIÓN DE APELLIDOS ---
        if ($this->requerido($apellidos, 'apellidos')) {
            $this->soloLetras($apellidos, 'apellidos');
            $this->longitud($apellidos, 'apellidos', 3, 40);
        }

        // --- VALIDACIÓN DE TELÉFONO PRINCIPAL ---
        if ($this->requerido($telPrin, 'telefono_principal')) {
            $this->soloNumeros($telPrin, 'telefono_principal');
            $this->longitud($telPrin, 'telefono_principal', 11, 11);
        }

        // --- VALIDACIÓN DE TELÉFONO DE EMERGENCIA (OPCIONAL) ---
  
        if (!empty(trim($telEmer))) {
            $this->soloNumeros($telEmer, 'telefono_emergencia');
            $this->longitud($telEmer, 'telefono_emergencia', 11, 11);
        }

        // --- VALIDACIÓN DE CORREO ---
        if ($this->requerido($correo, 'correo')) {
            $this->correoValido($correo, 'correo');
            $this->longitud($correo, 'correo', 5, 40);
        }

        // --- VALIDACIÓN DE PARENTESCO (Protección contra manipulación del HTML) ---
        if ($this->requerido($parentesco, 'parentesco')) {
            // Evita que un atacante inyecte valores desde la consola del navegador
            $parentescosPermitidos = ['Padre', 'Madre', 'Tutor', 'Otro'];
            if (!in_array($parentesco, $parentescosPermitidos)) {
                $this->agregarError('parentesco', 'El parentesco seleccionado no es válido.');
            }
        }

        // --- VALIDACIÓN DE DIRECCIÓN ---
        if ($this->requerido($direccion, 'direccion_residencia')) {
            $this->longitud($direccion, 'direccion_residencia', 10, 200);
        }

       

      
        return empty($this->obtenerErrores());
    }

      private function validarReglasDeNegocio(): bool {
      
        
        $idRep = !empty($this->datos['cedula_original']) ? (int)$this->datos['cedula_original'] : 0;
        
        if ($idRep > 0) {
            $stmtRep = $this->pdo->prepare("SELECT COUNT(id_representante) FROM representantes WHERE id_representante = :id");
            $stmtRep->bindValue(':id', $idRep, \PDO::PARAM_INT);
            $stmtRep->execute();
            if ($stmtRep->fetchColumn() == 0) {
                $this->agregarError('cedula_original', 'El representante que intenta modificar no existe (Posible manipulación de origen).');
            }
        }

        
        $atletasIds = $this->datos['atletas_ids'] ?? [];
        
        if (!empty($atletasIds) && is_array($atletasIds)) {
            
            $idsLimpios = array_map('intval', $atletasIds);
            
            
            $marcadores = implode(',', array_fill(0, count($idsLimpios), '?'));
            
          
           
            $sqlAtletas = "SELECT COUNT(a.id_atleta) FROM atletas a
                           LEFT JOIN atleta_representante ar ON a.id_atleta = ar.id_atleta
                           WHERE a.id_atleta IN ($marcadores)
                             AND TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 18
                             AND (ar.id_representante IS NULL OR ar.id_representante = ?)";
                             
            $stmtAtletas = $this->pdo->prepare($sqlAtletas);
            
           
            $posicion = 1;
            foreach ($idsLimpios as $idAtleta) {
                $stmtAtletas->bindValue($posicion++, $idAtleta, \PDO::PARAM_INT);
            }
           
            $stmtAtletas->bindValue($posicion, $idRep, \PDO::PARAM_INT);
            
            $stmtAtletas->execute();
            $conteoValidos = (int)$stmtAtletas->fetchColumn();
            
           
         
            if ($conteoValidos !== count($idsLimpios)) {
                $this->agregarError('atletas_ids', 'Violación de Integridad: Uno o más atletas seleccionados no existen, son mayores de edad o ya pertenecen a otro representante.');
            } else {
              
                
                $autMedica = $this->datos['aut_medica'] ?? [];
                $autImagen = $this->datos['aut_imagen'] ?? [];

                
                $llavesMedica = is_array($autMedica) ? array_keys($autMedica) : [];
                $llavesImagen = is_array($autImagen) ? array_keys($autImagen) : [];

                $trampaMedica = array_diff($llavesMedica, $idsLimpios);
                $trampaImagen = array_diff($llavesImagen, $idsLimpios);

               
                $valoresAlterados = false;
                
                $valoresPermitidos = ['1', '0', 1, 0]; 

                if (is_array($autMedica)) {
                    foreach ($autMedica as $val) {
                        
                        if (!in_array($val, $valoresPermitidos, true)) {
                            $valoresAlterados = true; 
                            break;
                        }
                    }
                }
                
                if (is_array($autImagen)) {
                    foreach ($autImagen as $val) {
                        if (!in_array($val, $valoresPermitidos, true)) {
                            $valoresAlterados = true; 
                            break;
                        }
                    }
                }

                
                if (!empty($trampaMedica) || !empty($trampaImagen) || $valoresAlterados) {
                    $this->agregarError('atletas_ids', 'Violación de Seguridad (Error 400): Se detectó una manipulación maliciosa en la estructura o valores de las autorizaciones.');
                }
            }
        }
      
        return empty($this->obtenerErrores());
    }



    public function Registrar(): bool {
       
        if (!$this->validarDatos()) {
            return false; 
        } 

        if (!$this->validarReglasDeNegocio()) {
            return false; 
        } 


        return $this->registrarRepresentante();
    }

    public function Actualizar(): bool {
       
        if (!$this->validarDatos()) {
            return false; 
        } 

        if (!$this->validarReglasDeNegocio()) {
            return false; 
        } 

        return $this->actualizarRepresentante();
    }

    public function Eliminar(): bool {
       
    if (empty($this->datos['id_representante'])) {
            $this->agregarError('id_representante', 'El ID del representante es requerido para eliminar.');
            return false;
        }
         
        return $this->eliminarRepresentante();
    }    

    public function Reactivar(): bool {

    if (empty($this->datos['id_representante'])) {
            $this->agregarError('id_representante', 'El ID del representante es requerido para reactivar.');
            return false;
        }
       
         
        return $this->reactivarRepresentante();
    }    


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

            
            $locales = [
                'id_usuario_local' => $_SESSION['id'] ?? 1
            ];

           
            $this->autoBind($stmt, $mapa, $this->datos, $locales);
            $stmt->execute();

            
            $id_representante = (int)$this->pdo->lastInsertId();

            
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


private function vincularAtletas(\PDO $conex, int $id_representante, array $datos) {
   
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
            'aut_medica_local' => !empty($datos['aut_medica'][$id_atleta]) ? 1 : 0,
            'aut_img_local'    => !empty($datos['aut_imagen'][$id_atleta]) ? 1 : 0
        ];
        
        $this->autoBind($stmt, $mapa, $datos, $locales);
        $stmt->execute();
    }
}



   
    private function actualizarRepresentante(): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

          
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

            
           /*  $sqlDelete = "DELETE FROM atleta_representante WHERE id_representante = :id_rep";
            $stmtDel = $conex->prepare($sqlDelete);
            $stmtDel->bindValue(':id_rep', $id_representante, \PDO::PARAM_INT);
            $stmtDel->execute(); */
            $this->limpiarRelacionesAtletas($conex, $id_representante);

            
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

    private function limpiarRelacionesAtletas(\PDO $conex, int $id_representante): void {
        $sqlDelete = "DELETE FROM atleta_representante WHERE id_representante = :id_rep";
        $stmtDel = $conex->prepare($sqlDelete);
        $stmtDel->bindValue(':id_rep', $id_representante, \PDO::PARAM_INT);
        $stmtDel->execute();
    }


    private function eliminarRepresentante(): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $id_representante = (int)$this->datos['id_representante'];
            
            $sqlLogico = "UPDATE representantes SET estado = 'Inactivo' WHERE id_representante = :id";
            $stmtLogico = $conex->prepare($sqlLogico);
            $stmtLogico->bindValue(':id', $id_representante, \PDO::PARAM_INT);
            $stmtLogico->execute();

            
            /* $sqlFisico = "DELETE FROM atleta_representante WHERE id_representante = :id";
            $stmtFisico = $conex->prepare($sqlFisico);
            $stmtFisico->bindValue(':id',(int)$this->datos['id_representante'], \PDO::PARAM_INT);
            $stmtFisico->execute(); */
             $this->limpiarRelacionesAtletas($conex, $id_representante);

            $conex->commit();
            return true;
        } catch (\PDOException $e) {
            $conex->rollBack();
            error_log("Error Eliminando Representante: " . $e->getMessage());
            return false;
        }
    }

    
    private function reactivarRepresentante(): bool {
        $conex = $this->pdo;
        try {
           
            $sql = "UPDATE representantes SET estado = 'Activo' WHERE id_representante = :id";
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id', (int)$this->datos['id_representante'], \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error Reactivando Representante: " . $e->getMessage());
            return false;
        }
    }       

    
    public function listarRepresentantes(string $estado = 'Activo'): array {
        $conex = $this->pdo;
        try {
            /* $sql = "SELECT 
                        r.id_representante, r.cedula, r.nombres, r.apellidos, 
                        r.telefono_principal, r.parentesco, r.estado,
                        GROUP_CONCAT(CONCAT(a.id_atleta, ':', a.nombres, ' ', a.apellidos) SEPARATOR '|') as atletas_vinculados
                    FROM representantes r
                    LEFT JOIN atleta_representante ar ON r.id_representante = ar.id_representante
                    LEFT JOIN atletas a ON ar.id_atleta = a.id_atleta
                    WHERE r.estado = :estado 
                    GROUP BY r.id_representante
                    ORDER BY r.id_representante DESC"; */
                    
                    $sql ="SELECT * FROM vista_representantes_atletas
                    WHERE estado = :estado
                    ORDER BY id_representante DESC;";
                    
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':estado', $estado, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error Listando Representantes: " . $e->getMessage());
            return [];
        }
    }
   
   
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



}

?>