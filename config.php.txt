<?php
$settings = array(
    'db_host' => 'localhost',
    'db_name' => 'hallenbuch',
    'db_user' => 'dein_user',
    'db_pass' => 'dein_pass',
    'db_charset' => 'utf8mb4',
    'debug_mode' => true,
    'app_version' => '1.0.0'
);

if ($settings['debug_mode']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

if (!defined('IN_SCRIPT')) {
    die('Invalid attempt!');
}

function get_db_connection() {
    global $settings;
    $dsn = "mysql:host={$settings['db_host']};dbname={$settings['db_name']};charset={$settings['db_charset']}";
    return new PDO($dsn, $settings['db_user'], $settings['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}
?>

