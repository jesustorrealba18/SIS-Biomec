<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Atleta extends Conexion {
    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function validarDatos(array $datos, ?int $excluirId = null): array {
        $this->resetearErrores();

        $cedula       = $datos['cedula'] ?? '';
        $nombres      = $datos['nombres'] ?? '';
        $apellidos    = $datos['apellidos'] ?? '';
        $fecha        = $datos['fecha_nacimiento'] ?? '';
        $sexo         = $datos['sexo'] ?? '';
        $estado       = $datos['estado'] ?? '';
        $correo       = $datos['correo'] ?? '';
        $telefono     = $datos['telefono'] ?? '';
        $direccion    = $datos['direccion'] ?? '';
        $grupoSang    = $datos['grupo_sanguineo'] ?? '';
        $seguro       = $datos['seguro_medico'] ?? '';
        $alergias     = $datos['alergias'] ?? '';
        $condiciones  = $datos['condiciones_previas'] ?? '';
        $contNombre   = $datos['contacto_emergencia_nombre'] ?? '';
        $contTelefono = $datos['contacto_emergencia_telefono'] ?? '';
        $contParentesco = $datos['contacto_emergencia_parentesco'] ?? '';
        $feveda       = $datos['numero_feveda'] ?? '';
        $club         = $datos['club_procedencia'] ?? '';
        $idCategoria  = $datos['id_categoria'] ?? '';
        $fechaRegistro = $datos['fecha_registro_club'] ?? '';

        // === DATOS PERSONALES ===

        $this->requerido($cedula, 'cedula');
        if ($cedula !== '') {
            $this->cedula($cedula, 'cedula');
        }
        $this->unico($this->pdo, trim($cedula), 'atletas', 'cedula', $excluirId, 'id_atleta');

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');
        $this->longitud($nombres, 'nombres', 2, 100);

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');
        $this->longitud($apellidos, 'apellidos', 2, 100);

        $this->requerido($fecha, 'fecha_nacimiento');
        $this->fechaValida($fecha, 'fecha_nacimiento');
        $this->fechaNoFutura($fecha, 'fecha_nacimiento');

        $this->requerido($sexo, 'sexo');
        $this->enEnum($sexo, 'sexo', ['M', 'F']);

        $this->requerido($estado, 'estado');
        $this->enEnum($estado, 'estado', ['Activo', 'Inactivo', 'Retirado', 'Transferido']);

        $this->requerido($correo, 'correo');
        $this->correoValido($correo, 'correo');
        $this->unico($this->pdo, $correo, 'atletas', 'correo', $excluirId, 'id_atleta');

        $this->requerido($telefono, 'telefono');
        $this->telefono($telefono, 'telefono');

        $this->requerido($direccion, 'direccion');
        $this->longitud($direccion, 'direccion', 5, 200);

        $this->requerido($fechaRegistro, 'fecha_registro_club');
        $this->fechaValida($fechaRegistro, 'fecha_registro_club');
        $this->fechaNoFutura($fechaRegistro, 'fecha_registro_club');

        // === DATOS MEDICOS ===

        $this->requerido($grupoSang, 'grupo_sanguineo');
        $this->enEnum($grupoSang, 'grupo_sanguineo', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);

        $this->requerido($seguro, 'seguro_medico');
        $this->longitud($seguro, 'seguro_medico', 2, 100);

        $this->requerido($alergias, 'alergias');
        $this->longitud($alergias, 'alergias', 0, 500);

        $this->requerido($condiciones, 'condiciones_previas');
        $this->longitud($condiciones, 'condiciones_previas', 0, 500);

        $this->requerido($contNombre, 'contacto_emergencia_nombre');
        $this->soloLetras($contNombre, 'contacto_emergencia_nombre');
        $this->longitud($contNombre, 'contacto_emergencia_nombre', 2, 100);

        $this->requerido($contTelefono, 'contacto_emergencia_telefono');
        $this->telefono($contTelefono, 'contacto_emergencia_telefono');

        $this->requerido($contParentesco, 'contacto_emergencia_parentesco');
        $this->enEnum($contParentesco, 'contacto_emergencia_parentesco', ['Padre', 'Madre', 'Hermano/a', 'Tutor', 'Otro']);

        // === DATOS FEDERATIVOS ===

        $this->requerido($idCategoria, 'id_categoria');

        $this->requerido($feveda, 'numero_feveda');
        $this->longitud($feveda, 'numero_feveda', 1, 50);

        $this->requerido($club, 'club_procedencia');
        $this->longitud($club, 'club_procedencia', 2, 100);

        return $this->errores;
    }

    public function listarAtletas(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT a.*, 
                           TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) AS edad,
                           c.nombre AS categoria_nombre,
                           dm.numero_feveda,
                           dm.grupo_sanguineo,
                           dm.alergias,
                           dm.condiciones_previas,
                           dm.contacto_emergencia_nombre,
                           dm.contacto_emergencia_telefono,
                           dm.contacto_emergencia_parentesco,
                           dm.seguro_medico,
                           dm.club_procedencia
                    FROM atletas a
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    LEFT JOIN atleta_datos_medicos dm ON a.id_atleta = dm.id_atleta
                    ORDER BY a.fecha_creacion DESC";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId(int $id): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT a.*,
                           TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) AS edad,
                           c.nombre AS categoria_nombre,
                           c.id_categoria,
                           dm.grupo_sanguineo, dm.alergias, dm.condiciones_previas,
                           dm.contacto_emergencia_nombre, dm.contacto_emergencia_telefono,
                           dm.contacto_emergencia_parentesco, dm.seguro_medico,
                           dm.numero_feveda, dm.club_procedencia
                    FROM atletas a
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    LEFT JOIN atleta_datos_medicos dm ON a.id_atleta = dm.id_atleta
                    WHERE a.id_atleta = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerCategorias(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT id_categoria, nombre, edad_minima, edad_maxima 
                    FROM categorias_feveda WHERE activa = 1 ORDER BY edad_minima";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

/*     public function listarMenoresSinRepresentante(): array {
    $conex = $this->getConex1();
    try {
       
        $sql = "SELECT a.id_atleta, a.cedula, a.nombres, a.apellidos 
                FROM atletas a
                LEFT JOIN atleta_representante ar ON a.id_atleta = ar.id_atleta
                WHERE TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 18
                  AND ar.id_atleta IS NULL 
                ORDER BY a.nombres ASC";

        $stmt = $conex->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      
        return [];
    }
} */


public function listarMenoresParaRepresentante(int $id_representante = 0): array {
    $conex = $this->pdo;
    try {
        // La consulta permite traer atletas sin representante O que pertenezcan al representante actual
        $sql = "SELECT a.id_atleta, a.cedula, a.nombres, a.apellidos,
                       (CASE WHEN ar.id_representante = :id_rep THEN 1 ELSE 0 END) as seleccionado
                FROM atletas a
                LEFT JOIN atleta_representante ar ON a.id_atleta = ar.id_atleta
                WHERE (ar.id_atleta IS NULL OR ar.id_representante = :id_rep)
                  AND TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 18
                ORDER BY a.nombres ASC";

        $stmt = $conex->prepare($sql);
        $stmt->execute([':id_rep' => $id_representante]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en listarMenoresParaRepresentante: " . $e->getMessage());
        return [];
    }
}

    public function registrarAtleta(array $datos): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO atletas (cedula, nombres, apellidos, fecha_nacimiento, sexo, 
                                        direccion, telefono, correo, foto, fecha_registro_club, 
                                        estado, id_categoria, id_usuario) 
                    VALUES (:cedula, :nombres, :apellidos, :fecha_nac, :sexo, 
                            :direccion, :telefono, :correo, :foto, :fecha_registro, 
                            :estado, :id_categoria, :id_usuario)";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula'         => $datos['cedula'],
                ':nombres'        => $datos['nombres'],
                ':apellidos'      => $datos['apellidos'],
                ':fecha_nac'      => $datos['fecha_nacimiento'],
                ':sexo'           => $datos['sexo'],
                ':direccion'      => $datos['direccion'] ?? null,
                ':telefono'       => $datos['telefono'] ?? null,
                ':correo'         => $datos['correo'] ?? null,
                ':foto'           => $datos['foto'] ?? null,
                ':fecha_registro' => $datos['fecha_registro_club'] ?? null,
                ':estado'         => $datos['estado'] ?? 'Activo',
                ':id_categoria'   => $datos['id_categoria'],
                ':id_usuario'     => $datos['id_usuario'] ?? null
            ]);

            $idAtleta = (int)$conex->lastInsertId();

            $sql2 = "INSERT INTO atleta_datos_medicos (id_atleta, grupo_sanguineo, alergias, 
                        condiciones_previas, contacto_emergencia_nombre, 
                        contacto_emergencia_telefono, contacto_emergencia_parentesco, 
                        seguro_medico, numero_feveda, club_procedencia)
                     VALUES (:id_atleta, :gs, :alergias, :condiciones,
                        :cont_nombre, :cont_telefono, :cont_parentesco,
                        :seguro, :feveda, :club)";
            $stmt2 = $conex->prepare($sql2);
            $stmt2->execute([
                ':id_atleta'      => $idAtleta,
                ':gs'             => $datos['grupo_sanguineo'] ?? null,
                ':alergias'        => $datos['alergias'] ?? null,
                ':condiciones'     => $datos['condiciones_previas'] ?? null,
                ':cont_nombre'     => $datos['contacto_emergencia_nombre'] ?? null,
                ':cont_telefono'   => $datos['contacto_emergencia_telefono'] ?? null,
                ':cont_parentesco' => $datos['contacto_emergencia_parentesco'] ?? null,
                ':seguro'          => $datos['seguro_medico'] ?? null,
                ':feveda'          => $datos['numero_feveda'] ?? null,
                ':club'            => $datos['club_procedencia'] ?? null
            ]);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            return false;
        }
    }

    public function editarAtleta(array $datos): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sql = "UPDATE atletas SET 
                        cedula = :cedula, nombres = :nombres, apellidos = :apellidos, 
                        fecha_nacimiento = :fecha_nac, sexo = :sexo, 
                        direccion = :direccion, telefono = :telefono, correo = :correo, 
                        foto = COALESCE(:foto, foto), fecha_registro_club = :fecha_registro,
                        estado = :estado, id_categoria = :id_categoria, id_usuario = :id_usuario
                    WHERE id_atleta = :id";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula'         => $datos['cedula'],
                ':nombres'        => $datos['nombres'],
                ':apellidos'      => $datos['apellidos'],
                ':fecha_nac'      => $datos['fecha_nacimiento'],
                ':sexo'           => $datos['sexo'],
                ':direccion'      => $datos['direccion'] ?? null,
                ':telefono'       => $datos['telefono'] ?? null,
                ':correo'         => $datos['correo'] ?? null,
                ':foto'           => $datos['foto'] ?? null,
                ':fecha_registro' => $datos['fecha_registro_club'] ?? null,
                ':estado'         => $datos['estado'],
                ':id_categoria'   => $datos['id_categoria'],
                ':id_usuario'     => $datos['id_usuario'] ?? null,
                ':id'             => $datos['id_atleta']
            ]);

            $sqlCheck = "SELECT COUNT(*) FROM atleta_datos_medicos WHERE id_atleta = :id";
            $stmtCheck = $conex->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $datos['id_atleta']]);
            $existe = $stmtCheck->fetchColumn() > 0;

            $medicosParams = [
                ':id_atleta'      => $datos['id_atleta'],
                ':gs'             => $datos['grupo_sanguineo'] ?? null,
                ':alergias'        => $datos['alergias'] ?? null,
                ':condiciones'     => $datos['condiciones_previas'] ?? null,
                ':cont_nombre'     => $datos['contacto_emergencia_nombre'] ?? null,
                ':cont_telefono'   => $datos['contacto_emergencia_telefono'] ?? null,
                ':cont_parentesco' => $datos['contacto_emergencia_parentesco'] ?? null,
                ':seguro'          => $datos['seguro_medico'] ?? null,
                ':feveda'          => $datos['numero_feveda'] ?? null,
                ':club'            => $datos['club_procedencia'] ?? null
            ];

            if ($existe) {
                $sql2 = "UPDATE atleta_datos_medicos SET 
                            grupo_sanguineo = :gs, alergias = :alergias,
                            condiciones_previas = :condiciones,
                            contacto_emergencia_nombre = :cont_nombre,
                            contacto_emergencia_telefono = :cont_telefono,
                            contacto_emergencia_parentesco = :cont_parentesco,
                            seguro_medico = :seguro, numero_feveda = :feveda,
                            club_procedencia = :club
                         WHERE id_atleta = :id_atleta";
            } else {
                $sql2 = "INSERT INTO atleta_datos_medicos (id_atleta, grupo_sanguineo, alergias, 
                            condiciones_previas, contacto_emergencia_nombre, 
                            contacto_emergencia_telefono, contacto_emergencia_parentesco, 
                            seguro_medico, numero_feveda, club_procedencia)
                         VALUES (:id_atleta, :gs, :alergias, :condiciones,
                            :cont_nombre, :cont_telefono, :cont_parentesco,
                            :seguro, :feveda, :club)";
            }

            $stmt2 = $conex->prepare($sql2);
            $stmt2->execute($medicosParams);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            return false;
        }
    }

    public function eliminarAtleta(int $id): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE atletas SET estado = 'Inactivo' WHERE id_atleta = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }



    public function procesarFoto(array $archivo, ?string $fotoActual = null): ?string {
        if ($archivo['error'] === UPLOAD_ERR_NO_FILE || !isset($archivo['tmp_name'])) {
            return $fotoActual;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $this->agregarError('foto', 'Error al subir la imagen');
            return $fotoActual;
        }

        $tiposPermitidos = ['image/jpeg', 'image/png'];
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            $this->agregarError('foto', 'Solo se permiten imágenes JPG o PNG');
            return $fotoActual;
        }

        if ($archivo['size'] > 2 * 1024 * 1024) {
            $this->agregarError('foto', 'La imagen no debe superar los 2MB');
            return $fotoActual;
        }

        $directorio = RAIZ . 'assets/uploads/fotos/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $info = \getimagesize($archivo['tmp_name']);
        if (!$info) {
            $this->agregarError('foto', 'No se pudo leer la imagen');
            return $fotoActual;
        }

        $ancho = $info[0];
        $alto = $info[1];
        $tamano = 300;

        $lienzo = \imagecreatetruecolor($tamano, $tamano);
        $esPng = ($archivo['type'] === 'image/png');

        if ($esPng) {
            $imagen = \imagecreatefrompng($archivo['tmp_name']);
            \imagealphablending($lienzo, false);
            \imagesavealpha($lienzo, true);
            $transparente = \imagecolorallocatealpha($lienzo, 255, 255, 255, 127);
            \imagefill($lienzo, 0, 0, $transparente);
        } else {
            $imagen = \imagecreatefromjpeg($archivo['tmp_name']);
        }

        if (!$imagen) {
            \imagedestroy($lienzo);
            $this->agregarError('foto', 'No se pudo procesar la imagen');
            return $fotoActual;
        }

        $minimo = \min($ancho, $alto);
        $ejeX = (int)(($ancho - $minimo) / 2);
        $ejeY = (int)(($alto - $minimo) / 2);

        \imagecopyresampled($lienzo, $imagen, 0, 0, $ejeX, $ejeY, $tamano, $tamano, $minimo, $minimo);

        $extension = $esPng ? 'png' : 'jpg';
        $nombreArchivo = 'atleta_' . \uniqid() . '.' . $extension;
        $rutaCompleta = $directorio . $nombreArchivo;
        $rutaRelativa = 'assets/uploads/fotos/' . $nombreArchivo;

        if ($esPng) {
            \imagepng($lienzo, $rutaCompleta, 9);
        } else {
            \imagejpeg($lienzo, $rutaCompleta, 85);
        }

        \imagedestroy($lienzo);
        \imagedestroy($imagen);

        if ($fotoActual && file_exists(RAIZ . $fotoActual)) {
            unlink(RAIZ . $fotoActual);
        }

        return $rutaRelativa;
    }
}
