<?php
// Einfaches, eigenständiges Script: eindeutige Liste aller Trainernamen
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../include.php';

$conn = get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo "Fehler: Datenbankverbindung fehlgeschlagen.";
    exit;
}

// NEW: Stelle sicher, dass die DB‑Verbindung UTF-8 benutzt (vermeidet "RÃ¶der")
if (!mysqli_set_charset($conn, 'utf8mb4')) {
    // Fallback / Debug: versuche zumindest utf8
    @mysqli_set_charset($conn, 'utf8');
}

// Tabellenname aus Einstellungen (sicher filtern)
$trainer_table = isset($hesk_settings['db_hb_pfix']) ? $hesk_settings['db_hb_pfix'] . 'trainer' : 'trainer';
$trainer_table = preg_replace('/[^A-Za-z0-9_]/', '', $trainer_table);

// Sort-Richtung aus GET (nur 'asc' oder 'desc' erlauben)
$dir = isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc' ? 'desc' : 'asc';
$order_sql = "ORDER BY trname " . strtoupper($dir);

// Query: eindeutige (unique) Trainernamen, leerwerte raus
$sql = "SELECT DISTINCT TRIM(trname) AS trname
        FROM `{$trainer_table}`
        WHERE TRIM(trname) <> ''
        {$order_sql}";

$res = mysqli_query($conn, $sql);

// Basis-Link für Sort-Links (erhalte andere GET-Parameter außer dir)
$qs = $_GET;
unset($qs['dir']);
$base_link = $_SERVER['PHP_SELF'];
if (!empty($qs)) {
    $base_link .= '?' . http_build_query($qs) . '&';
} else {
    $base_link .= '?';
}
$next_dir = ($dir === 'asc') ? 'desc' : 'asc';
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Alle Trainer — eindeutige Liste</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:system-ui,Arial,Helvetica,sans-serif;margin:18px}
  table{border-collapse:collapse;width:100%;max-width:900px}
  th,td{border:1px solid #ddd;padding:8px;text-align:left}
  th{background:#f3f3f3}
  th a{color:inherit;text-decoration:none}
</style>
</head>
<body>
<h1>Alle Trainer (einzigartige Liste)</h1>

<?php
if ($res === false) {
    echo "<p style='color:#900'>SQL-Fehler: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES) . "</p>";
} elseif (mysqli_num_rows($res) === 0) {
    echo "<p>Keine Trainer gefunden.</p>";
} else {
    echo "<table><thead><tr>";
    // keine ID-Spalte mehr, nur Name mit Sortierlink
    $link = htmlspecialchars($base_link . 'dir=' . $next_dir, ENT_QUOTES);
    $arrow = $dir === 'asc' ? ' ↑' : ' ↓';
    echo "<th><a href=\"{$link}\">Name{$arrow}</a></th>";
    echo "</tr></thead><tbody>";

    while ($row = mysqli_fetch_assoc($res)) {
        // decode HTML entities from DB, then escape for safe output
        $raw = $row['trname'] ?? '';
        $decoded = html_entity_decode($raw, ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
        echo "<tr><td>{$name}</td></tr>";
    }
    echo "</tbody></table>";
}

if ($res && is_object($res)) mysqli_free_result($res);
mysqli_close($conn);
?>
</body>
</html>