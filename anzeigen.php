<?php
require 'include_public.php';
#define('IN_SCRIPT', true);
#require 'config.php';

$pdo = get_db_connection();

$filter = $_GET['filter'] ?? 'heute';
$heute = new DateTimeImmutable('today');
$eintraege = [];

switch ($filter) {
    case 'heute':
        $stmt = $pdo->prepare("SELECT * FROM hallenbuch WHERE datum = ?");
        $stmt->execute([$heute->format('Y-m-d')]);
        break;
    case 'woche':
        $start = $heute->modify('monday this week');
        $ende = $start->modify('+6 days');
        $stmt = $pdo->prepare("SELECT * FROM hallenbuch WHERE datum BETWEEN ? AND ?");
        $stmt->execute([$start->format('Y-m-d'), $ende->format('Y-m-d')]);
        break;
    default:
        $stmt = $pdo->query("SELECT * FROM hallenbuch ORDER BY datum DESC");
}

$eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Hallenbuch – Anzeige</title>
</head>
<body>
    <h2>Hallenbuch-Einträge</h2>
    <form method="get">
        <select name="filter">
            <option value="heute" <?= $filter === 'heute' ? 'selected' : '' ?>>Heute</option>
            <option value="woche" <?= $filter === 'woche' ? 'selected' : '' ?>>Diese Woche</option>
            <option value="alle" <?= $filter === 'alle' ? 'selected' : '' ?>>Alle</option>
        </select>
        <button type="submit">Filtern</button>
    </form>
    <table border="1" cellpadding="5" style="margin-top:1em;">
        <tr>
            <th>Datum</th><th>Uhrzeit</th><th>Gruppe</th><th>Leiter/in</th><th>Vermerk</th>
        </tr>
        <?php foreach ($eintraege as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['datum']) ?></td>
            <td><?= htmlspecialchars($e['von']) ?>–<?= htmlspecialchars($e['bis']) ?></td>
            <td><?= htmlspecialchars($e['gruppe']) ?></td>
            <td><?= htmlspecialchars($e['leiter']) ?></td>
            <td><?= nl2br(htmlspecialchars($e['vermerk'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

