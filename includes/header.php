<?php
// Sicherstellen, dass eine Session aktiv ist (falls config.php das nicht schon macht)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Basis-URL definieren
$base_url = '/hallenbuch/';

// Reset-Anfrage vor der Ausgabe verarbeiten
$reset_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_session'])) {
    unset($_SESSION['entry_count'], $_SESSION['eintrags_reset']);
    $reset_msg = "<div style='text-align:right;'><p>✅ Eintragszähler wurde zurückgesetzt.</p></div>";
}
?>

<header style="display:flex; justify-content:space-between; align-items:center;">
    <h1 style="margin:0 0 6px 0;">FTD Hallenbuch</h1>

    <div style="display:flex; gap:12px; align-items:center; font-size:0.95em; margin-bottom:12px;">
      <a href="<?php echo htmlspecialchars($base_url ?? '/hallenbuch/', ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration:none; color:inherit; white-space:nowrap;">
        <span aria-hidden="true">🏠</span>&nbsp;Startseite
      </a>

      <a href="<?php echo htmlspecialchars(($base_url ?? '/hallenbuch/') . 'includes/list_all_trainers.php', ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration:none; color:inherit; white-space:nowrap;">
        <span aria-hidden="true">👤</span>&nbsp;Trainer
      </a>

      <a href="<?php echo htmlspecialchars(($base_url ?? '/hallenbuch/') . 'includes/list_all_goups.php', ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration:none; color:inherit; white-space:nowrap;">
        <span aria-hidden="true">👥</span>&nbsp;Gruppen
      </a>

      <a href="<?php echo htmlspecialchars(($base_url ?? '/hallenbuch/') . 'includes/list_entries_for_delete.php', ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration:none; color:inherit; white-space:nowrap;">
        <span aria-hidden="true">🗑️</span>&nbsp;Einträge löschen
      </a>
    </div>

    <?php
    // Login/Logout-Leiste rechts unterhalb der Überschrift
    if (!empty($_SESSION['user'])) {
        echo "<div style='text-align:right;'>";
        echo "👤 Angemeldet als <strong>" . htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8') . "</strong>"
           . " | <a href='" . htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . "admin/admin_menu.php'>Admin-Menü</a>"
           . " | <a href='" . htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . "logout.php'>🚪 Logout</a>";
        // Eintragszähler-Reset nach ganz rechts (visuell getrennt)
        if (isset($_SESSION['entry_count']) && $_SESSION['entry_count'] >= 3) {
            echo " <span style='margin-left:12px; display:inline-block; vertical-align:middle;'>";
            echo "<form method='post' style='display:inline; margin:0;'>";
            echo "<button type='submit' name='reset_session' style='font-size:0.8em;'>🔄 Eintragszähler zurücksetzen</button>";
            echo "</form>";
            echo "</span>";
        }
        echo "</div>";
    } else {
        echo "<div style='text-align:right;'><a href='" . htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . "login.php'>🔐 Admin-Login</a></div>";
    }

    if ($reset_msg) {
        echo $reset_msg;
    }
    ?>
</header>

<main>