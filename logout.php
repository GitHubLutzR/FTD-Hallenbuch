<?php
session_start();
session_destroy();
header('Location: index.php');
#header('Location: login.php');

