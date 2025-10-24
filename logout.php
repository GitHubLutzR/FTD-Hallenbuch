<?php
// Konfiguration einbinden (setzt ggf. Session-Name / cookie-Parameter und startet Session)
require_once __DIR__ . '/config.php';

// Stelle sicher, dass die Session aktiv ist
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Alle Session-Daten löschen
$_SESSION = [];

// Cookie löschen (verwende die gleichen Parameter wie die aktuelle Session)
$params = session_get_cookie_params();
setcookie(
    session_name(),
    '',
    time() - 42000,
    $params['path'] ?? '/',
    $params['domain'] ?? '',
    $params['secure'] ?? false,
    $params['httponly'] ?? true
);

// Session vollständig zerstören
session_unset();
session_destroy();

// Redirect
header('Location: index.php');
exit;
?>

