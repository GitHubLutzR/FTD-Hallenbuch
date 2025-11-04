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

// Navigation links je nach Login-Status bauen (wird mittig angezeigt)
$navLinks = [];
if (!empty($_SESSION['user'])) {
    $navLinks = [
        ['url' => $base_url, 'label' => '🏠 Startseite'],
        ['url' => $base_url . 'includes/list_groups-trainer.php', 'label' => '👥 Gruppen'],
        ['url' => $base_url . 'includes/list_groups-trainer.php', 'label' => 'G-T'],
        ['url' => $base_url . 'includes/edit_trainers.php', 'label' => '👤 Trainer'],
        ['url' => $base_url . 'includes/list_trainers-groups.php', 'label' => 'T-G'],
        ['url' => $base_url . 'includes/edit_entries.php', 'label' => '🗑️ Einträge bearbeiten'],
    ];
} else {
    $navLinks = [
        ['url' => $base_url, 'label' => '🏠 Startseite'],
        ['url' => $base_url . 'includes/list_groups-trainer.php', 'label' => '👥 Gruppe-Trainer-Liste'],
        ['url' => $base_url . 'includes/list_trainers-groups.php', 'label' => '👤 Trainer-Gruppen-Liste'],
        ['url' => $base_url . 'includes/view_entries.php', 'label' => '🗑️ Einträge anzeigen'],
    ];
}

// Nutzer-/Login-Bereich rechts bauen
$userHtml = '';
if (!empty($_SESSION['user'])) {
    $userEsc = htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8');
    $userHtml .= "<div style='text-align:right;'>";
    $userHtml .= "👤 Angemeldet als <strong>{$userEsc}</strong>";
    $userHtml .= " | <a href='" . htmlspecialchars($base_url . "admin/admin_menu.php", ENT_QUOTES, 'UTF-8') . "'>Admin-Menü</a>";
    $userHtml .= " | <a href='" . htmlspecialchars($base_url . "logout.php", ENT_QUOTES, 'UTF-8') . "'>🚪 Logout</a>";
    // Eintragszähler-Reset
    if (isset($_SESSION['entry_count']) && $_SESSION['entry_count'] >= 3) {
        $userHtml .= " <span style='margin-left:12px; display:inline-block; vertical-align:middle;'>";
        $userHtml .= "<form method='post' style='display:inline; margin:0;'>";
        $userHtml .= "<button type='submit' name='reset_session' style='font-size:0.8em;'>🔄 Eintragszähler zurücksetzen</button>";
        $userHtml .= "</form>";
        $userHtml .= "</span>";
    }
    $userHtml .= "</div>";
} else {
    $userHtml = "<div style='text-align:right;'><a href='" . htmlspecialchars($base_url . "login.php", ENT_QUOTES, 'UTF-8') . "'>🔐 Admin-Login</a></div>";
}

// Nav HTML mittig
$navHtml = "<nav style='text-align:center;'>";
foreach ($navLinks as $lnk) {
    $navHtml .= "<a href='" . htmlspecialchars($lnk['url'], ENT_QUOTES, 'UTF-8') . "' style='margin:0 10px; text-decoration:none; color:inherit; white-space:nowrap;'>" . htmlspecialchars($lnk['label'], ENT_QUOTES, 'UTF-8') . "</a>";
}
$navHtml .= "</nav>";
?>

<header style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
    <div style="flex:0 0 auto;">
        <h1 style="margin:0 12px 0 0;">FTD Hallenbuch</h1>
    </div>

    <div style="flex:1 1 auto; display:flex; justify-content:center; align-items:center;">
        <?php echo $navHtml; ?>
    </div>

    <div style="flex:0 0 auto; min-width:220px;">
        <?php echo $userHtml; ?>
    </div>
</header>

<main>
<?php
if ($reset_msg) {
    echo $reset_msg;
}
?>
