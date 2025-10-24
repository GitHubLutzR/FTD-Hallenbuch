<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- changed code: eindeutigen Session-Namen pro Instanz setzen und Session einmalig starten
// Bestimme Host / Instanz-Bezeichner
$host_ident = $_SERVER['HTTP_HOST'] ?? (getenv('HOSTNAME') ?: 'local');
$host_ident = preg_replace('/[^a-z0-9_]/i', '_', $host_ident);
$session_name = 'HB_' . substr($host_ident, 0, 32);

// sichere Cookie-Parameter (müssen vor session_start gesetzt werden)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
session_name($session_name);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
$lifetime = 3600; // Sekunden, z.B. 1 Stunde

// Stelle sicher: session_set_cookie_params(...) und session_start() bereits aufgerufen
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Sliding timeout: prüfen und aktualisieren
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $lifetime)) {
    // Inaktiv zu lange -> ausloggen
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    session_destroy();
} else {
    // aktiv -> Timestamp und Cookie-Expiry erneuern (macht die Lifetime "sliding")
    $_SESSION['LAST_ACTIVITY'] = time();
    $params = session_get_cookie_params();
    setcookie(session_name(), session_id(), time() + $lifetime, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

if (!defined('IN_SCRIPT')) {
    define('IN_SCRIPT', 1); // HESK erwartet diese Konstante
}
// HESK-Konfiguration einbinden
//require_once '/srv/hesk_settings.inc.php';
$hesk_cfg_path = '/srv/hesk_settings.inc.php';
if (is_readable($hesk_cfg_path)) {
    require_once $hesk_cfg_path;
    #echo "<p>✅ Konfig-Datei geladen: $hesk_cfg_path</p>";
} else {
    error_log("FEHLER: Konfig-Datei nicht gefunden: $hesk_cfg_path");
    // optional: versuchen, über include_path zu finden
    if ($alt = stream_resolve_include_path('hesk_settings.inc.php')) {
        require_once $alt;
    } else {
        // Abbruch mit klarer Fehlermeldung (oder setze Fallback-Werte)
        trigger_error("Konfigurationsdatei $hesk_cfg_path fehlt. Abbruch.", E_USER_ERROR);
        // alternativ statt Abbruch:
        // $hesk_settings = []; $hesk_settings['debug'] = true;
    }
}
// Hallenbuch-spezifischer Tabellenprefix
$hesk_settings['db_hb_pfix'] = 'hb_';
$hesk_settings['debug'] = false; // oder false zum Abschalten
date_default_timezone_set('Europe/Berlin');
// Datenbankverbindung bereitstellen
if (!function_exists('get_db_connection')) {
    function get_db_connection(): mysqli {
        global $hesk_settings;

//        echo "<p>🔍 get_db_connection() gestartet</p>";
//        echo "<p>Host: {$hesk_settings['db_host']}</p>";
//        echo "<p>User: {$hesk_settings['db_user']}</p>";
//        echo "<p>Datenbank: {$hesk_settings['db_name']}</p>";

        $conn = mysqli_connect(
            $hesk_settings['db_host'],
            $hesk_settings['db_user'],
            $hesk_settings['db_pass'],
            $hesk_settings['db_name']
        );

        if (!$conn) {
            echo "<p>❌ DB-Verbindung fehlgeschlagen: " . mysqli_connect_error() . "</p>";
            exit;
        }

//        echo "<p>✅ Verbindung erfolgreich</p>";
        return $conn;
    }
} else {
        echo "<p>❌ DB-Verbindung fehlgeschlagen: falsche Funktion geladen. </p>";
}
