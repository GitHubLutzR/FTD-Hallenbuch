<?php
require_once '/srv/hesk_settings.inc.php'; // Pfad ggf. anpassen

// Optional: eigenen Prefix für Hallenbuch definieren
$hesk_settings['db_hb_pfix'] = 'hb_';

function get_db_connection(): PDO {
    global $hesk_settings;

    $dsn = "mysql:host={$hesk_settings['db_host']};dbname={$hesk_settings['db_name']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    return new PDO($dsn, $hesk_settings['db_user'], $hesk_settings['db_pass'], $options);
}

