<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

require_once 'vista/calendario.php';
