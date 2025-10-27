<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include_public.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = get_db_connection();
if (!$conn) {
    echo "<p>Fehler: DB Verbindung fehlgeschlagen.</p>";
    exit;
}
mysqli_set_charset($conn, 'utf8mb4');

global $hesk_settings;
$trainer_table = isset($hesk_settings['db_hb_pfix']) ? $hesk_settings['db_hb_pfix'] . 'trainer' : 'trainer';
$group_table   = isset($hesk_settings['db_hb_pfix']) ? $hesk_settings['db_hb_pfix'] . 'gruppen' : 'gruppen';

// einfache Sanitization für Tabellennamen
$trainer_table = preg_replace('/[^A-Za-z0-9_]/', '', $trainer_table);
$group_table   = preg_replace('/[^A-Za-z0-9_]/', '', $group_table);

// Ermittele korrekt vorhandene Spaltenname für Gruppen (namen oder name)
$group_col = 'name';
$check = mysqli_query($conn, "SHOW COLUMNS FROM `{$group_table}` LIKE 'namen'");
if ($check && mysqli_num_rows($check) > 0) {
    $group_col = 'namen';
    mysqli_free_result($check);
} else {
    $check2 = mysqli_query($conn, "SHOW COLUMNS FROM `{$group_table}` LIKE 'name'");
    if ($check2 && mysqli_num_rows($check2) > 0) {
        $group_col = 'name';
        mysqli_free_result($check2);
    } else {
        // fallback: nimm erste Spalte außer id falls nichts passt
        $cols = mysqli_query($conn, "SHOW COLUMNS FROM `{$group_table}`");
        if ($cols && mysqli_num_rows($cols) > 0) {
            while ($c = mysqli_fetch_assoc($cols)) {
                if ($c['Field'] !== 'id') { $group_col = $c['Field']; break; }
            }
            mysqli_free_result($cols);
        }
    }
}

// sichere Nutzung des Spaltennamens (nur Alnum + Unterstrich)
$group_col = preg_replace('/[^A-Za-z0-9_]/', '', $group_col);

// --- NEU: Sort‑Richtung für Namen aus GET, Basis-Link für Toggle ---
$allowed_dirs = ['asc', 'desc'];
$dir = isset($_GET['dir']) && in_array(strtolower($_GET['dir']), $allowed_dirs, true) ? strtolower($_GET['dir']) : 'asc';

$qs = $_GET;
unset($qs['dir']);
$base_link = $_SERVER['PHP_SELF'];
if (!empty($qs)) {
    $base_link .= '?' . http_build_query($qs) . '&';
} else {
    $base_link .= '?';
}
$next_dir = ($dir === 'asc') ? 'desc' : 'asc';

// sichere Order-Direktive
$order_dir = strtoupper($dir);

// Build query: UNIQUE list of trainers, groups concatenated with " / "
$sql = "
  SELECT
    TRIM(t.trname) AS trname,
    GROUP_CONCAT(DISTINCT TRIM(g.`{$group_col}`) ORDER BY TRIM(g.`{$group_col}`) SEPARATOR ' / ') AS groups
  FROM `{$trainer_table}` AS t
  LEFT JOIN `{$group_table}` AS g ON g.id = t.gruppe_id
  WHERE TRIM(t.trname) <> ''
  GROUP BY TRIM(t.trname)
  ORDER BY TRIM(t.trname) {$order_dir}
";

$res = mysqli_query($conn, $sql);

echo "<div style='margin-bottom:12px;'><h3 style='margin:0;'>Liste der Gruppen pro Trainer</h3></div>";

if ($res === false) {
    echo "<p style='color:#900'>SQL-Fehler: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<pre>" . htmlspecialchars($sql, ENT_QUOTES, 'UTF-8') . "</pre>";
} elseif (mysqli_num_rows($res) === 0) {
    echo "<p>Keine Trainer gefunden.</p>";
} else {
    echo "<table style='table-layout:fixed; width:100%; border-collapse:collapse;'>";
    // Name mit Sortierlink (nur Name sortierbar), Gruppe bleibt unveränderlich
    $link_name = htmlspecialchars($base_link . 'dir=' . $next_dir, ENT_QUOTES, 'UTF-8');
    $arrow = ($dir === 'asc') ? ' ↑' : ' ↓';
    echo "<tr><th style='border:1px solid #ccc; padding:6px;'><a href=\"{$link_name}\">Name{$arrow}</a></th><th style='border:1px solid #ccc; padding:6px;'>Gruppen</th></tr>";

    while ($row = mysqli_fetch_assoc($res)) {
        $rawName  = $row['trname']  ?? '';
        $rawGroups= $row['groups']   ?? '';

        // Entities decodieren, dann für HTML escapen — sorgt für korrekte Umlaute
        $name   = htmlspecialchars(html_entity_decode($rawName,  ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $groups = $rawGroups !== '' ? htmlspecialchars(html_entity_decode($rawGroups, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') : '–';

        echo "<tr>";
        echo "<td style='border:1px solid #ccc; padding:6px;'>{$name}</td>";
        echo "<td style='border:1px solid #ccc; padding:6px;'>{$groups}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

if ($res && is_object($res)) mysqli_free_result($res);
mysqli_close($conn);
?>