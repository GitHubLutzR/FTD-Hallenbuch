<?php
session_start();

define('IN_SCRIPT', true);
require_once(__DIR__ . '/config.php');
$base_url = '/hallenbuch/'; 
if (empty($_SESSION['user'])) {
    echo "Session User not set";
}
?>