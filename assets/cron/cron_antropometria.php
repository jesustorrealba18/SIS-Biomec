<?php
// cron_antropometria.php
require_once __DIR__ . '/../vendor/autoload.php'; // Tu autocargador
use GrupoProyecto\SisBiomec\modelo\Conexion;
use GrupoProyecto\SisBiomec\modelo\Notificacion;

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    $db = new Conexion('sis_natacion');

    $sql="SELECT a.id_atleta, a.nombres, a.apellidos, a.fecha_registro_club, MAX(ma.fecha) as ultima_medicion 
    FROM sis_natacion.atletas a LEFT JOIN sis_natacion.mediciones_antropometricas ma 
    ON a.id_atleta = ma.id_atleta 
    WHERE a.estado = 'Activo' GROUP BY a.id_atleta 
    HAVING DATEDIFF(CURDATE(), ultima_medicion) = 85 
    OR (ultima_medicion IS NULL AND DATEDIFF(CURDATE(), a.fecha_registro_club) = 85);";
                
    $stmt = $db->getConex1()->prepare($sql);
    $stmt->execute();
    $atletas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($atletas as $atleta) {
        $titulo = "Evaluación Antropométrica Próxima";
        $mensaje = "Hola {$atleta['nombres']}, en 5 días corresponde tu próxima medición antropométrica. Por favor, coordina con tu entrenador.";
        
        // ¡Usamos tu método existente que ya hace la magia del ruteo!
        Notificacion::notificarAtletaYRepresentante(
            (int)$atleta['id_atleta'], 
            $titulo, 
            $mensaje, 
            'fa-weight', 
            'blue', 
            '?p=antropometria'
        );
    }
    echo "Cron ejecutado con éxito. Se notificó a " . count($atletas) . " atletas.";
} catch (Exception $e) {
    error_log("Fallo en Cron Antropometría: " . $e->getMessage());
}