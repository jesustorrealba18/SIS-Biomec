<?php
/**
 * Setup de usuarios para pruebas de integracion QA (Postman / smoke).
 * Crea (de forma idempotente):
 *   - qa.pm.admin@qa.test   (rol 1 = Administrador)  -> acceso total
 *   - qa.pm.noperm@qa.test  (sin roles)               -> para pruebas 403
 * Password de ambos: ClaveQA123*
 * Y el fixture malicioso para la prueba de upload (shell.png con codigo PHP).
 */
$pdo = new PDO('mysql:host=localhost;dbname=sis_seguridad;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$hash = password_hash('ClaveQA123*', PASSWORD_BCRYPT);

foreach (['qa.pm.admin@qa.test', 'qa.pm.noperm@qa.test'] as $correo) {
    $pdo->prepare('DELETE FROM usuario_roles WHERE id_usuario IN (SELECT id_usuario FROM usuarios WHERE correo = ?)')->execute([$correo]);
    $pdo->prepare('DELETE FROM notificaciones WHERE id_usuario IN (SELECT id_usuario FROM usuarios WHERE correo = ?)')->execute([$correo]);
    $pdo->prepare('DELETE FROM bitacora WHERE id_usuario IN (SELECT id_usuario FROM usuarios WHERE correo = ?)')->execute([$correo]);
    $pdo->prepare('DELETE FROM usuarios WHERE correo = ?')->execute([$correo]);
}
$pdo->prepare("INSERT INTO usuarios (nombres, apellidos, correo, contrasena_hash) VALUES ('QA','POSTMAN','qa.pm.admin@qa.test',?)")->execute([$hash]);
$idAdmin = (int)$pdo->lastInsertId();
$pdo->prepare('INSERT INTO usuario_roles (id_usuario, id_rol) VALUES (?, 1)')->execute([$idAdmin]);

$pdo->prepare("INSERT INTO usuarios (nombres, apellidos, correo, contrasena_hash) VALUES ('QA','NOPERM','qa.pm.noperm@qa.test',?)")->execute([$hash]);
$idNoperm = (int)$pdo->lastInsertId();

echo "admin id=$idAdmin (rol 1), noperm id=$idNoperm (sin roles)\n";

// Verificar que el rol 1 posee los permisos que exigiran los 4 modulos.
$necesarios = ['atletas.crear', 'atletas.ver', 'testFisico.registrar', 'observacionesTecnicas.registrar', 'periodizacion.generar', 'periodizacion.editar'];
$fila = $pdo->prepare(
    "SELECT GROUP_CONCAT(DISTINCT CONCAT(p.modulo,'.',p.accion)) FROM rol_permisos rp
     JOIN permisos p ON p.id_permiso = rp.id_permiso WHERE rp.id_rol = 1"
);
$fila->execute();
$permisosRol1 = explode(',', (string)$fila->fetchColumn());
$faltan = array_diff($necesarios, $permisosRol1);
echo $faltan === [] ? "rol 1 posee todos los permisos requeridos\n" : "FALTAN PERMISOS en rol 1: " . implode(', ', $faltan) . "\n";

// Fixture malicioso: "PNG" con contenido PHP para la prueba de upload.
$dir = 'C:/xampp/htdocs/SIS-Biomec/docs/qa-postman/fixtures';
if (!is_dir($dir)) { mkdir($dir, 0777, true); }
file_put_contents($dir . '/shell.png', "<?php echo 'QA-SHELL-UPLOAD'; ?>");
echo "fixture shell.png creado\n";
