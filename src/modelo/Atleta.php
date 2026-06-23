<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;
use PDOStatement;

class Atleta extends Conexion {
    use ValidacionesTrait;

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): self {
        $this->datos = $this->sanitizar($datos);
        $this->validar();
        return $this;
    }

    public function getCampo(string $clave, $default = null) {
        return $this->datos[$clave] ?? $default;
    }

    public function setCampo(string $clave, $valor): self {
        $this->datos[$clave] = $valor;
        return $this;
    }

    public function hayErrores(): bool {
        return !empty($this->obtenerErrores());
    }

    private function sanitizar(array $datos): array {
        $sanitizados = [];
        foreach ($datos as $clave => $valor) {
            if (is_string($valor)) {
                $sanitizados[$clave] = trim($valor);
            } else {
                $sanitizados[$clave] = $valor;
            }
        }
        return $sanitizados;
    }

    protected function vincular(PDOStatement $stmt, string $param, $value, int $tipo = PDO::PARAM_STR): void {
        if ($value === null || $value === '') {
            $stmt->bindValue($param, null, PDO::PARAM_NULL);
            return;
        }
        switch ($tipo) {
            case PDO::PARAM_INT:
                $stmt->bindValue($param, (int)$value, PDO::PARAM_INT);
                break;
            case PDO::PARAM_BOOL:
                $stmt->bindValue($param, (bool)$value, PDO::PARAM_BOOL);
                break;
            default:
                $stmt->bindValue($param, $value, PDO::PARAM_STR);
                break;
        }
    }

    private function validar(): array {
        $this->resetearErrores();

        $excluirId = !empty($this->getCampo('id_atleta')) ? (int)$this->getCampo('id_atleta') : null;

        $cedula = $this->getCampo('cedula', '');
        $this->requerido($cedula, 'cedula');
        if ($cedula !== '') {
            $this->cedula($cedula, 'cedula');
        }
        $this->unico($this->pdo, trim($cedula), 'atletas', 'cedula', $excluirId, 'id_atleta');

        $nombres = $this->getCampo('nombres', '');
        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');
        $this->longitud($nombres, 'nombres', 2, 100);

        $apellidos = $this->getCampo('apellidos', '');
        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');
        $this->longitud($apellidos, 'apellidos', 2, 100);

        $fechaNac = $this->getCampo('fecha_nacimiento', '');
        $this->requerido($fechaNac, 'fecha_nacimiento');
        $this->fechaValida($fechaNac, 'fecha_nacimiento');
        $this->fechaNoFutura($fechaNac, 'fecha_nacimiento');

        $sexo = $this->getCampo('sexo', '');
        $this->requerido($sexo, 'sexo');
        $this->enEnum($sexo, 'sexo', ['M', 'F']);

        $estado = $this->getCampo('estado', '');
        $this->requerido($estado, 'estado');
        $this->enEnum($estado, 'estado', ['Activo', 'Inactivo', 'Retirado', 'Transferido']);

        $correo = $this->getCampo('correo', '');
        $this->requerido($correo, 'correo');
        $this->correoValido($correo, 'correo');
        $this->unico($this->pdo, $correo, 'atletas', 'correo', $excluirId, 'id_atleta');

        $telefono = $this->getCampo('telefono', '');
        $this->requerido($telefono, 'telefono');
        $this->telefono($telefono, 'telefono');

        $direccion = $this->getCampo('direccion', '');
        $this->requerido($direccion, 'direccion');
        $this->longitud($direccion, 'direccion', 5, 200);

        $fechaReg = $this->getCampo('fecha_registro_club', '');
        $this->requerido($fechaReg, 'fecha_registro_club');
        $this->fechaValida($fechaReg, 'fecha_registro_club');
        $this->fechaNoFutura($fechaReg, 'fecha_registro_club');

        $grupoSang = $this->getCampo('grupo_sanguineo', '');
        $this->requerido($grupoSang, 'grupo_sanguineo');
        $this->enEnum($grupoSang, 'grupo_sanguineo', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);

        $seguro = $this->getCampo('seguro_medico', '');
        $this->requerido($seguro, 'seguro_medico');
        $this->longitud($seguro, 'seguro_medico', 2, 100);

        $alergias = $this->getCampo('alergias', '');
        $this->requerido($alergias, 'alergias');
        $this->longitud($alergias, 'alergias', 0, 500);

        $condiciones = $this->getCampo('condiciones_previas', '');
        $this->requerido($condiciones, 'condiciones_previas');
        $this->longitud($condiciones, 'condiciones_previas', 0, 500);

        $contNombre = $this->getCampo('contacto_emergencia_nombre', '');
        $this->requerido($contNombre, 'contacto_emergencia_nombre');
        $this->soloLetras($contNombre, 'contacto_emergencia_nombre');
        $this->longitud($contNombre, 'contacto_emergencia_nombre', 2, 100);

        $contTelefono = $this->getCampo('contacto_emergencia_telefono', '');
        $this->requerido($contTelefono, 'contacto_emergencia_telefono');
        $this->telefono($contTelefono, 'contacto_emergencia_telefono');

        $contParentesco = $this->getCampo('contacto_emergencia_parentesco', '');
        $this->requerido($contParentesco, 'contacto_emergencia_parentesco');
        $this->enEnum($contParentesco, 'contacto_emergencia_parentesco', ['Padre', 'Madre', 'Hermano/a', 'Tutor', 'Otro']);

        $this->requerido($this->getCampo('id_categoria', ''), 'id_categoria');

        $feveda = $this->getCampo('numero_feveda', '');
        $this->requerido($feveda, 'numero_feveda');
        $this->longitud($feveda, 'numero_feveda', 1, 50);

        $club = $this->getCampo('club_procedencia', '');
        $this->requerido($club, 'club_procedencia');
        $this->longitud($club, 'club_procedencia', 2, 100);

        return $this->obtenerErrores();
    }

    public function guardar(): bool {

        if (empty($this->datos['token_asistencia'])) {
        // Creamos un hash seguro usando bytes aleatorios
        $token = bin2hex(random_bytes(16)); 
        $this->datos['token_asistencia'] = $token;
        }   


        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO atletas (cedula, nombres, apellidos, fecha_nacimiento, sexo,
                                        direccion, telefono, correo, foto, fecha_registro_club,
                                        estado, token_asistencia, id_categoria, id_usuario)
                    VALUES (:cedula, :nombres, :apellidos, :fecha_nac, :sexo,
                            :direccion, :telefono, :correo, :foto, :fecha_registro,
                            :estado, :token_asistencia, :id_categoria, :id_usuario)";
            $stmt = $conex->prepare($sql);
            $this->vincular($stmt, ':cedula', $this->getCampo('cedula'));
            $this->vincular($stmt, ':nombres', $this->getCampo('nombres'));
            $this->vincular($stmt, ':apellidos', $this->getCampo('apellidos'));
            $this->vincular($stmt, ':fecha_nac', $this->getCampo('fecha_nacimiento'));
            $this->vincular($stmt, ':sexo', $this->getCampo('sexo'));
            $this->vincular($stmt, ':direccion', $this->getCampo('direccion'));
            $this->vincular($stmt, ':telefono', $this->getCampo('telefono'));
            $this->vincular($stmt, ':correo', $this->getCampo('correo'));
            $this->vincular($stmt, ':foto', $this->getCampo('foto'));
            $this->vincular($stmt, ':fecha_registro', $this->getCampo('fecha_registro_club'));
            $this->vincular($stmt, ':estado', $this->getCampo('estado', 'Activo'));
            $stmt->bindValue(':token_asistencia', $this->datos['token_asistencia']);
            $this->vincular($stmt, ':id_categoria', $this->getCampo('id_categoria'), PDO::PARAM_INT);
            $this->vincular($stmt, ':id_usuario', $this->getCampo('id_usuario'), PDO::PARAM_INT);

            $stmt->execute();

            $idAtleta = (int)$conex->lastInsertId();

            $sql2 = "INSERT INTO atleta_datos_medicos (id_atleta, grupo_sanguineo, alergias,
                        condiciones_previas, contacto_emergencia_nombre,
                        contacto_emergencia_telefono, contacto_emergencia_parentesco,
                        seguro_medico, numero_feveda, club_procedencia)
                     VALUES (:id_atleta, :gs, :alergias, :condiciones,
                        :cont_nombre, :cont_telefono, :cont_parentesco,
                        :seguro, :feveda, :club)";
            $stmt2 = $conex->prepare($sql2);
            $this->vincular($stmt2, ':id_atleta', $idAtleta, PDO::PARAM_INT);
            $this->vincular($stmt2, ':gs', $this->getCampo('grupo_sanguineo'));
            $this->vincular($stmt2, ':alergias', $this->getCampo('alergias'));
            $this->vincular($stmt2, ':condiciones', $this->getCampo('condiciones_previas'));
            $this->vincular($stmt2, ':cont_nombre', $this->getCampo('contacto_emergencia_nombre'));
            $this->vincular($stmt2, ':cont_telefono', $this->getCampo('contacto_emergencia_telefono'));
            $this->vincular($stmt2, ':cont_parentesco', $this->getCampo('contacto_emergencia_parentesco'));
            $this->vincular($stmt2, ':seguro', $this->getCampo('seguro_medico'));
            $this->vincular($stmt2, ':feveda', $this->getCampo('numero_feveda'));
            $this->vincular($stmt2, ':club', $this->getCampo('club_procedencia'));
            $stmt2->execute();

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            error_log("Error en Atleta::guardar(): " . $e->getMessage());
            return false;
        }
    }

    public function actualizar(): bool {
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
            $this->vincular($stmt, ':cedula', $this->getCampo('cedula'));
            $this->vincular($stmt, ':nombres', $this->getCampo('nombres'));
            $this->vincular($stmt, ':apellidos', $this->getCampo('apellidos'));
            $this->vincular($stmt, ':fecha_nac', $this->getCampo('fecha_nacimiento'));
            $this->vincular($stmt, ':sexo', $this->getCampo('sexo'));
            $this->vincular($stmt, ':direccion', $this->getCampo('direccion'));
            $this->vincular($stmt, ':telefono', $this->getCampo('telefono'));
            $this->vincular($stmt, ':correo', $this->getCampo('correo'));
            $this->vincular($stmt, ':foto', $this->getCampo('foto'));
            $this->vincular($stmt, ':fecha_registro', $this->getCampo('fecha_registro_club'));
            $this->vincular($stmt, ':estado', $this->getCampo('estado'));
            $this->vincular($stmt, ':id_categoria', $this->getCampo('id_categoria'), PDO::PARAM_INT);
            $this->vincular($stmt, ':id_usuario', $this->getCampo('id_usuario'), PDO::PARAM_INT);
            $this->vincular($stmt, ':id', $this->getCampo('id_atleta'), PDO::PARAM_INT);
            $stmt->execute();

            $sqlCheck = "SELECT COUNT(*) FROM atleta_datos_medicos WHERE id_atleta = :id";
            $stmtCheck = $conex->prepare($sqlCheck);
            $this->vincular($stmtCheck, ':id', $this->getCampo('id_atleta'), PDO::PARAM_INT);
            $stmtCheck->execute();
            $existe = $stmtCheck->fetchColumn() > 0;

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
            $this->vincular($stmt2, ':id_atleta', $this->getCampo('id_atleta'), PDO::PARAM_INT);
            $this->vincular($stmt2, ':gs', $this->getCampo('grupo_sanguineo'));
            $this->vincular($stmt2, ':alergias', $this->getCampo('alergias'));
            $this->vincular($stmt2, ':condiciones', $this->getCampo('condiciones_previas'));
            $this->vincular($stmt2, ':cont_nombre', $this->getCampo('contacto_emergencia_nombre'));
            $this->vincular($stmt2, ':cont_telefono', $this->getCampo('contacto_emergencia_telefono'));
            $this->vincular($stmt2, ':cont_parentesco', $this->getCampo('contacto_emergencia_parentesco'));
            $this->vincular($stmt2, ':seguro', $this->getCampo('seguro_medico'));
            $this->vincular($stmt2, ':feveda', $this->getCampo('numero_feveda'));
            $this->vincular($stmt2, ':club', $this->getCampo('club_procedencia'));
            $stmt2->execute();

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            error_log("Error en Atleta::actualizar(): " . $e->getMessage());
            return false;
        }
    }

    public function eliminar(int $id): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE atletas SET estado = 'Inactivo' WHERE id_atleta = :id";
            $stmt = $conex->prepare($sql);
            $this->vincular($stmt, ':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Atleta::eliminar(): " . $e->getMessage());
            return false;
        }
    }

    public function listar(): array {
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
            error_log("Error en Atleta::listar(): " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetallePorId(int $id): ?array {
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
            $this->vincular($stmt, ':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en Atleta::obtenerDetallePorId(): " . $e->getMessage());
            return null;
        }
    }

    public function obtenerDetallePorIdUSER(int $id): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT a.*,
       TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) AS edad,
       c.nombre AS categoria_nombre,
       c.id_categoria,
       dm.grupo_sanguineo, dm.alergias, dm.condiciones_previas,
       dm.contacto_emergencia_nombre, dm.contacto_emergencia_telefono,
       dm.contacto_emergencia_parentesco, dm.seguro_medico,
       dm.numero_feveda, dm.club_procedencia,
       r.id_representante,
       CONCAT(r.nombres, ' ', r.apellidos) AS representante_nombre,
       r.cedula AS representante_cedula,
       r.telefono_principal AS representante_telefono,
       r.parentesco AS representante_parentesco
        FROM atletas a
        LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
        LEFT JOIN atleta_datos_medicos dm ON a.id_atleta = dm.id_atleta
        LEFT JOIN atleta_representante ar ON a.id_atleta = ar.id_atleta
        LEFT JOIN representantes r ON ar.id_representante = r.id_representante
        WHERE a.id_usuario = :id;";
            $stmt = $conex->prepare($sql);
            $this->vincular($stmt, ':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en Atleta::obtenerDetallePorIdUSER(): " . $e->getMessage());
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
            error_log("Error en Atleta::obtenerCategorias(): " . $e->getMessage());
            return [];
        }
    }

 

    public function listarMenoresParaRepresentante(int $id_representante = 0): array {
        $conex = $this->pdo; 
        try {
           
/*             $sql = "SELECT a.id_atleta, a.cedula, a.nombres, a.apellidos,
                           (CASE WHEN ar.id_representante = :id_rep1 THEN 1 ELSE 0 END) as seleccionado
                    FROM atletas a
                    LEFT JOIN atleta_representante ar ON a.id_atleta = ar.id_atleta AND ar.id_representante = :id_rep2
                    WHERE TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 18
                      AND (
                           a.id_atleta NOT IN (SELECT id_atleta FROM atleta_representante WHERE id_representante != :id_rep3)
                           OR ar.id_representante = :id_rep4
                      )
                    ORDER BY a.nombres ASC"; */

$sql = "SELECT a.id_atleta, a.cedula, a.nombres, a.apellidos,
                   (CASE WHEN ar.id_representante = :id_rep1 THEN 1 ELSE 0 END) as seleccionado,
                   IFNULL(ar.autorizacion_medica, 0) as aut_medica,
                   IFNULL(ar.autorizacion_imagen, 0) as aut_imagen
            FROM atletas a
            LEFT JOIN atleta_representante ar ON a.id_atleta = ar.id_atleta AND ar.id_representante = :id_rep2
            WHERE TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 18
              AND (
                   a.id_atleta NOT IN (SELECT id_atleta FROM atleta_representante WHERE id_representante != :id_rep3)
                   OR ar.id_representante = :id_rep4
              )
            ORDER BY a.nombres ASC";                    
                    
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':id_rep1', $id_representante, \PDO::PARAM_INT);
            $stmt->bindValue(':id_rep2', $id_representante, \PDO::PARAM_INT);
            $stmt->bindValue(':id_rep3', $id_representante, \PDO::PARAM_INT);
            $stmt->bindValue(':id_rep4', $id_representante, \PDO::PARAM_INT);
           
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarMenoresParaRepresentante: " . $e->getMessage());
            return [];
        }
    }

     public function listarMenoresSinRepresentante(): array {
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
} 

/**
     * Obtiene los atletas que asistieron ('Presente') a un entrenamiento específico
     */
    public function obtenerAtletasPorSesion(int $id_sesion): array {
        try {
            // Unimos Atletas con Asistencia y filtramos por los Presentes
            $sql = "SELECT a.id_atleta, a.nombres, a.apellidos, a.cedula 
                    FROM atletas a
                    INNER JOIN asistencia asi ON a.id_atleta = asi.id_atleta
                    WHERE asi.id_sesion = :id_sesion 
                    AND asi.estado = 'Presente'
                    ORDER BY a.nombres ASC";
                    
            $stmt = $this->pdo->prepare($sql);
            $this->vincular($stmt, ':id_sesion', $id_sesion, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al listar atletas por sesión: " . $e->getMessage());
            return []; // Retornamos arreglo vacío en caso de error para no romper el JS
        }
    }

    /**
     * Obtiene los atletas que están inscritos formalmente en un evento
     */
    public function obtenerAtletasPorEvento(int $id_evento): array {
        try {
            // Unimos Atletas con evento_inscripcion
            $sql = "SELECT a.id_atleta, a.nombres, a.apellidos, a.cedula 
                    FROM atletas a
                    INNER JOIN evento_inscripcion ei ON a.id_atleta = ei.id_atleta
                    WHERE ei.id_evento = :id_evento
                    ORDER BY a.nombres ASC";
                    
            $stmt = $this->pdo->prepare($sql);
            $this->vincular($stmt, ':id_evento', $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al listar atletas por evento: " . $e->getMessage());
            return [];
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
