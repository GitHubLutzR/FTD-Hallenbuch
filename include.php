<!-- DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>FTD Hallenbuch</title>
  <link rel="stylesheet" href="assets/style.css">
  <meta charset="utf-8">
  <title>FTD Hallenbuch</title>
  <style>
    .logout {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 0.9em;
      text-align: right;
      z-index: 1000;
    }
  </style>
</head>
<body -->
<?php
session_start();

define('IN_SCRIPT', true);
require_once(__DIR__ . '/config.php');
$base_url = '/hallenbuch/'; 
if (empty($_SESSION['user'])) {
    header('Location: ' . $base_url . 'login.php');
    require_once(__DIR__ . '/footer.php');
    exit;
} else {
     echo "<div style='text-align:right;'><a href='{$base_url}logout.php'>logout</a></div>";
}   
?>