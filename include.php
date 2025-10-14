<?php
session_start();

define('IN_SCRIPT', true);
require_once 'config.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

