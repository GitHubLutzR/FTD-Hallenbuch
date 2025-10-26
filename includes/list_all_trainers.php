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

// POST-Aktionen: create / save / delete / cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && isset($_POST['trname'], $_POST['gruppe_id'])) {
        $trname = trim($_POST['trname']);
        $gid = (int)$_POST['gruppe_id'];
        if ($trname !== '' && $gid > 0) {
            $stmt = mysqli_prepare($conn, "INSERT INTO `{$trainer_table}` (trname, gruppe_id) VALUES (?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $trname, $gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        // redirect to avoid resubmit
        $loc = preg_replace('/([?&])new=1(&?)/', '$1', $_SERVER['REQUEST_URI']);
        $loc = rtrim($loc, '?&');
        header("Location: " . $loc);
        exit;
    }

    if ($action === 'save' && isset($_POST['old_trname'], $_POST['old_gid'], $_POST['trname'], $_POST['gruppe_id'])) {
        $old_trname = trim($_POST['old_trname']);
        $old_gid = (int)$_POST['old_gid'];
        $trname = trim($_POST['trname']);
        $gid = (int)$_POST['gruppe_id'];
        if ($old_trname !== '' && $trname !== '' && $gid > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE `{$trainer_table}` SET trname = ?, gruppe_id = ? WHERE trname = ? AND gruppe_id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sisi', $trname, $gid, $old_trname, $old_gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        header("Location: " . preg_replace('/[?&]edit_trname=[^&]+/', '', $_SERVER['REQUEST_URI']));
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
        header("Location: " . preg_replace('/[?&]confirm_delete_trname=[^&]+/', '', $_SERVER['REQUEST_URI']));
        exit;
    }

    if ($action === 'cancel') {
        header("Location: " . preg_replace('/[?&]edit_trname=[^&]+/', '', $_SERVER['REQUEST_URI']));
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
        header("Location: " . preg_replace('/([?&])(confirm_delete_trname|confirm_delete_gid)=[^&]+(&?)/', '$1', $_SERVER['REQUEST_URI']));
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

// Build query
if ($filter_gid > 0) {
    $stmt = mysqli_prepare($conn, "SELECT trname, gruppe_id FROM `{$trainer_table}` WHERE gruppe_id = ? ORDER BY trname ASC");
    mysqli_stmt_bind_param($stmt, 'i', $filter_gid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $res = mysqli_query($conn, "SELECT trname, gruppe_id FROM `{$trainer_table}` ORDER BY trname ASC");
}

if ($res && mysqli_num_rows($res) > 0) {
    echo "<table style='table-layout:fixed; width:100%; border-collapse:collapse;'>";
    echo "<tr><th style='border:1px solid #ccc; padding:6px;'>Name</th><th style='border:1px solid #ccc; padding:6px;'>Gruppe</th><th style='border:1px solid #ccc; padding:6px;'>Bearbeiten</th><th style='border:1px solid #ccc; padding:6px;'>Löschen</th></tr>";
    while ($row = mysqli_fetch_assoc($res)) {
        $trname = $row['trname'];
        $gid = (int)$row['gruppe_id'];
        $display_name = htmlspecialchars($trname, ENT_QUOTES, 'UTF-8');
        $display_group = isset($groups[$gid]) ? htmlspecialchars($groups[$gid], ENT_QUOTES, 'UTF-8') : '–';

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
            $edit_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?edit_trname=' . urlencode($trname) . '&edit_gid=' . $gid, ENT_QUOTES, 'UTF-8');
            echo "<td style='border:1px solid #ccc; padding:6px; text-align:center;'><a href='{$edit_url}' title='Bearbeiten'>✏️</a></td>";
            $confirm_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?confirm_delete_trname=' . urlencode($trname) . '&confirm_delete_gid=' . $gid, ENT_QUOTES, 'UTF-8');
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
```// filepath: /home/risse/github/FTD-Hallenbuch/includes/list_all_trainers.php
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

// POST-Aktionen: create / save / delete / cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && isset($_POST['trname'], $_POST['gruppe_id'])) {
        $trname = trim($_POST['trname']);
        $gid = (int)$_POST['gruppe_id'];
        if ($trname !== '' && $gid > 0) {
            $stmt = mysqli_prepare($conn, "INSERT INTO `{$trainer_table}` (trname, gruppe_id) VALUES (?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $trname, $gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        // redirect to avoid resubmit
        $loc = preg_replace('/([?&])new=1(&?)/', '$1', $_SERVER['REQUEST_URI']);
        $loc = rtrim($loc, '?&');
        header("Location: " . $loc);
        exit;
    }

    if ($action === 'save' && isset($_POST['old_trname'], $_POST['old_gid'], $_POST['trname'], $_POST['gruppe_id'])) {
        $old_trname = trim($_POST['old_trname']);
        $old_gid = (int)$_POST['old_gid'];
        $trname = trim($_POST['trname']);
        $gid = (int)$_POST['gruppe_id'];
        if ($old_trname !== '' && $trname !== '' && $gid > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE `{$trainer_table}` SET trname = ?, gruppe_id = ? WHERE trname = ? AND gruppe_id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sisi', $trname, $gid, $old_trname, $old_gid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        header("Location: " . preg_replace('/[?&]edit_trname=[^&]+/', '', $_SERVER['REQUEST_URI']));
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
        header("Location: " . preg_replace('/[?&]confirm_delete_trname=[^&]+/', '', $_SERVER['REQUEST_URI']));
        exit;
    }

    if ($action === 'cancel') {
        header("Location: " . preg_replace('/[?&]edit_trname=[^&]+/', '', $_SERVER['REQUEST_URI']));
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
        header("Location: " . preg_replace('/([?&])(confirm_delete_trname|confirm_delete_gid)=[^&]+(&?)/', '$1', $_SERVER['REQUEST_URI']));
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

// Build query
if ($filter_gid > 0) {
    $stmt = mysqli_prepare($conn, "SELECT trname, gruppe_id FROM `{$trainer_table}` WHERE gruppe_id = ? ORDER BY trname ASC");
    mysqli_stmt_bind_param($stmt, 'i', $filter_gid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $res = mysqli_query($conn, "SELECT trname, gruppe_id FROM `{$trainer_table}` ORDER BY trname ASC");
}

if ($res && mysqli_num_rows($res) > 0) {
    echo "<table style='table-layout:fixed; width:100%; border-collapse:collapse;'>";
    echo "<tr><th style='border:1px solid #ccc; padding:6px;'>Name</th><th style='border:1px solid #ccc; padding:6px;'>Gruppe</th><th style='border:1px solid #ccc; padding:6px;'>Bearbeiten</th><th style='border:1px solid #ccc; padding:6px;'>Löschen</th></tr>";
    while ($row = mysqli_fetch_assoc($res)) {
        $trname = $row['trname'];
        $gid = (int)$row['gruppe_id'];
        $display_name = htmlspecialchars($trname, ENT_QUOTES, 'UTF-8');
        $display_group = isset($groups[$gid]) ? htmlspecialchars($groups[$gid], ENT_QUOTES, 'UTF-8') : '–';

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
            $edit_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?edit_trname=' . urlencode($trname) . '&edit_gid=' . $gid, ENT_QUOTES, 'UTF-8');
            echo "<td style='border:1px solid #ccc; padding:6px; text-align:center;'><a href='{$edit_url}' title='Bearbeiten'>✏️</a></td>";
            $confirm_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?confirm_delete_trname=' . urlencode($trname) . '&confirm_delete_gid=' . $gid, ENT_QUOTES, 'UTF-8');
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