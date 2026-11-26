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

echo "Iniciando prueba de Selenium para el módulo de Horarios...\n";

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

    echo "-> Navegando a Horarios...\n";
    $driver->get($urlBase . '?p=horario');
    sleep(3);

    echo "-> Abriendo modal de registro...\n";
    $driver->wait(10)->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'nuevo horario')]"))
    )->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('modalHorario')));
    sleep(1);

    echo "-> Llenando formulario de horario...\n";
   
    $selectDia = new WebDriverSelect($driver->findElement(WebDriverBy::id('dia_semana')));
    $selectDia->selectByValue('Martes');

    $driver->executeScript("document.getElementById('hora_inicio').value = '10:00';");
    $driver->executeScript("document.getElementById('hora_fin').value = '11:30';");

    echo "-> Guardando el horario...\n";
    $driver->findElement(WebDriverBy::id('btnGuardar'))->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.swal2-popup')));
    
    $titulo = $driver->findElement(WebDriverBy::cssSelector('.swal2-title'))->getText();
    $mensaje = $driver->findElement(WebDriverBy::cssSelector('.swal2-html-container'))->getText();
    
    echo "-> Alerta mostrada: [{$titulo}] - {$mensaje}\n";
    
    if (strpos($titulo, 'Transacción Exitosa') !== false || strpos($titulo, 'Correcto') !== false) {
        echo "PRUEBA EXITOSA: El horario fue registrado correctamente.\n";
    } else {
        echo "El sistema arrojó un error en el guardado. (Posible solapamiento de horario)\n";
    }

} catch (Exception $e) {
    echo "ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
    $driver->takeScreenshot('error_horario.png');
    echo "Captura guardada en 'error_horario.png'\n";
} finally {
    echo "-> Cerrando navegador...\n";
    $driver->quit();
}