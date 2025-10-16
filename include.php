<?php
session_start();

define('IN_SCRIPT', true);
require_once(__DIR__ . '/../config.php');
$base_url = '/hallenbuch/'; 
if (empty($_SESSION['user'])) {
    header('Location: ' . $base_url . 'login.php');
    require_once(__DIR__ . '/footer.php');
    exit;
}

