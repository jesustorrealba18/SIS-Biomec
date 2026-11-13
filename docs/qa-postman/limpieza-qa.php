<?php
/**
 * Limpieza de datos creados por las corridas QA de Postman (newman).
 * Marcadores: correos/textos/nombres con prefijo 'qapm' / 'QAPM'.
 */
$nat = new PDO('mysql:host=localhost;dbname=sis_natacion;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$seg = new PDO('mysql:host=localhost;dbname=sis_seguridad;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// TestFisico: registros + valores (sin FK en valores_test)
$ids = $nat->query("SELECT id_registro_test FROM registros_test WHERE observaciones LIKE 'QAPM%'")->fetchAll(PDO::FETCH_COLUMN);
if ($ids) {
    $m = implode(',', array_fill(0, count($ids), '?'));
    $nat->prepare("DELETE FROM valores_test WHERE id_registro_test IN ($m)")->execute($ids);
    $nat->prepare("DELETE FROM registros_test WHERE id_registro_test IN ($m)")->execute($ids);
}
// Observaciones
$nat->exec("DELETE FROM observaciones_tecnicas WHERE observacion_texto LIKE 'QAPM%'");
// Periodizacion (cascade a fases -> mesos -> micros)
$nat->exec("DELETE FROM macrociclos WHERE nombre LIKE 'QAPM-%'");
// Atletas (cascade dm)
$nat->exec("DELETE FROM atletas WHERE correo LIKE 'qapm%'");
// Catalogos accidentales
$nat->exec("DELETE FROM variables_test WHERE id_tipo_test IN (SELECT id_tipo_test FROM tipos_test_predefinidos WHERE nombre LIKE 'QAPM%')");
$nat->exec("DELETE FROM tipos_test_predefinidos WHERE nombre LIKE 'QAPM%'");
$nat->exec("DELETE FROM variables_test WHERE id_test_pers IN (SELECT id_test_pers FROM tests_personalizados WHERE nombre LIKE 'QAPM%')");
$nat->exec("DELETE FROM tests_personalizados WHERE nombre LIKE 'QAPM%'");

// Seguridad: rastros de los usuarios QA
$seg->exec("DELETE FROM notificaciones WHERE id_usuario IN (SELECT id_usuario FROM usuarios WHERE correo LIKE 'qa.pm.%')");
$seg->exec("DELETE FROM bitacora WHERE id_usuario IN (SELECT id_usuario FROM usuarios WHERE correo LIKE 'qa.pm.%')");
$seg->exec("DELETE FROM intentos_login WHERE correoIntento LIKE 'qa.pm.%'");

echo "Limpieza Postman completada\n";
