<?php
//session_start();

define('IN_SCRIPT', true);
require_once(__DIR__ . '/config.php');
$header_file = __DIR__ . '/includes/header.php';
if (is_readable($header_file)) {
    require_once $header_file;
}

$base_url = '/hallenbuch/'; 
if (empty($_SESSION['user'])) {
    echo "Session User not set";
//    echo "<div style='text-align:right;'><a href='{$base_url}login.php'>🔐 Admin-Login</a></div>";
    exit;
//} else {
//    echo "<div style='text-align:right;'><a href='{$base_url}logout.php'>🚪 Logout</a></div>";
}
?>