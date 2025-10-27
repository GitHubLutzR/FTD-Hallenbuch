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
$self = $_SERVER['REQUEST_URI'];  

// POST-Aktionen: create / save / delete / cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && isset($_POST['trname'], $_POST['gruppe_id'])) {
        $trname = trim($_POST['trname']);
        $gid = (int)$_POST['gruppe_id'];
        if ($trname !== '' && $gid > 0) {
            // encode special chars before saving
            $safe_trname = htmlentities($trname, ENT_QUOTES, 'UTF-8');
            $stmt = mysqli_prepare($conn, "INSERT INTO `{$trainer_table}` (trname, gruppe_id) VALUES (?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $safe_trname, $gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        // redirect to avoid resubmit
        header('Location: ' . $self );
        exit;
    }

    if ($action === 'save' && isset($_POST['old_trname'], $_POST['old_gid'], $_POST['trname'], $_POST['gruppe_id'])) {
        $old_trname = trim($_POST['old_trname']);
        $old_gid = (int)$_POST['old_gid'];
        $trname = trim($_POST['trname']);
        $gid = (int)$_POST['gruppe_id'];
        if ($old_trname !== '' && $trname !== '' && $gid > 0) {
            // encode special chars before saving
            $safe_trname = htmlentities($trname, ENT_QUOTES, 'UTF-8');
            $stmt = mysqli_prepare($conn, "UPDATE `{$trainer_table}` SET trname = ?, gruppe_id = ? WHERE trname = ? AND gruppe_id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sisi', $safe_trname, $gid, $old_trname, $old_gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        header('Location: ' . $self );
        exit;
    }

    if ($action === 'delete' && isset($_POST['trname'], $_POST['gruppe_id'])) {
        $trname = trim($_POST['trname']);
        $gid = (int)$_POST['gruppe_id'];
        if ($trname !== '' && $gid > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM `{$trainer_table}` WHERE trname = ? AND gruppe_id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $trname, $gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        header('Location: ' . $self );
        exit;
    }

    if ($action === 'cancel') {
        header('Location: ' . $self );
        exit;
    }
}

// Holen aller Gruppen für Dropdowns
$groups = [];
$gres = mysqli_query($conn, "SELECT id, name FROM `{$group_table}` ORDER BY name ASC");
if ($gres) {
    while ($g = mysqli_fetch_assoc($gres)) {
        $groups[(int)$g['id']] = $g['name'];
    }
    mysqli_free_result($gres);
}

// Confirm-Delete Seite (PHP-Bestätigung), wenn gesetzt
if (isset($_GET['confirm_delete_trname']) && isset($_GET['confirm_delete_gid'])) {
    $ctname = $_GET['confirm_delete_trname'];
    $cgid = (int)($_GET['confirm_delete_gid']);
    if ($ctname === '' || $cgid <= 0) {
        header('Location: ' . $self );
        exit;
    }
    $ctname_esc = htmlspecialchars($ctname, ENT_QUOTES, 'UTF-8');
    $gname = isset($groups[$cgid]) ? htmlspecialchars($groups[$cgid], ENT_QUOTES, 'UTF-8') : '–';
    echo "<h3>Trainer löschen</h3>";
    echo "<p>Bitte bestätigen: Trainer <strong>{$ctname_esc}</strong> aus Gruppe <strong>{$gname}</strong> wirklich löschen?</p>";
    echo "<form method='post' style='display:flex; gap:8px; align-items:center;'>";
    echo "<input type='hidden' name='action' value='delete'>";
    echo "<input type='hidden' name='trname' value='" . htmlspecialchars($ctname, ENT_QUOTES, 'UTF-8') . "'>";
    echo "<input type='hidden' name='gruppe_id' value='{$cgid}'>";
    echo "<button type='submit' style='background:#c00;color:#fff;border:none;padding:6px 10px;cursor:pointer;'>Trainer löschen</button>";
    $back = preg_replace('/([?&])(confirm_delete_trname|confirm_delete_gid)=[^&]+(&?)/', '$1', $_SERVER['REQUEST_URI']);
    $back = rtrim($back, '?&');
    echo " <a href='".htmlspecialchars($back, ENT_QUOTES, 'UTF-8')."' style='padding:6px 10px; background:#eee; text-decoration:none; color:#000; border:1px solid #ccc;'>Abbrechen</a>";
    echo "</form>";
    exit;
}

// Edit key from GET (old trname + gid)
$edit_trname = isset($_GET['edit_trname']) ? $_GET['edit_trname'] : null;
$edit_gid = isset($_GET['edit_gid']) ? (int)$_GET['edit_gid'] : 0;

// Optional: New trainer form if ?new=1
if (isset($_GET['new']) && $_GET['new'] == '1') {
    echo "<h3>Neuen Trainer anlegen</h3>";
    echo "<form method='post' style='display:flex; gap:8px; align-items:center; margin-bottom:12px;'>";
    echo "<input type='hidden' name='action' value='create'>";
    echo "<input type='text' name='trname' placeholder='Name des Trainers' required style='padding:6px;border:1px solid #ccc;border-radius:4px;'>";
    echo "<select name='gruppe_id' required style='padding:6px;border:1px solid #ccc;border-radius:4px;'>";
    echo "<option value=''>Bitte Gruppe wählen</option>";
    foreach ($groups as $gid => $gname) {
        echo "<option value='".(int)$gid."'>".htmlspecialchars($gname, ENT_QUOTES, 'UTF-8')."</option>";
    }
    echo "</select>";
    echo "<button type='submit' style='padding:6px 10px; background:#28a745;color:#fff;border:none;border-radius:4px;'>Anlegen</button>";
    $cancel_url = preg_replace('/([?&])new=1(&?)/', '$1', $_SERVER['REQUEST_URI']);
    $cancel_url = rtrim($cancel_url, '?&');
    echo " <a href='".htmlspecialchars($cancel_url, ENT_QUOTES, 'UTF-8')."' style='padding:6px 10px; background:#eee; text-decoration:none; color:#000; border:1px solid #ccc; border-radius:4px;'>Abbrechen</a>";
    echo "</form>";
}

// Filter: Gruppe auswählen (muss gesetzt sein, bevor die SQL-Query gebaut wird)
$filter_gid = isset($_GET['filter_gid']) ? (int)$_GET['filter_gid'] : 0;

// --- NEU: Sortierung aus GET lesen und validieren ---
$allowed_sorts = ['name','group'];
$allowed_dirs  = ['asc','desc'];

$sort = isset($_GET['sort']) ? strtolower($_GET['sort']) : 'name';
$dir  = isset($_GET['dir'])  ? strtolower($_GET['dir'])  : 'asc';

if (!in_array($sort, $allowed_sorts, true)) $sort = 'name';
if (!in_array($dir, $allowed_dirs, true)) $dir = 'asc';

// Basis-Link für Sort-Links (behalte ggf. filter_gid/new/edit params)
$qs = $_GET;
unset($qs['sort'], $qs['dir']);
$base_link = $_SERVER['PHP_SELF'];
if (!empty($qs)) {
    $base_link .= '?' . http_build_query($qs) . '&';
} else {
    $base_link .= '?';
}

// Build ORDER BY (JOIN, damit Gruppennamen sortierbar sind)
$order_sql = 'ORDER BY t.trname ' . ($sort === 'name' ? strtoupper($dir) : 'ASC');
if ($sort === 'group') {
    $order_sql = 'ORDER BY g.name ' . strtoupper($dir);
}

// Query mit Join damit group_name verfügbar ist und sortierbar ist
$sql = "
  SELECT t.trname, t.gruppe_id, g.name AS group_name
  FROM `{$trainer_table}` AS t
  LEFT JOIN `{$group_table}` AS g ON g.id = t.gruppe_id
  {$order_sql}
";

if ($filter_gid > 0) {
    $sql = "
      SELECT t.trname, t.gruppe_id, g.name AS group_name
      FROM `{$trainer_table}` AS t
      LEFT JOIN `{$group_table}` AS g ON g.id = t.gruppe_id
      WHERE t.gruppe_id = " . (int)$filter_gid . "
      {$order_sql}
    ";
}

$res = mysqli_query($conn, $sql);

// kompaktere Zeilenhöhe / Spaltenbreiten (keine funktionalen Änderungen)
echo '<style>
  .slim-table { font-size:12px; border-collapse:collapse; width:100%; table-layout:fixed; }
  .slim-table th, .slim-table td { padding:4px 6px; line-height:1.0; vertical-align:middle; border:1px solid #ccc; }
  .slim-table td { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .slim-table td.name-col { width:50%; }
  .slim-table td.group-col { width:30%; }
  .slim-table td.actions-col { width:10%; text-align:center; }
  .slim-input { padding:3px 5px; font-size:12px; }
  .slim-table a, .slim-table button { line-height:1.0; font-size:12px; padding:2px 6px; }
  /* make selects/inputs visually compact */
  .slim-table input, .slim-table select, .slim-table textarea { font-size:12px; padding:4px; }
</style>';

// single table (replace any other "<table ...>" open so we do not nest tables)
echo "<table class=\'slim-table\' style=\'table-layout:fixed; width:100%; border-collapse:collapse;\'>";

// Tabelle: alle Trainer mit Gruppennamen filterbar
echo "<div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;'>";
echo "<h3 style='margin:0;'>Liste der Trainer:</h3>";
$new_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?new=1', ENT_QUOTES, 'UTF-8');
echo "<a href='{$new_url}' style='padding:8px 12px; background:#2b7cff; color:#fff; text-decoration:none; border-radius:4px;'>➕ Neuer Trainer</a>";
echo "</div>";

// Filter: Gruppe auswählen
$filter_gid = isset($_GET['filter_gid']) ? (int)$_GET['filter_gid'] : 0;
echo "<form method='get' style='margin-bottom:12px; display:flex; gap:8px; align-items:center;'>";
echo "<label>Gruppe: ";
echo "<select name='filter_gid'>";
echo "<option value='0'>Alle</option>";
foreach ($groups as $gid => $gname) {
    $sel = ($gid === $filter_gid) ? " selected" : "";
    echo "<option value='".(int)$gid."'{$sel}>".htmlspecialchars($gname, ENT_QUOTES, 'UTF-8')."</option>";
}
echo "</select>";
echo "</label>";
echo " <button type='submit' style='padding:6px 10px;'>Filtern</button>";
echo "</form>";

// Tabelle Ausgabe: Header mit sortierbaren Links
if ($res && mysqli_num_rows($res) > 0) {
    // berechne toggle dirs für Spalten
    $next_dir_name  = ($sort === 'name'  && $dir === 'asc')  ? 'desc' : 'asc';
    $next_dir_group = ($sort === 'group' && $dir === 'asc')  ? 'desc' : 'asc';

    echo '<table class="slim-table" style="table-layout:fixed; width:100%; border-collapse:collapse;">';
    echo "<tr>";
    // Name header (Link wechselt sort & dir)
    $link_name = htmlspecialchars($base_link . 'sort=name&dir=' . $next_dir_name, ENT_QUOTES, 'UTF-8');
    $arrow_name = ($sort === 'name') ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
    echo "<th style='border:1px solid #ccc; padding:6px;'><a href=\"{$link_name}\">Name{$arrow_name}</a></th>";

    // Gruppe header
    $link_group = htmlspecialchars($base_link . 'sort=group&dir=' . $next_dir_group, ENT_QUOTES, 'UTF-8');
    $arrow_group = ($sort === 'group') ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
    echo "<th style='border:1px solid #ccc; padding:6px;'><a href=\"{$link_group}\">Gruppe{$arrow_group}</a></th>";

    echo "<th style='border:1px solid #ccc; padding:6px;'>Bearbeiten</th>";
    echo "<th style='border:1px solid #ccc; padding:6px;'>Löschen</th>";
    echo "</tr>";

    while ($row = mysqli_fetch_assoc($res)) {
        $trname = $row['trname'] ?? '';
        $gid = (int)($row['gruppe_id'] ?? 0);

        // decode any HTML entities from DB, then escape for safe output (fixes Umlaut-Mojibake)
        $display_name = htmlspecialchars(
            html_entity_decode($trname, ENT_QUOTES, 'UTF-8'),
            ENT_QUOTES,
            'UTF-8'
        );

        // group_name aus JOIN verwenden, fallback auf $groups array falls leer
        $raw_group = !empty($row['group_name']) ? $row['group_name'] : ($groups[$gid] ?? '');
        $display_group = $raw_group !== '' 
            ? htmlspecialchars(html_entity_decode($raw_group, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') 
            : '–';

        echo "<tr>";
        if ($edit_trname !== null && $edit_trname === $trname && $edit_gid === $gid) {
            // Inline edit form (identify by old trname + old gid)
            $escaped = htmlspecialchars($trname, ENT_QUOTES, 'UTF-8');
            echo "<td style='border:1px solid #ccc; padding:6px;'>
                    <form method='post' style='display:flex; gap:8px; align-items:center; margin:0;'>
                      <input type='hidden' name='action' value='save'>
                      <input type='hidden' name='old_trname' value=\"".htmlspecialchars($trname, ENT_QUOTES, 'UTF-8')."\">
                      <input type='hidden' name='old_gid' value='{$gid}'>
                      <input type='text' name='trname' value=\"{$escaped}\" style='padding:6px;border:1px solid #ccc;'>
                    </td>";
            echo "<td style='border:1px solid #ccc; padding:6px;'>
                      <select name='gruppe_id'>";
            foreach ($groups as $ggid => $gname) {
                $s = ($ggid === $gid) ? " selected" : "";
                echo "<option value='".(int)$ggid."'{$s}>".htmlspecialchars($gname, ENT_QUOTES, 'UTF-8')."</option>";
            }
            echo "</select></td>";
            echo "<td style='border:1px solid #ccc; padding:6px;'><button type='submit'>Speichern</button></td>";
            echo "<td style='border:1px solid #ccc; padding:6px;'><button type='submit' name='action' value='cancel'>Abbrechen</button></td>";
            echo "</form>";
        } else {
            echo "<td style='border:1px solid #ccc; padding:6px;'>{$display_name}</td>";
            echo "<td style='border:1px solid #ccc; padding:6px;'>{$display_group}</td>";
            $edit_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?edit_trname=' . urlencode($trname) . '&edit_gid=' . $gid . ($filter_gid ? '&filter_gid=' . (int)$filter_gid : ''), ENT_QUOTES, 'UTF-8');
            echo "<td style='border:1px solid #ccc; padding:6px; text-align:center;'><a href='{$edit_url}' title='Bearbeiten'>✏️</a></td>";
            $confirm_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?confirm_delete_trname=' . urlencode($trname) . '&confirm_delete_gid=' . $gid . ($filter_gid ? '&filter_gid=' . (int)$filter_gid : ''), ENT_QUOTES, 'UTF-8');
            echo "<td style='border:1px solid #ccc; padding:6px; text-align:center;'><a href='{$confirm_url}' title='Löschen' style='color:#900;'>🗑️</a></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Keine Trainer gefunden.</p>";
}

if (isset($stmt) && is_object($stmt)) { mysqli_stmt_close($stmt); }
if (isset($res) && is_object($res)) { mysqli_free_result($res); }

mysqli_close($conn);
?>
