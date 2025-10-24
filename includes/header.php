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

<header>
    <h1>FTD Hallenbuch</h1>

    <?php
    // Login/Logout-Leiste rechts unterhalb der Überschrift
    if (!empty($_SESSION['user'])) {
        echo "<div class='logout' style='text-align:right;'>"
           . "👤 Angemeldet als <strong>" . htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8') . "</strong>"
           . " | <a href='" . htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . "admin/admin_menu.php'>Admin-Menü</a>"
           . " | <a href='" . htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . "logout.php'>🚪 Logout</a>"
           . "</div>";
    } else {
        echo "<div style='text-align:right;'><a href='" . htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . "login.php'>🔐 Admin-Login</a></div>";
    }

    if ($reset_msg) {
        echo $reset_msg;
    }
    ?>
</header>

<main>