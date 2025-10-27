<?php
ob_start(); // <-- fängt frühe Ausgabe ab, verhindert "headers already sent"
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

$conn = get_db_connection();
if (!$conn) {
    echo "<p>Fehler: DB Verbindung fehlgeschlagen.</p>";
    exit;
}
mysqli_set_charset($conn, 'utf8mb4');

global $hesk_settings;

// sichere Tabellennamen
$trainer_table = isset($hesk_settings['db_hb_pfix']) ? $hesk_settings['db_hb_pfix'] . 'trainer' : 'trainer';
$group_table   = isset($hesk_settings['db_hb_pfix']) ? $hesk_settings['db_hb_pfix'] . 'gruppen' : 'gruppen';
$trainer_table = preg_replace('/[^A-Za-z0-9_]/', '', $trainer_table);
$group_table   = preg_replace('/[^A-Za-z0-9_]/', '', $group_table);

// ermittel Group-Spaltenname (namen oder name)
$group_col = 'name';
$chk = mysqli_query($conn, "SHOW COLUMNS FROM `{$group_table}` LIKE 'namen'");
if ($chk && mysqli_num_rows($chk) > 0) {
    $group_col = 'namen';
    mysqli_free_result($chk);
} else {
    $chk2 = mysqli_query($conn, "SHOW COLUMNS FROM `{$group_table}` LIKE 'name'");
    if ($chk2 && mysqli_num_rows($chk2) > 0) {
        $group_col = 'name';
        mysqli_free_result($chk2);
    } else {
        // fallback: nimm erste Spalte außer id
        $cols = mysqli_query($conn, "SHOW COLUMNS FROM `{$group_table}`");
        if ($cols) {
            while ($c = mysqli_fetch_assoc($cols)) {
                if ($c['Field'] !== 'id') { $group_col = $c['Field']; break; }
            }
            mysqli_free_result($cols);
        }
    }
}
$group_col = preg_replace('/[^A-Za-z0-9_]/', '', $group_col);

// helper: decode entities then escape for output
function safe_html($s) {
    if ($s === null) return '';
    return htmlspecialchars(html_entity_decode($s, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
}

// POST: speichern (inline edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // NEU: Trainer anlegen
    if ($_POST['action'] === 'create' && isset($_POST['trname'])) {
        $new_trname = trim($_POST['trname']);
        $new_gid    = isset($_POST['gruppe_id']) ? (int)$_POST['gruppe_id'] : 0;
        if ($new_trname !== '') {
            $safe_trname = htmlentities($new_trname, ENT_QUOTES, 'UTF-8');
            $stmt = mysqli_prepare($conn, "INSERT INTO `{$trainer_table}` (trname, gruppe_id) VALUES (?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $safe_trname, $new_gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'save' && isset($_POST['old_trname'], $_POST['old_gid'], $_POST['trname'], $_POST['gruppe_id'])) {
        $old_trname = $_POST['old_trname'];
        $old_gid    = (int)$_POST['old_gid'];
        $new_trname = trim($_POST['trname']);
        $new_gid    = (int)$_POST['gruppe_id'];

        if ($new_trname !== '' && $old_gid > 0) {
            // encode special chars before saving (keeps DB consistent with earlier changes)
            $safe_trname = htmlentities($new_trname, ENT_QUOTES, 'UTF-8');
            $stmt = mysqli_prepare($conn, "UPDATE `{$trainer_table}` SET trname = ?, gruppe_id = ? WHERE trname = ? AND gruppe_id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sisi', $safe_trname, $new_gid, $old_trname, $old_gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        // redirect to avoid repost
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'delete' && isset($_POST['del_trname'], $_POST['del_gid'])) {
        $del_trname = $_POST['del_trname'];
        $del_gid = (int)$_POST['del_gid'];
        if ($del_gid >= 0 && $del_trname !== '') {
            $stmt = mysqli_prepare($conn, "DELETE FROM `{$trainer_table}` WHERE trname = ? AND gruppe_id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $del_trname, $del_gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// einfache Abfrage: Trainer mit Gruppenname (keine Filter-Funktionen)
$sql = "
  SELECT t.trname AS trname, t.gruppe_id AS gid, g.`{$group_col}` AS group_name
  FROM `{$trainer_table}` AS t
  LEFT JOIN `{$group_table}` AS g ON g.id = t.gruppe_id
  ORDER BY TRIM(t.trname) ASC
";

$res = mysqli_query($conn, $sql);

// CSS: schmalere Zeilen
echo '<style>
  .slim-table { font-size:13px; border-collapse:collapse; width:100%; table-layout:fixed; }
  .slim-table th, .slim-table td { padding:4px 6px; line-height:1.05; vertical-align:middle; border:1px solid #ccc; }
  .slim-table td:first-child { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:40%; }
  .slim-table td:nth-child(2) { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:40%; }
  .slim-table td:nth-child(3), .slim-table td:nth-child(4) { text-align:center; width:10%; }
  .slim-input { padding:3px 6px; font-size:13px; }
  form.inline { margin:0; display:inline-block; }
</style>';

echo "<h3>Liste der Trainer</h3>";

if ($res === false) {
    echo "<p style='color:#900'>SQL-Fehler: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<pre>" . htmlspecialchars($sql, ENT_QUOTES, 'UTF-8') . "</pre>";
    mysqli_close($conn);
    exit;
}

if (mysqli_num_rows($res) === 0) {
    echo "<p>Keine Trainer gefunden.</p>";
    mysqli_free_result($res);
    mysqli_close($conn);
    exit;
}

echo "<table class='slim-table'>";
echo "<thead><tr><th>Name</th><th>Gruppe</th><th>Bearbeiten</th><th>Löschen</th></tr></thead><tbody>";

while ($row = mysqli_fetch_assoc($res)) {
    $rawName  = $row['trname'] ?? '';
    $rawGroup = $row['group_name'] ?? '';
    $gid      = (int)($row['gid'] ?? 0);

    $display_name  = safe_html($rawName);
    $display_group = $rawGroup !== '' ? safe_html($rawGroup) : '–';

    echo "<tr>";

    // If editing this row: show inline form
    $is_edit = (isset($_GET['edit_trname'], $_GET['edit_gid']) && $_GET['edit_trname'] === $rawName && (int)$_GET['edit_gid'] === $gid);

    if ($is_edit) {
        // inline edit: fields for trname + gruppe_id (select built from groups table)
        echo "<td>";
        echo "<form method='post' class='inline'>";
        echo "<input type='hidden' name='action' value='save'>";
        // keep raw trname value for WHERE comparison
        echo "<input type='hidden' name='old_trname' value=\"" . htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8') . "\">";
        echo "<input type='hidden' name='old_gid' value='" . $gid . "'>";
        echo "<input class='slim-input' type='text' name='trname' value=\"" . htmlspecialchars(html_entity_decode($rawName, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') . "\">";
        echo "</td>";

        // gruppe select
        echo "<td>";
        echo "<select name='gruppe_id' class='slim-input'>";
        // load groups for select
        $gq = "SELECT id, `{$group_col}` AS gname FROM `{$group_table}` ORDER BY `{$group_col}` ASC";
        $gr = mysqli_query($conn, $gq);
        if ($gr) {
            while ($g = mysqli_fetch_assoc($gr)) {
                $ggid = (int)$g['id'];
                $gname = $g['gname'] ?? '';
                $sel = ($ggid === $gid) ? ' selected' : '';
                echo "<option value='" . $ggid . "'{$sel}>" . safe_html($gname) . "</option>";
            }
            mysqli_free_result($gr);
        }
        echo "</select>";
        echo "</td>";

        echo "<td colspan='2' style='text-align:center;'>";
        echo "<button class='slim-input' type='submit'>Speichern</button> ";
        echo "<a class='slim-input' href='" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "' style='text-decoration:none;padding:4px 6px;border:1px solid #ccc;background:#f3f3f3;color:#000;border-radius:3px;margin-left:6px;'>Abbrechen</a>";
        echo "</form>";
        echo "</td>";
    } else {
        // normal row
        echo "<td>{$display_name}</td>";
        echo "<td>{$display_group}</td>";

        // Edit link: go to same page with edit params (use raw DB value so matching works)
        $edit_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?edit_trname=' . urlencode($rawName) . '&edit_gid=' . $gid, ENT_QUOTES, 'UTF-8');
        echo "<td><a href=\"{$edit_url}\" title='Bearbeiten'>✏️</a></td>";

        // Delete: small POST form to avoid accidental GET deletes
        echo "<td>";
        echo "<form method='post' class='inline' onsubmit=\"return confirm('Wirklich löschen?');\">";
        echo "<input type='hidden' name='action' value='delete'>";
        echo "<input type='hidden' name='del_trname' value=\"" . htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8') . "\">";
        echo "<input type='hidden' name='del_gid' value='" . $gid . "'>";
        echo "<button class='slim-input' type='submit' style='background:transparent;border:none;cursor:pointer;color:#900;'>🗑️</button>";
        echo "</form>";
        echo "</td>";
    }

    echo "</tr>";
}

echo "</tbody></table>";

mysqli_free_result($res);
mysqli_close($conn);
ob_end_flush();
?>