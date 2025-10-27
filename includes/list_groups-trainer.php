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

// --- Sort‑Richtung für Gruppen aus GET, Basis-Link für Toggle ---
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
$order_dir = strtoupper($dir);

// Build query: UNIQUE list of groups, trainers concatenated with " / "
// Gruppe "sonstige" (case-insensitive) ausschließen
$sql = "
  SELECT
    g.id AS gid,
    TRIM(g.`{$group_col}`) AS group_name,
    GROUP_CONCAT(DISTINCT TRIM(t.trname) ORDER BY TRIM(t.trname) SEPARATOR ' / ') AS trainers
  FROM `{$group_table}` AS g
  LEFT JOIN `{$trainer_table}` AS t ON t.gruppe_id = g.id
  WHERE g.id IS NOT NULL
    AND TRIM(g.`{$group_col}`) <> ''
    AND LOWER(TRIM(g.`{$group_col}`)) <> 'sonstige'
  GROUP BY g.id, TRIM(g.`{$group_col}`)
  ORDER BY TRIM(g.`{$group_col}`) {$order_dir}
";

$res = mysqli_query($conn, $sql);

echo "<div style='margin-bottom:12px;'><h3 style='margin:0;'>Liste der Trainner/ -innen pro Gruppen</h3></div>";

if ($res === false) {
    echo "<p style='color:#900'>SQL-Fehler: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<pre>" . htmlspecialchars($sql, ENT_QUOTES, 'UTF-8') . "</pre>";
} elseif (mysqli_num_rows($res) === 0) {
    echo "<p>Keine Gruppen gefunden.</p>";
} else {
    // CSS: schmalere Zeilenhöhe / weniger Padding
    echo '<style>
      /* reduzierte Zeilenhöhe für die Tabelle */
      table.groups-trainer { font-size:13px; border-collapse:collapse; }
      table.groups-trainer th, table.groups-trainer td {
        padding:4px 6px;
        line-height:1.1;
        vertical-align:middle;
      }
      /* Gruppe-Breitensetzung / keine Umbrüche */
      table.groups-trainer th:first-child, table.groups-trainer td:first-child {
        width:260px;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
      }
      /* Trainer-Spalte: ebenfalls keine Zeilenumbrüche */
      table.groups-trainer td:nth-child(2) {
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
      }
    </style>';

    // Tabelle mit Klasse damit CSS greift
    $link_group = htmlspecialchars($base_link . 'dir=' . $next_dir, ENT_QUOTES, 'UTF-8');
    $arrow = ($dir === 'asc') ? ' ↑' : ' ↓';
    echo "<table class='groups-trainer' style='table-layout:fixed; width:100%;'>";
    echo "<tr><th style='border:1px solid #ccc; padding:6px;'><a href=\"{$link_group}\">Gruppe{$arrow}</a></th><th style='border:1px solid #ccc; padding:6px;'>Trainer</th></tr>";

    while ($row = mysqli_fetch_assoc($res)) {
        $rawGroup   = $row['group_name'] ?? '';
        $rawTrainers= $row['trainers'] ?? '';

        $group_display = $rawGroup !== '' ? htmlspecialchars(html_entity_decode($rawGroup, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') : '–';
        $trainers_display = $rawTrainers !== '' ? htmlspecialchars(html_entity_decode($rawTrainers, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') : '–';

        echo "<tr>";
        echo "<td style='border:1px solid #ccc;'>{$group_display}</td>";
        echo "<td style='border:1px solid #ccc;'>{$trainers_display}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

if ($res && is_object($res)) mysqli_free_result($res);
mysqli_close($conn);
?>