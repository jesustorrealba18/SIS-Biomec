<?php

require_once __DIR__ . '/vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(__DIR__)->load();

session_start();

use GrupoProyecto\SisBiomec\seguridad\Captcha;

$captcha = new Captcha();
$captcha->generar();
