<?php
use GrupoProyecto\SisBiomec\modelo\Conexion;

class Login extends Conexion {
    public function __construct() {
        parent::__construct();
    }

    public function validarUsuario($usuario, $password) {
    $conex = $this->getConex1();
    try {
        // Buscamos 'nombre' porque así se llama en tu tabla de la imagen
        $sql = "SELECT id_usuario, nombre, clave, rol FROM usuarios WHERE nombre = :user";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':user' => $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // IMPORTANTE: En tu imagen la clave es 060705 (texto plano)
        // Usamos trim() para eliminar espacios accidentales
        if ($user && trim($password) === trim($user['clave'])) {
            return $user;
        }
        return false;
    } catch (PDOException $e) {
        return false;
    }
}
}
?>