<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class entrenador extends Conexion {
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
        $genero       = $datos['genero'] ?? '';
        $correo       = $datos['correo'] ?? '';
        $telefono     = $datos['telefono'] ?? '';
        $direccion    = $datos['direccion'] ?? '';
    
        $this->requerido($cedula, 'cedula');
        if ($cedula !== '') {
            $this->cedula($cedula, 'cedula');
        }
        $this->unico($this->getConex1(), trim($cedula), 'entrenador', 'cedula', $excluirId, 'id_entrenador');

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');
        $this->longitud($nombres, 'nombres', 2, 100);

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');
        $this->longitud($apellidos, 'apellidos', 2, 100);

        $this->requerido($fecha, 'fecha_nacimiento');
        $this->fechaValida($fecha, 'fecha_nacimiento');
        $this->fechaNoFutura($fecha, 'fecha_nacimiento');

        $this->requerido($genero, 'genero');
        $this->enEnum($genero, 'genero', ['M', 'F']);

        $this->requerido($correo, 'correo');
        $this->correoValido($correo, 'correo');
        $this->unico($this->getConex1(), $correo, 'entrenador', 'correo', $excluirId, 'id_entrenador');

        $this->requerido($telefono, 'telefono');
        $this->telefono($telefono, 'telefono');

        $this->requerido($direccion, 'direccion');
        $this->longitud($direccion, 'direccion', 5, 200);

        return $this->errores;
    }

    public function listarEntrenador(): array {
        $conex = $this->getConex1();
         try {
            $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad FROM entrenador";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId(int $id): ?array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT * FROM entrenador WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return null; }
    }

    public function registrarEntrenador(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO entrenador (cedula, nombres, apellidos, fecha_nacimiento, genero, correo,
                                            direccion, telefono, foto, id_usuario) 
                    VALUES (:cedula, :nombres, :apellidos, :fecha_nac, :genero, :correo,
                            :direccion, :telefono, :foto, :id_usuario)";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula'         => $datos['cedula'],
                ':nombres'        => $datos['nombres'],
                ':apellidos'      => $datos['apellidos'],
                ':fecha_nac'      => $datos['fecha_nacimiento'],
                ':genero'         => $datos['genero'],
                ':direccion'      => $datos['direccion'] ?? null,
                ':telefono'       => $datos['telefono'] ?? null,
                ':correo'         => $datos['correo'] ?? null,
                ':foto'           => $datos['foto'] ?? null,
                ':id_usuario'     => $datos['id_usuario'] ?? null
            ]);

            $idEntrenador = (int)$conex->lastInsertId();
            $conex->commit();
            return true;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            return false;
        }
    }

    public function editarEntrenador(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "UPDATE entrenador SET 
                        cedula = :cedula, nombres = :nombres, apellidos = :apellidos, 
                        fecha_nacimiento = :fecha_nac, genero = :genero, 
                        direccion = :direccion, telefono = :telefono, correo = :correo, 
                        foto = COALESCE(:foto, foto), id_usuario = :id_usuario
                    WHERE id_entrenador = :id";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula'         => $datos['cedula'],
                ':nombres'        => $datos['nombres'],
                ':apellidos'      => $datos['apellidos'],
                ':fecha_nac'      => $datos['fecha_nacimiento'],
                ':genero'         => $datos['genero'],
                ':direccion'      => $datos['direccion'] ?? null,
                ':telefono'       => $datos['telefono'] ?? null,
                ':correo'         => $datos['correo'] ?? null,
                ':foto'           => $datos['foto'] ?? null,
                ':id_usuario'     => $datos['id_usuario'] ?? null,
                ':id'             => $datos['id_entrenador']
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

    public function eliminarEntrenador($id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "DELETE FROM entrenador WHERE id_entrenador = :id";
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
        $nombreArchivo = 'entrenador_' . \uniqid() . '.' . $extension;
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