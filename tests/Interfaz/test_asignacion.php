<?php
require __DIR__ . '/../../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

$serverUrl   = 'http://localhost:4444';
$urlBase     = 'http://localhost/SIS-Biomec/'; 
$usuario     = 'admin@sgrd.com'; 
$contrasena  = 'admin123';       

echo "Iniciando prueba de Selenium para el módulo de Asignaciones...\n";

$driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::chrome());
$driver->manage()->window()->maximize();

try {
    echo "-> Navegando al login...\n";
    $driver->get($urlBase . '?p=login');
    
    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::name('usuario')));
    $driver->findElement(WebDriverBy::name('usuario'))->sendKeys($usuario);
    $driver->findElement(WebDriverBy::name('password'))->sendKeys($contrasena);
    $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
    
    $driver->wait(10)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("//*[contains(text(), 'Inicio')]")));
    echo "-> Login exitoso.\n";

    echo "-> Navegando a Asignaciones...\n";
    $driver->get($urlBase . '?p=asignacion');
    sleep(3); 
    echo "-> Abriendo modal de nueva asignación...\n";
    $driver->wait(10)->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'nueva asignación')]"))
    )->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('modalAsignacion')));
    sleep(2); 
    echo "-> Llenando datos generales...\n";

    $selectBloque = new WebDriverSelect($driver->findElement(WebDriverBy::id('id_bloque_horario')));
    if ($selectBloque->getOptions() > 1) {
        $selectBloque->selectByIndex(1); 
    } else {
        throw new Exception("No hay bloques horarios disponibles.");
    }
    
    $selectGrupo = new WebDriverSelect($driver->findElement(WebDriverBy::id('id_grupo')));
    if ($selectGrupo->getOptions() > 1) {
        $selectGrupo->selectByIndex(1); 
    } else {
        throw new Exception("No hay grupos disponibles.");
    }

    echo "-> Estableciendo fechas...\n";
    $fechaHoy = date('Y-m-d');
    $fechaMes = date('Y-m-d', strtotime('+1 month'));
    
    $driver->executeScript("
        document.getElementById('fecha_vigente_inicio').value = '{$fechaHoy}';
        document.getElementById('fecha_vigente_fin').value = '{$fechaMes}';
        // Disparamos el evento change para que el mapa de la piscina se actualice
        document.getElementById('fecha_vigente_inicio').dispatchEvent(new Event('change'));
    ");

    echo "-> Esperando que cargue el mapa de la piscina...\n";
   
    $driver->wait(10)->until(
        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.carril-card'))
    );
    sleep(1); 

    echo "-> Seleccionando un carril...\n";
    $carrilesLibres = $driver->findElements(WebDriverBy::cssSelector('.carril-card:not(.ocupado)'));
    
    if (count($carrilesLibres) > 0) {
        $carrilesLibres[0]->click(); 
    } else {
        throw new Exception("Todos los carriles están ocupados para este horario.");
    }

    echo "-> Guardando la asignación...\n";
    $driver->findElement(WebDriverBy::id('btnGuardar'))->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.swal2-popup')));
    
    $titulo = $driver->findElement(WebDriverBy::cssSelector('.swal2-title'))->getText();
    $mensaje = $driver->findElement(WebDriverBy::cssSelector('.swal2-html-container'))->getText();
    
    echo "-> Alerta mostrada: [{$titulo}] - {$mensaje}\n";
    
    if (strpos($titulo, 'Transacción Exitosa') !== false || strpos($titulo, 'Correcto') !== false) {
        echo "PRUEBA EXITOSA: La asignación se guardó correctamente.\n";
    } else {
        echo "El sistema arrojó un error en el guardado.\n";
    }

} catch (Exception $e) {
    echo "ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
    $driver->takeScreenshot('error_asignacion.png');
    echo "Captura guardada en 'error_asignacion.png'\n";
} finally {
    echo "-> Cerrando navegador...\n";
    $driver->quit();
}