<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include.php');

$conn = get_db_connection();
global $hesk_settings;

$trainer_table = $hesk_settings['db_hb_pfix'] . 'trainer';
$group_table   = $hesk_settings['db_hb_pfix'] . 'gruppen';

// Helper: sichere Tabellennamen (einfache Filterung)
$trainer_table = preg_replace('/[^A-Za-z0-9_]/', '', $trainer_table);
$group_table   = preg_replace('/[^A-Za-z0-9_]/', '', $group_table);

// Basis‑Ziel-URL für diese Seite (sauber, absolut relativ zum Webroot)
$self = $base_url . 'admin/trainers_list.php';

$trainer_table = preg_replace('/[^A-Za-z0-9_]/', '', $hesk_settings['db_hb_pfix'] . 'trainer');
$group_table   = preg_replace('/[^A-Za-z0-9_]/', '', $hesk_settings['db_hb_pfx'] . 'gruppen');

// prüfen, ob Feld "uniq" in trainer exists (optional)
$has_uniq = false;
$res = mysqli_query($conn, "SHOW COLUMNS FROM `{$trainer_table}` LIKE 'uniq'");
if ($res && mysqli_num_rows($res) > 0) {
    $has_uniq = true;
    mysqli_free_result($res);
}

// Sortierung aus GET, validieren
$allowed_sorts = ['name'];
if ($has_uniq) $allowed_sorts[] = 'uniq';
$allowed_dirs  = ['asc','desc'];

$sort = isset($_GET['sort']) ? strtolower($_GET['sort']) : 'name';
$dir  = isset($_GET['dir'])  ? strtolower($_GET['dir'])  : 'asc';
if (!in_array($sort, $allowed_sorts, true)) $sort = 'name';
if (!in_array($dir, $allowed_dirs, true)) $dir = 'asc';

// Basis-Link für Sort-Links (erhalte andere GET-Parameter)
$qs = $_GET;
unset($qs['sort'], $qs['dir']);
$base_link = $_SERVER['PHP_SELF'];
if (!empty($qs)) {
    $base_link .= '?' . http_build_query($qs) . '&';
} else {
    $base_link .= '?';
}

// Build ORDER BY
if ($sort === 'name') {
    $order_sql = "ORDER BY t.trname " . strtoupper($dir);
} else { // uniq
    // wenn uniq exists, sortiere zuerst nach uniq (falls leer fallback auf trname)
    $order_sql = "ORDER BY COALESCE(t.uniq, t.id) " . strtoupper($dir) . ", t.trname ASC";
}

// Query: gruppiere alle Gruppen pro Trainer (falls ein Trainer in mehreren Zeilen vorkommt)
// Wir gehen davon aus, dass ggf. mehrere Zeilen mit gleichem trname existieren (versch. gruppe_id)
$sql = "
  SELECT
    COALESCE(t.trname, '') AS trname,
    " . ($has_uniq ? "t.uniq AS uniq_val," : "t.id AS uniq_val,") . "
    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ' / ') AS groups
  FROM `{$trainer_table}` AS t
  LEFT JOIN `{$group_table}` AS g ON g.id = t.gruppe_id
  GROUP BY " . ($has_uniq ? "t.uniq, t.trname" : "t.trname, t.id") . "
  {$order_sql}
";

$result = mysqli_query($conn, $sql);
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Trainer — FTD Hallenbuch</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  table { border-collapse: collapse; width:100%; }
  th, td { border:1px solid #ccc; padding:8px; text-align:left; }
  th a { color:inherit; text-decoration:none; }
  .sort-ind { margin-left:6px; color:#666; font-size:0.9em; }
  .tools { margin-bottom:12px; }
</style>
</head>
<body>
<?php // falls include.php bereits Header ausgibt, kannst du das hier anpassen ?>
<h1>Trainer</h1>

<div class="tools">
  <a href="<?php echo htmlspecialchars($base_link . 'sort=name&dir=' . ($sort==='name' && $dir==='asc' ? 'desc' : 'asc'), ENT_QUOTES); ?>">
    Name<?php if ($sort==='name') echo $dir==='asc' ? " ↑" : " ↓"; ?>
  </a>
  <?php if ($has_uniq): ?>
    &nbsp;|&nbsp;
    <a href="<?php echo htmlspecialchars($base_link . 'sort=uniq&dir=' . ($sort==='uniq' && $dir==='asc' ? 'desc' : 'asc'), ENT_QUOTES); ?>">
      Uniq<?php if ($sort==='uniq') echo $dir==='asc' ? " ↑" : " ↓"; ?>
    </a>
  <?php endif; ?>
  &nbsp;|&nbsp;
  <a href="<?php echo htmlspecialchars($base_url . 'admin/groups.php', ENT_QUOTES); ?>">Gruppen-Verwaltung</a>
</div>

<?php
if (!$result) {
    echo "<p>Fehler bei Abfrage: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES) . "</p>";
} elseif (mysqli_num_rows($result) === 0) {
    echo "<p>Keine Trainer gefunden.</p>";
} else {
    echo "<table>";
    echo "<tr><th>Name</th><th>Gruppen</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $name = htmlspecialchars($row['trname'], ENT_QUOTES, 'UTF-8');
        $groups = $row['groups'] ? htmlspecialchars($row['groups'], ENT_QUOTES, 'UTF-8') : '—';
        echo "<tr>";
        echo "<td>{$name}</td>";
        echo "<td>{$groups}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
mysqli_free_result($result);
mysqli_close($conn);
?>
</body>
</html>
```// filepath: /home/risse/github/FTD-Hallenbuch/admin/trainers_list.php
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../include.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// einfache Berechtigung: nur eingeloggte Nutzer dürfen admin-Listen sehen
if (empty($_SESSION['user'])) {
    header('Location: ' . ($base_url ?? '/hallenbuch/') . 'login.php');
    exit;
}

$conn = get_db_connection();
if (!$conn) {
    echo "<p>Fehler: Datenbankverbindung fehlgeschlagen.</p>";
    exit;
}

$trainer_table = preg_replace('/[^A-Za-z0-9_]/', '', $hesk_settings['db_hb_pfix'] . 'trainer');
$group_table   = preg_replace('/[^A-Za-z0-9_]/', '', $hesk_settings['db_hb_pfx'] . 'gruppen');

// prüfen, ob Feld "uniq" in trainer exists (optional)
$has_uniq = false;
$res = mysqli_query($conn, "SHOW COLUMNS FROM `{$trainer_table}` LIKE 'uniq'");
if ($res && mysqli_num_rows($res) > 0) {
    $has_uniq = true;
    mysqli_free_result($res);
}

// Sortierung aus GET, validieren
$allowed_sorts = ['name'];
if ($has_uniq) $allowed_sorts[] = 'uniq';
$allowed_dirs  = ['asc','desc'];

$sort = isset($_GET['sort']) ? strtolower($_GET['sort']) : 'name';
$dir  = isset($_GET['dir'])  ? strtolower($_GET['dir'])  : 'asc';
if (!in_array($sort, $allowed_sorts, true)) $sort = 'name';
if (!in_array($dir, $allowed_dirs, true)) $dir = 'asc';

// Basis-Link für Sort-Links (erhalte andere GET-Parameter)
$qs = $_GET;
unset($qs['sort'], $qs['dir']);
$base_link = $_SERVER['PHP_SELF'];
if (!empty($qs)) {
    $base_link .= '?' . http_build_query($qs) . '&';
} else {
    $base_link .= '?';
}

// Build ORDER BY
if ($sort === 'name') {
    $order_sql = "ORDER BY t.trname " . strtoupper($dir);
} else { // uniq
    // wenn uniq exists, sortiere zuerst nach uniq (falls leer fallback auf trname)
    $order_sql = "ORDER BY COALESCE(t.uniq, t.id) " . strtoupper($dir) . ", t.trname ASC";
}

// Query: gruppiere alle Gruppen pro Trainer (falls ein Trainer in mehreren Zeilen vorkommt)
// Wir gehen davon aus, dass ggf. mehrere Zeilen mit gleichem trname existieren (versch. gruppe_id)
$sql = "
  SELECT
    COALESCE(t.trname, '') AS trname,
    " . ($has_uniq ? "t.uniq AS uniq_val," : "t.id AS uniq_val,") . "
    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ' / ') AS groups
  FROM `{$trainer_table}` AS t
  LEFT JOIN `{$group_table}` AS g ON g.id = t.gruppe_id
  GROUP BY " . ($has_uniq ? "t.uniq, t.trname" : "t.trname, t.id") . "
  {$order_sql}
";

$result = mysqli_query($conn, $sql);
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Trainer — FTD Hallenbuch</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  table { border-collapse: collapse; width:100%; }
  th, td { border:1px solid #ccc; padding:8px; text-align:left; }
  th a { color:inherit; text-decoration:none; }
  .sort-ind { margin-left:6px; color:#666; font-size:0.9em; }
  .tools { margin-bottom:12px; }
</style>
</head>
<body>
<?php // falls include.php bereits Header ausgibt, kannst du das hier anpassen ?>
<h1>Trainer</h1>

<div class="tools">
  <a href="<?php echo htmlspecialchars($base_link . 'sort=name&dir=' . ($sort==='name' && $dir==='asc' ? 'desc' : 'asc'), ENT_QUOTES); ?>">
    Name<?php if ($sort==='name') echo $dir==='asc' ? " ↑" : " ↓"; ?>
  </a>
  <?php if ($has_uniq): ?>
    &nbsp;|&nbsp;
    <a href="<?php echo htmlspecialchars($base_link . 'sort=uniq&dir=' . ($sort==='uniq' && $dir==='asc' ? 'desc' : 'asc'), ENT_QUOTES); ?>">
      Uniq<?php if ($sort==='uniq') echo $dir==='asc' ? " ↑" : " ↓"; ?>
    </a>
  <?php endif; ?>
  &nbsp;|&nbsp;
  <a href="<?php echo htmlspecialchars($base_url . 'admin/groups.php', ENT_QUOTES); ?>">Gruppen-Verwaltung</a>
</div>

<?php
if (!$result) {
    echo "<p>Fehler bei Abfrage: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES) . "</p>";
} elseif (mysqli_num_rows($result) === 0) {
    echo "<p>Keine Trainer gefunden.</p>";
} else {
    echo "<table>";
    echo "<tr><th>Name</th><th>Gruppen</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $name = htmlspecialchars($row['trname'], ENT_QUOTES, 'UTF-8');
        $groups = $row['groups'] ? htmlspecialchars($row['groups'], ENT_QUOTES, 'UTF-8') : '—';
        echo "<tr>";
        echo "<td>{$name}</td>";
        echo "<td>{$groups}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
mysqli_free_result($result);
mysqli_close($conn);
?>
</body>
</html>