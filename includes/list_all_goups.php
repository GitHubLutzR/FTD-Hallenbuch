<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include.php');

$conn = get_db_connection();

global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'gruppen';

// Wenn ein Lösch-Request per POST kommt (Bestätigungssubmit), ausführen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE: neue Gruppe anlegen
    if ($action === 'create' && isset($_POST['name'])) {
        $name = trim($_POST['name']);
        if ($name !== '') {
            $stmt = mysqli_prepare($conn, "INSERT INTO `{$table}` (`name`) VALUES (?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $name);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        // zurück zur Liste (ohne ?new=1)
        $loc = preg_replace('/([?&])new=1(&?)/', '$1', $_SERVER['REQUEST_URI']);
        $loc = rtrim($loc, '?&');
        header("Location: " . $loc);
        exit;
    }

    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM `{$table}` WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        header("Location: " . preg_replace('/[?&]confirm_delete=\d+/', '', $_SERVER['REQUEST_URI']));
        exit;
    }

    if ($action === 'save' && isset($_POST['id'], $_POST['name'])) {
        $id = (int) $_POST['id'];
        $name = trim($_POST['name']);
        if ($id > 0 && $name !== '') {
            $safe_name = $name;
            $stmt = mysqli_prepare($conn, "UPDATE `{$table}` SET `name` = ? WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $safe_name, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        header("Location: " . preg_replace('/[?&]edit_id=\d+/', '', $_SERVER['REQUEST_URI']));
        exit;
    }

    if ($action === 'cancel') {
        header("Location: " . preg_replace('/[?&]edit_id=\d+/', '', $_SERVER['REQUEST_URI']));
        exit;
    }
}

// Wenn Confirm-GET gesetzt ist -> zeige PHP-Bestätigungsseite (kein JS-Dialog)
if (isset($_GET['confirm_delete'])) {
    $confirm_id = (int)$_GET['confirm_delete'];
    if ($confirm_id <= 0) {
        header("Location: " . preg_replace('/[?&]confirm_delete=\d+/', '', $_SERVER['REQUEST_URI']));
        exit;
    }
    // Gruppe holen
    $stmt = mysqli_prepare($conn, "SELECT id, name FROM `{$table}` WHERE id = ? LIMIT 1");
    $group = null;
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $confirm_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && ($row = mysqli_fetch_assoc($res))) {
            $group = $row;
        }
        mysqli_stmt_close($stmt);
    }

    if (!$group) {
        echo "<p>Gruppe nicht gefunden.</p>";
        exit;
    }

    $gname = htmlspecialchars(html_entity_decode($group['name'] ?? '', ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    // Bestätigungsformular
    echo "<h3>Gruppe löschen</h3>";
    echo "<p>Bitte bestätigen: Die Gruppe <strong>{$gname}</strong> wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.</p>";
    echo "<form method='post' style='display:flex; gap:8px; align-items:center;'>";
    echo "<input type='hidden' name='action' value='delete'>";
    echo "<input type='hidden' name='id' value='{$confirm_id}'>";
    echo "<button type='submit' style='background:#c00;color:#fff;border:none;padding:6px 10px;cursor:pointer;'>Gruppe löschen</button>";
    // Abbrechen -> zurück zur Liste (entferne confirm_delete aus URL)
    $back = preg_replace('/([?&])confirm_delete=\d+(&?)/', '$1', $_SERVER['REQUEST_URI']);
    $back = rtrim($back, '?&');
    echo " <a href='".htmlspecialchars($back, ENT_QUOTES, 'UTF-8')."' style='padding:6px 10px; background:#eee; text-decoration:none; color:#000; border:1px solid #ccc;'>Abbrechen</a>";
    echo "</form>";
    exit;
}

// Hole ggf. edit_id aus GET
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

$sql = "SELECT * FROM $table ORDER BY name ASC";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  // Header mit Abstand und "Neue Gruppe"-Knopf
  echo "<div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;'>";
  echo "<h3 style='margin:0;'>Liste der Gruppen:</h3>";
  // Button: öffnet Formular (?new=1)
  $new_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?new=1', ENT_QUOTES, 'UTF-8');
  echo "<a href='{$new_url}' style='padding:8px 12px; background:#2b7cff; color:#fff; text-decoration:none; border-radius:4px;'>➕ Neue Gruppe</a>";
  echo "</div>";

  // Falls ?new=1 gesetzt ist, zeige das Eingabeformular oberhalb der Tabelle
  if (isset($_GET['new']) && $_GET['new'] == '1') {
      echo "<form method='post' style='display:flex; gap:8px; align-items:center; margin-bottom:12px;'>";
      echo "<input type='hidden' name='action' value='create'>";
      echo "<input type='text' name='name' placeholder='Name der neuen Gruppe' required style='flex:1;padding:6px;border:1px solid #ccc;border-radius:4px;'>";
      echo "<button type='submit' style='padding:6px 10px; background:#28a745;color:#fff;border:none;border-radius:4px;'>Anlegen</button>";
      $cancel_url = preg_replace('/([?&])new=1(&?)/', '$1', $_SERVER['REQUEST_URI']);
      $cancel_url = rtrim($cancel_url, '?&');
      echo " <a href='".htmlspecialchars($cancel_url, ENT_QUOTES, 'UTF-8')."' style='padding:6px 10px; background:#eee; text-decoration:none; color:#000; border:1px solid #ccc; border-radius:4px;'>Abbrechen</a>";
      echo "</form>";
  }

  echo "<table class='last-entries' style='table-layout: fixed; width: 100%; border-collapse: collapse;'>";
  echo "<tr>";
  echo "<th style='width:70%; border:1px solid #ccc; padding:4px;'>Gruppenname</th>";
  echo "<th style='width:15%; border:1px solid #ccc; padding:4px;'>Bearbeiten</th>";
  echo "<th style='width:15%; border:1px solid #ccc; padding:4px;'>Löschen</th>";
  echo "</tr>";

  while ($row = mysqli_fetch_assoc($result)) {
    $id = (int)$row['id'];
    $name = html_entity_decode($row['name'] ?? '', ENT_QUOTES, 'UTF-8');
    echo "<tr>";
    if ($edit_id === $id) {
        // Inline-Edit-Formular
        $escaped = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        echo "<td style='border:1px solid #ccc; padding:4px;'>
                <form method='post' style='margin:0; display:flex; gap:8px; align-items:center;'>
                  <input type='hidden' name='action' value='save'>
                  <input type='hidden' name='id' value='{$id}'>
                  <input type='text' name='name' value=\"{$escaped}\" style='flex:1;'>
                  <button type='submit'>Speichern</button>
                  <button type='submit' name='action' value='cancel'>Abbrechen</button>
                </form>
              </td>";
        echo "<td style='border:1px solid #ccc; padding:4px; text-align:center;'>-</td>";
        echo "<td style='border:1px solid #ccc; padding:4px; text-align:center;'>-</td>";
    } else {
        // Anzeige mit Aktionen
        $display = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        echo "<td style='border:1px solid #ccc; padding:4px;'>$display</td>";

        // Edit-Link (setzt GET edit_id)
        $edit_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?edit_id=' . $id, ENT_QUOTES, 'UTF-8');
        echo "<td style='border:1px solid #ccc; padding:4px; text-align:center;'>
                <a href='{$edit_url}' title='Bearbeiten' style='text-decoration:none;'>✏️</a>
              </td>";

        // Delete-Link: führt zur PHP-Bestätigungsseite (kein JS-Popup)
        $confirm_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?confirm_delete=' . $id, ENT_QUOTES, 'UTF-8');
        echo "<td style='border:1px solid #ccc; padding:4px; text-align:center;'>
                <a href='{$confirm_url}' title='Löschen' style='text-decoration:none;color:#900;'>🗑️</a>
              </td>";
    }
    echo "</tr>";
  }

  echo "</table>";
} else {
  echo "<p>Keine Gruppen gefunden.</p>";
}

mysqli_free_result($result);
mysqli_close($conn);
?>
