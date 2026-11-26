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

echo "Iniciando prueba de Selenium para el módulo de Entrenadores...\n";

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

    echo "-> Navegando a Entrenadores...\n";
    $driver->get($urlBase . '?p=entrenador');
    sleep(3);

    echo "-> Abriendo modal de registro...\n";
    $driver->wait(10)->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'registrar entrenador')]"))
    )->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('modalEntrenador')));
    sleep(2);

    echo "-> Llenando formulario de entrenador...\n";
   
    $selectPrefijo = new WebDriverSelect($driver->findElement(WebDriverBy::id('cedula_prefijo')));
    $selectPrefijo->selectByValue('V');
    
    $cedulaNumero = rand(1000000, 9999999);
    $driver->findElement(WebDriverBy::id('cedula_numero'))->sendKeys((string)$cedulaNumero);
    
    $driver->findElement(WebDriverBy::id('nombres'))->sendKeys('Carlos Alberto');
    $driver->findElement(WebDriverBy::id('apellidos'))->sendKeys('Mendoza Perez');
    
    echo "-> Estableciendo fecha de nacimiento...\n";
    $driver->executeScript("document.getElementById('fecha_nacimiento').value = '1985-05-20';");
    
    $selectGenero = new WebDriverSelect($driver->findElement(WebDriverBy::id('genero')));
    $selectGenero->selectByValue('M');
 
    $driver->findElement(WebDriverBy::id('telefono'))->sendKeys('04121234567');
  
    $driver->findElement(WebDriverBy::id('correo'))->sendKeys('carlos.test@sgrd.com');
    
    $driver->findElement(WebDriverBy::id('direccion'))->sendKeys('Calle Principal, Residencias El Sol');

    echo "-> Guardando el entrenador...\n";
    $driver->findElement(WebDriverBy::id('btnGuardar'))->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.swal2-popup')));
    
    $titulo = $driver->findElement(WebDriverBy::cssSelector('.swal2-title'))->getText();
    $mensaje = $driver->findElement(WebDriverBy::cssSelector('.swal2-html-container'))->getText();
    
    echo "-> Alerta mostrada: [{$titulo}] - {$mensaje}\n";
    
    if (strpos($titulo, 'Transacción Exitosa') !== false || strpos($titulo, 'Correcto') !== false) {
        echo "PRUEBA EXITOSA: El entrenador fue registrado correctamente.\n";
    } else {
        echo "El sistema arrojó un error en el guardado.\n";
    }

} catch (Exception $e) {
    echo "ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
    $driver->takeScreenshot('error_entrenador.png');
    echo "Captura guardada en 'error_entrenador.png'\n";
} finally {
    echo "-> Cerrando navegador...\n";
    $driver->quit();
}