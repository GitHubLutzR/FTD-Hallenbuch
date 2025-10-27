<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include.php');

echo "<h1>Admin-Menü</h1>";
echo "<ul>
    <li><a href='../index.php'><span aria-hidden='true'>🏠</span> Startseite</a></li>
    <li><a href='../includes/list_all_trainers.php'><span aria-hidden='true'>👤</span> Trainer</a></li>
    <li><a href='../includes/list_all_goups.php'><span aria-hidden='true'>👥</span> Gruppen</a></li>
    <li><a href='../includes/list_trainers-groups.php'><span aria-hidden='true'></span> Trainer2</a></li>
    <li><a href='../includes/list_entries_for_delete.php'>🗑️ Einträge löschen (manuelle ID nötig)</a></li>
    <li><a href='../logout.php'>🚪 Logout</a></li>
</ul>";
require_once(__DIR__ . '/footer.php');
?>

