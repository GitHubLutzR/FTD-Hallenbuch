<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include.php');

$conn = get_db_connection();

global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'gruppen';

// Aktion verarbeiten (löschen / speichern)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF: optional prüfen (nicht implementiert hier)
    $action = $_POST['action'] ?? '';
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
        header("Location: " . $_SERVER['REQUEST_URI']);
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

// Hole ggf. edit_id aus GET
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

$sql = "SELECT * FROM $table ORDER BY name ASC";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  echo "<h3>Liste der Gruppen:</h3>";

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

        // Delete-Form (POST) mit JS-Confirm
        echo "<td style='border:1px solid #ccc; padding:4px; text-align:center;'>
                <form method='post' onsubmit=\"return confirm('Gruppe löschen? Diese Aktion kann nicht rückgängig gemacht werden.');\" style='margin:0;'>
                  <input type='hidden' name='action' value='delete'>
                  <input type='hidden' name='id' value='{$id}'>
                  <button type='submit' title='Löschen' style='background:none;border:none;cursor:pointer;padding:2px;'>🗑️</button>
                </form>
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
