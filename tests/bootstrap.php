<?php
// tests/bootstrap.php

// 1. Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Definir la constante RAIZ (igual que en index.php)
define('RAIZ', str_replace('\\', '/', dirname(__DIR__)) . '/');

// 3. Cargar las variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(RAIZ);
$dotenv->load(); // Usamos load() en lugar de safeLoad() para que lance error si no encuentra el .env