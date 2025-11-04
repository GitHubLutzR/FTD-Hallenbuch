<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include.php');

echo "<h1>Admin-Menü</h1>";
echo "<ul>
    <li><a href='../index.php'><span aria-hidden='true'>🏠</span> Startseite</a></li>
    <li><a href='../includes/edit_trainers.php'><span aria-hidden='true'>👤</span> Trainer</a></li>
    <li><a href='../includes/edit_goups.php'><span aria-hidden='true'>👥</span> Gruppen</a></li>
    <li><a href='../includes/list_trainers-groups.php'><span aria-hidden='true'>-</span> Liste der Gruppen pro Trainer/ -innen</a></li>
    <li><a href='../includes/list_groups-trainer.php'><span aria-hidden='true'>-</span> Liste der Trainner/ -innen pro Gruppen</a></li>
    <li><a href='../includes/edit_entries.php'>🗑️ Einträge bearbeiten</a></li>
    <li><a href='../logout.php'>🚪 Logout</a></li>
</ul>";
require_once(__DIR__ . '/footer.php');
?>

