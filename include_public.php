<?php
session_start();

define('IN_SCRIPT', true);
require_once(__DIR__ . '/config.php');
$base_url = '/hallenbuch/'; 
if (empty($_SESSION['user'])) {
    echo "Session User not set";
    echo "<div style='text-align:right;'><a href='{$base_url}login.php'>🔐 Admin-Login</a></div>";
} else {
    echo "<div style='text-align:right;'><a href='{$base_url}logout.php'logout</a></div>";
}
?>