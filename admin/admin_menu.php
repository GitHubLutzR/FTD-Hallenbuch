<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/header.php');
session_start();

// Zugriffsschutz
if (!isset($_SESSION['user'])) {
    echo "<p>⛔ Kein Zugriff. Bitte <a href='../login.php'>einloggen</a>.</p>";
    exit;
}


echo "<h1>Admin-Menü</h1>";
echo "<ul>
    <li><a href='../index.php'> Startseite</a></li>
    <li><a href='admin_users.php'>👥 Benutzerübersicht</a></li>
    <li><a href='admin_user_new.php'>➕ Benutzer anlegen</a></li>
    <li><a href='admin_user_edit.php'>✏️ Benutzer bearbeiten (manuelle ID nötig)</a></li>
    <li><a href='admin_user_password.php'>🔑 Passwort setzen/zurücksetzen (manuelle ID nötig)</a></li>
    <li><a href='admin_user_delete.php'>🗑️ Benutzer löschen (manuelle ID nötig)</a></li>
    <li><a href='../logout.php'>🚪 Logout</a></li>
    <li><a href='../includes/list_entries_for_delete.php'>🗑️Einträge löschen (manuelle ID nötig)</a></li>
</ul>";
require_once(__DIR__ . '/footer.php');
?>

