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

echo "Iniciando prueba de Selenium para el módulo de Drills...\n";

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

    echo "-> Navegando a Drills...\n";
    $driver->get($urlBase . '?p=drills');
    sleep(3);

    echo "-> Abriendo modal de registro...\n";
    $botonRegistrar = $driver->wait(10)->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'registrar drills')]"))
    );
    $botonRegistrar->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('modalDrills')));
    sleep(2);

    echo "-> Llenando Paso 1: Información Básica...\n";

    $driver->findElement(WebDriverBy::id('nombre'))->sendKeys('Ejercicio Test Auto');

    $driver->findElement(WebDriverBy::id('enfoque_tecnico'))->sendKeys('Mejora de la tecnica de brazada');
   
    $driver->findElement(WebDriverBy::id('descripcion'))->sendKeys('Esta es una descripcion de prueba automatica con Selenium.');

    echo "-> Avanzando al Paso 2...\n";
    $driver->findElement(WebDriverBy::id('btnSiguienteDrill'))->click();
    sleep(1);

    echo "-> Llenando Paso 2: Detalles...\n";
    
    $driver->findElement(WebDriverBy::id('instrucciones'))->sendKeys('Realizar 4 series de 50 metros alternando ritmo fuerte y suave.');

    $driver->findElement(WebDriverBy::id('metraje_sugerido'))->sendKeys('4x50m');

    echo "-> Avanzando al Paso 3...\n";
    $driver->findElement(WebDriverBy::id('btnSiguienteDrill'))->click();
    sleep(1);

    echo "-> Configurando Paso 3...\n";

    echo "-> Guardando el drill...\n";
    $driver->findElement(WebDriverBy::id('btnGuardar'))->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.swal2-popup')));
    
    $titulo = $driver->findElement(WebDriverBy::cssSelector('.swal2-title'))->getText();
    $mensaje = $driver->findElement(WebDriverBy::cssSelector('.swal2-html-container'))->getText();
    
    echo "-> Alerta mostrada: [{$titulo}] - {$mensaje}\n";
    
    if (strpos($titulo, 'Transacción Exitosa') !== false || strpos($titulo, 'Correcto') !== false) {
        echo "PRUEBA EXITOSA: El drill se registró correctamente.\n";
    } else {
        echo "El sistema arrojó un error en el guardado.\n";
    }

} catch (Exception $e) {
    echo "ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
    $driver->takeScreenshot('error_drills.png');
    echo "Captura guardada en 'error_drills.png'\n";
} finally {
    echo "-> Cerrando navegador...\n";
    $driver->quit();
}