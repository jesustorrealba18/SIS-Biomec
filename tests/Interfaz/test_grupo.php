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

echo "Iniciando prueba de Selenium para el módulo de Grupos...\n";

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

    echo "-> Navegando a Grupos...\n";
    $driver->get($urlBase . '?p=grupo');
    sleep(3); 

    echo "-> Abriendo modal de creación de grupo...\n";
    $driver->wait(10)->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'nuevo grupo')]"))
    )->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('modalGrupo')));
    sleep(2); 

    echo "-> Llenando formulario de grupo...\n";
    $nombreGrupo = 'Grupo Test ' . date('His');
    
    $driver->findElement(WebDriverBy::id('nombre'))->sendKeys($nombreGrupo);
    
    $selectEntrenador = new WebDriverSelect($driver->findElement(WebDriverBy::id('id_entrenador')));
    if ($selectEntrenador->getOptions() > 1) {
        $selectEntrenador->selectByIndex(1); 
    } else {
        throw new Exception("No hay entrenadores registrados.");
    }

    $driver->findElement(WebDriverBy::id('descripcion'))->sendKeys('Grupo de prueba Selenium.');
    $driver->findElement(WebDriverBy::id('btnGuardar'))->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.swal2-popup')));
    $titulo = $driver->findElement(WebDriverBy::cssSelector('.swal2-title'))->getText();
    
    if (strpos($titulo, 'Transacción Exitosa') !== false || strpos($titulo, 'Correcto') !== false) {
        echo "Grupo '{$nombreGrupo}' creado correctamente.\n";
    } else {
        throw new Exception("Fallo al crear el grupo. Mensaje: {$titulo}");
    }

    $driver->findElement(WebDriverBy::cssSelector('.swal2-confirm'))->click();
    sleep(2); 

    echo "-> Buscando el grupo '{$nombreGrupo}' en la tabla...\n";
    
    $xpathBotonAsignar = "//tr[contains(., '{$nombreGrupo}')]//button[@title='Asignar Atletas']";
    
    $driver->wait(10)->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath($xpathBotonAsignar))
    )->click();

    echo "-> Abriendo modal de asignación...\n";
    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('modalAsignacion')));
    sleep(3); 

    echo "-> Seleccionando un atleta...\n";
    $checkboxesAtletas = $driver->findElements(WebDriverBy::cssSelector('input[name="atletas[]"]'));
    
    if (count($checkboxesAtletas) > 0) {
        if (!$checkboxesAtletas[0]->isSelected()) {
            $checkboxesAtletas[0]->click();
        }
    } else {
        throw new Exception("No hay atletas disponibles para asignar.");
    }

    echo "-> Guardando asignación...\n";
    $driver->findElement(WebDriverBy::id('btnAsignar'))->click();

    $driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.swal2-popup')));
    
    $tituloAsignacion = $driver->findElement(WebDriverBy::cssSelector('.swal2-title'))->getText();
    $mensajeAsignacion = $driver->findElement(WebDriverBy::cssSelector('.swal2-html-container'))->getText();
    
    echo "-> Alerta mostrada: [{$tituloAsignacion}] - {$mensajeAsignacion}\n";
    
    if (strpos($tituloAsignacion, 'Asignación Exitosa') !== false || strpos($tituloAsignacion, 'Correcto') !== false) {
        echo "PRUEBA TOTALMENTE EXITOSA: Grupo creado y atleta asignado correctamente.\n";
    } else {
        echo "El grupo se creó, pero falló la asignación del atleta.\n";
    }

} catch (Exception $e) {
    echo "ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
    $driver->takeScreenshot('error_grupo.png');
    echo "Captura guardada en 'error_grupo.png'\n";
} finally {
    echo "-> Cerrando navegador...\n";
    $driver->quit();
}