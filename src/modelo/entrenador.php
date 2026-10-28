<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class entrenador extends Conexion {
    use ValidacionesTrait;

    private const CAMPOS_PERMITIDOS = [
        'id_entrenador', 'cedula', 'nombres', 'apellidos',
        'fecha_nacimiento', 'genero', 'correo', 'telefono', 'direccion'
    ];

    private const RUTA_CURRICULUM = 'assets/uploads/curriculums/';
    private const TAMANO_MAX_PDF = 5 * 1024 * 1024; 

    private array $datos = [];
    private ?array $archivoCurriculum = null;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = array_intersect_key($datos, array_flip(self::CAMPOS_PERMITIDOS));
    }

    public function setCurriculum(?array $archivo): void {
        $this->archivoCurriculum = $archivo;
    }

    public function setIdEliminar(int $id): void {
        $this->datos['id_entrenador'] = $id;
    }

    public function getDatos(): array {
        return $this->datos;
    }

    private function validarFormatoCedula(string $cedula): bool {
        return (bool) preg_match('/^[VE]-\d{7,8}$/', $cedula);
    }

    private function validarCurriculum(): array {
        $errores = [];
        if ($this->archivoCurriculum === null ||
            !isset($this->archivoCurriculum['error']) ||
            $this->archivoCurriculum['error'] === UPLOAD_ERR_NO_FILE) {
            return $errores;
        }

        if ($this->archivoCurriculum['error'] !== UPLOAD_ERR_OK) {
            $errores['curriculum'] = 'Error al subir el archivo. Intente nuevamente.';
            return $errores;
        }

        if ($this->archivoCurriculum['size'] > self::TAMANO_MAX_PDF) {
            $errores['curriculum'] = 'El PDF no debe superar los 5MB.';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $this->archivoCurriculum['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            $errores['curriculum'] = 'Solo se permiten archivos PDF.';
        }

        return $errores;
    }

    private function guardarArchivoCurriculum(string $cedula): ?string {
        if ($this->archivoCurriculum === null ||
            !isset($this->archivoCurriculum['error']) ||
            $this->archivoCurriculum['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $directorio = self::RUTA_CURRICULUM;
        if (!is_dir($directorio)) {
            if (!mkdir($directorio, 0755, true) && !is_dir($directorio)) {
                error_log("No se pudo crear el directorio de currículums: " . $directorio);
                return null;
            }
        }

        $cedulaLimpia = preg_replace('/[^A-Za-z0-9]/', '', $cedula);
        $nombreArchivo = 'CV_' . $cedulaLimpia . '_' . time() . '.pdf';
        $rutaCompleta = $directorio . $nombreArchivo;

        if (move_uploaded_file($this->archivoCurriculum['tmp_name'], $rutaCompleta)) {
            return $nombreArchivo;
        }

        error_log("Fallo move_uploaded_file para currículum: " . ($this->archivoCurriculum['name'] ?? 'desconocido'));
        return null;
    }

    private function eliminarArchivoCurriculum(?string $nombreArchivo): void {
        if ($nombreArchivo && file_exists(self::RUTA_CURRICULUM . $nombreArchivo)) {
            @unlink(self::RUTA_CURRICULUM . $nombreArchivo);
        }
    }

    public function validarDatos(?string $excluirCedula = null): array {
        $this->resetearErrores();

        $cedula    = $this->datos['cedula'] ?? '';
        $nombres   = $this->datos['nombres'] ?? '';
        $apellidos = $this->datos['apellidos'] ?? '';
        $fecha     = $this->datos['fecha_nacimiento'] ?? '';
        $genero    = $this->datos['genero'] ?? '';
        $correo    = $this->datos['correo'] ?? '';
        $telefono  = $this->datos['telefono'] ?? '';
        $direccion = $this->datos['direccion'] ?? '';

        $this->requerido($cedula, 'cedula');
        if (!empty($cedula) && !$this->validarFormatoCedula($cedula)) {
            $this->errores['cedula'] = 'Formato inválido. Use V-12345678 o E-1234567 (7 u 8 dígitos).';
        }

        if ($excluirCedula === null) {
            $this->unico($this->getConex1(), $cedula, 'entrenador', 'cedula');
        } else {
            $conex = $this->getConex1();
            $sql = "SELECT COUNT(*) FROM entrenador WHERE cedula = :cedula AND id_entrenador != :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula' => $cedula,
                ':id'     => $this->datos['id_entrenador'] ?? 0
            ]);
            if ($stmt->fetchColumn() > 0) {
                $this->errores['cedula'] = 'La cédula ya está registrada por otro entrenador.';
            }
        }

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');
        $this->longitud($nombres, 'nombres', 2, 50);

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');
        $this->longitud($apellidos, 'apellidos', 2, 50);

        $this->requerido($fecha, 'fecha_nacimiento');
        $this->fechaValida($fecha, 'fecha_nacimiento');
        $this->fechaNoFutura($fecha, 'fecha_nacimiento');
        $this->edadMinima($fecha, 'fecha_nacimiento', 18);
        $this->edadMaxima($fecha, 'fecha_nacimiento', 100);

        $this->requerido($genero, 'genero');
        $this->enEnum($genero, 'genero', ['M', 'F']);

        $this->requerido($correo, 'correo');
        $this->correoValido($correo, 'correo');

        $this->requerido($telefono, 'teléfono');
        $this->soloNumeros($telefono, 'teléfono');
        $this->longitud($telefono, 'teléfono', 11, 11);

        $this->requerido($direccion, 'direccion');
        $this->longitud($direccion, 'direccion', 2, 50);

        $erroresCurriculum = $this->validarCurriculum();
        if (!empty($erroresCurriculum)) {
            $this->errores = array_merge($this->errores, $erroresCurriculum);
        }

        return $this->obtenerErrores();
    }

    public function registrarEntrenador(): bool {
        $errores = $this->validarDatos();
        if (!empty($errores)) {
            return false;
        }
        return $this->registrarEntrenadorP();
    }

    public function editarEntrenador(): bool {
        if (empty($this->datos['id_entrenador'])) {
            return false;
        }
        $errores = $this->validarDatos($this->datos['cedula'] ?? null);
        if (!empty($errores)) {
            return false;
        }
        return $this->editarEntrenadorP();
    }

    public function eliminarEntrenador(int $id): bool {
        if ($id <= 0) {
            return false;
        }
        return $this->eliminarEntrenadorP($id);
    }

    public function reactivarEntrenador(int $id): bool {
        if ($id <= 0) {
            return false;
        }
        return $this->reactivarEntrenadorP($id);
    }

    public function listarEntrenador(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_entrenador, cedula, nombres, apellidos, fecha_nacimiento,
                           genero, correo, telefono, direccion,
                           curriculum_pdf, curriculum_nombre_original, estado
                    FROM entrenador";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando Entrenadores: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $id_entrenador): ?array {
        if ($id_entrenador <= 0) {
            return null;
        }
        $conex = $this->getConex1();
        try {
            $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad
                    FROM entrenador
                    WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_entrenador]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerCurriculum(int $id_entrenador): ?array {
        if ($id_entrenador <= 0) {
            return null;
        }

        $conex = $this->getConex1();
        try {
            $sql = "SELECT curriculum_pdf, curriculum_nombre_original, cedula
                    FROM entrenador
                    WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_entrenador]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || empty($row['curriculum_pdf'])) {
                return null;
            }

            $ruta = self::RUTA_CURRICULUM . $row['curriculum_pdf'];
            if (!file_exists($ruta)) {
                error_log("Currículum no encontrado en disco: " . $ruta);
                return null;
            }

            return [
                'ruta'            => $ruta,
                'nombre_original' => $row['curriculum_nombre_original']
                                     ?? ('CV_' . $row['cedula'] . '.pdf'),
                'archivo'         => $row['curriculum_pdf'],
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener currículum: " . $e->getMessage());
            return null;
        }
    }

    private function registrarEntrenadorP(): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $nombrePdf      = $this->guardarArchivoCurriculum($this->datos['cedula'] ?? '');
            $nombreOriginal = ($nombrePdf !== null && isset($this->archivoCurriculum['name']))
                              ? $this->archivoCurriculum['name']
                              : null;

            $sql = "INSERT INTO entrenador (
                        cedula, nombres, apellidos, fecha_nacimiento, genero,
                        correo, telefono, direccion,
                        curriculum_pdf, curriculum_nombre_original, id_usuario
                    ) VALUES (
                        :cedula, :nombres, :apellidos, :fecha_nacimiento, :genero,
                        :correo, :telefono, :direccion,
                        :curriculum_pdf, :curriculum_nombre, :id_usuario
                    )";

            $stmt = $conex->prepare($sql);

            $stmt->execute([
                ':cedula'             => $this->datos['cedula'] ?? '',
                ':nombres'            => $this->datos['nombres'] ?? '',
                ':apellidos'          => $this->datos['apellidos'] ?? '',
                ':fecha_nacimiento'   => $this->datos['fecha_nacimiento'] ?? '',
                ':genero'             => $this->datos['genero'] ?? '',
                ':correo'             => $this->datos['correo'] ?? '',
                ':telefono'           => $this->datos['telefono'] ?? '',
                ':direccion'          => $this->datos['direccion'] ?? '',
                ':curriculum_pdf'     => $nombrePdf,
                ':curriculum_nombre'  => $nombreOriginal,
                ':id_usuario'         => null,
            ]);

            $this->datos['id_entrenador'] = (int)$conex->lastInsertId();

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error BD registrar Entrenador: " . $e->getMessage());
            return false;
        }
    }

    private function editarEntrenadorP(): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();
            $id_entrenador = (int)($this->datos['id_entrenador'] ?? 0);

            $stmtOld = $conex->prepare("SELECT curriculum_pdf FROM entrenador WHERE id_entrenador = :id");
            $stmtOld->execute([':id' => $id_entrenador]);
            $pdfAnterior = $stmtOld->fetchColumn();

            $hayNuevoPdf = $this->archivoCurriculum !== null
                           && isset($this->archivoCurriculum['error'])
                           && $this->archivoCurriculum['error'] === UPLOAD_ERR_OK;

            $nombrePdf      = null;
            $nombreOriginal = null;

            if ($hayNuevoPdf) {
                $nombrePdf = $this->guardarArchivoCurriculum($this->datos['cedula'] ?? '');
                $nombreOriginal = $this->archivoCurriculum['name'] ?? null;

                if ($nombrePdf !== null && $pdfAnterior) {
                    $this->eliminarArchivoCurriculum($pdfAnterior);
                }
            }

            $sql = "UPDATE entrenador SET
                        cedula = :cedula,
                        nombres = :nombres,
                        apellidos = :apellidos,
                        fecha_nacimiento = :fecha_nacimiento,
                        genero = :genero,
                        correo = :correo,
                        telefono = :telefono,
                        direccion = :direccion";

            $params = [
                ':cedula'           => $this->datos['cedula'] ?? '',
                ':nombres'          => $this->datos['nombres'] ?? '',
                ':apellidos'        => $this->datos['apellidos'] ?? '',
                ':fecha_nacimiento' => $this->datos['fecha_nacimiento'] ?? '',
                ':genero'           => $this->datos['genero'] ?? '',
                ':correo'           => $this->datos['correo'] ?? '',
                ':telefono'         => $this->datos['telefono'] ?? '',
                ':direccion'        => $this->datos['direccion'] ?? '',
                ':id_entrenador'    => $id_entrenador,
            ];

            if ($nombrePdf !== null) {
                $sql .= ", curriculum_pdf = :curriculum_pdf,
                          curriculum_nombre_original = :curriculum_nombre";
                $params[':curriculum_pdf']    = $nombrePdf;
                $params[':curriculum_nombre'] = $nombreOriginal;
            }

            $sql .= " WHERE id_entrenador = :id_entrenador";

            $stmt = $conex->prepare($sql);
            $status = $stmt->execute($params);

            $conex->commit();
            return $status;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en editarEntrenador: " . $e->getMessage());
            return false;
        }
    }

    private function eliminarEntrenadorP(int $id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "UPDATE entrenador SET estado = 'Inactivo' WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al desactivar entrenador: " . $e->getMessage());
            return false;
        }
    }

    private function reactivarEntrenadorP(int $id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "UPDATE entrenador SET estado = 'Activo' WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al reactivar entrenador: " . $e->getMessage());
            return false;
        }
    }
}