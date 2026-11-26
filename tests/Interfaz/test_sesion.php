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

echo "Iniciando prueba de Selenium...\n";

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

    echo "-> Navegando a Sesiones...\n";
    $driver->get($urlBase . '?p=sesiones');
    sleep(3); 

    echo "-> Buscando el botón 'Registrar Entrenamiento'...\n";
    $botonRegistrar = $driver->wait(10)->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'registrar entrenamiento')]"))
    );

    echo "-> Abriendo modal de planificación...\n";
    $botonRegistrar->click();
    
    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('modalSesion')));
    sleep(2); 

    echo "-> Llenando datos generales...\n";
    
    $selectEntrenador = new WebDriverSelect($driver->findElement(WebDriverBy::id('id_entrenador')));
    if ($selectEntrenador->getOptions() > 1) $selectEntrenador->selectByIndex(1); 
    
    sleep(3); 
    
    $selectGrupo = new WebDriverSelect($driver->findElement(WebDriverBy::id('id_grupo')));
    if ($selectGrupo->getOptions() > 1) $selectGrupo->selectByIndex(1); 
    
    echo "-> Forzando fecha de mañana con JavaScript...\n";
    $fechaManana = date('Y-m-d', strtotime('+1 day'));
  
    $driver->executeScript("document.getElementById('fecha').value = '{$fechaManana}';");

    $selectTipo = new WebDriverSelect($driver->findElement(WebDriverBy::id('tipo_sesion')));
    $selectTipo->selectByValue('Resistencia');
    
    $driver->findElement(WebDriverBy::id('duracion_minutos'))->sendKeys('90');
    
    $driver->findElement(WebDriverBy::id('observaciones'))->sendKeys('Prueba automatizada.');

    echo "-> Avanzando al paso 2...\n";
    $driver->findElement(WebDriverBy::id('btnSiguienteSesion'))->click();
    
    $driver->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('tab-series')));
    sleep(1); 

    echo "-> Llenando datos de la serie...\n";
    
    $fila = $driver->findElement(WebDriverBy::cssSelector('.fila-serie'));
    
    $selectBloque = new WebDriverSelect($fila->findElement(WebDriverBy::cssSelector('.bloque-select')));
    $selectBloque->selectByValue('Principal');
    
    $fila->findElement(WebDriverBy::cssSelector('.desc-input'))->sendKeys('Sprint 50m crol');
    $fila->findElement(WebDriverBy::cssSelector('.rep-input'))->clear()->sendKeys('4');
    $fila->findElement(WebDriverBy::cssSelector('.dist-input'))->clear()->sendKeys('50');
    $fila->findElement(WebDriverBy::cssSelector('input[name="serie_descanso_seg[]"]'))->clear()->sendKeys('30');
    
    $selectZona = new WebDriverSelect($fila->findElement(WebDriverBy::cssSelector('select[name="serie_zona_intensidad[]"]')));
    $selectZona->selectByValue('Z4');

    echo "-> Guardando la planificación...\n";
    $driver->findElement(WebDriverBy::id('btnGuardarSesion'))->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.swal2-popup')));
    
    $titulo = $driver->findElement(WebDriverBy::cssSelector('.swal2-title'))->getText();
    $mensaje = $driver->findElement(WebDriverBy::cssSelector('.swal2-html-container'))->getText();
    
    echo "-> Alerta mostrada: [{$titulo}] - {$mensaje}\n";
    
    if (strpos($titulo, 'Correcto') !== false || strpos($titulo, 'Éxito') !== false) {
        echo "PRUEBA EXITOSA: La sesión se guardó correctamente.\n";
    } else {
        echo "El sistema arrojó un error en el guardado.\n";
    }

} catch (Exception $e) {
    echo "ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
    $driver->takeScreenshot('error_sesion.png');
} finally {
    echo "-> Cerrando navegador...\n";
    $driver->quit();
}