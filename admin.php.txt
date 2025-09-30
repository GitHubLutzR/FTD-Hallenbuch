<?php
require 'include.php';
#define('IN_SCRIPT', true);
#require 'config.php';

$pdo = get_db_connection();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM hallenbuch WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin.php");
    exit;
}

$filter = $_GET['export'] ?? null;
$eintraege = [];

if ($filter === 'woche') {
    $start = (new DateTimeImmutable('monday this week'))->format('Y-m-d');
    $ende = (new DateTimeImmutable('sunday this week'))->format('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM hallenbuch WHERE datum BETWEEN ? AND ?");
    $stmt->execute([$start, $ende]);
    $eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($filter === 'monat') {
    $monat = date('Y-m');
    $stmt = $pdo->prepare("SELECT * FROM hallenbuch WHERE DATE_FORMAT(datum, '%Y-%m') = ?");
    $stmt->execute([$monat]);
    $eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM hallenbuch ORDER BY datum DESC");
    $eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Hallenbuch Admin</title>
</head>
<body>
    <h2>Admin-Ansicht</h2>
    <form method="get">
        <button name="export" value="woche">Export Woche (PDF)</button>
        <button name="export" value="monat">Export Monat (PDF)</button>
    </form>
    <table border="1" cellpadding="5" style="margin-top:1em;">
        <tr>
            <th>Datum</th><th>Uhrzeit</th><th>Gruppe</th><th>Leiter/in</th><th>Vermerk</th><th>Löschen</th>
        </tr>
        <?php foreach ($eintraege as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['datum']) ?></td>
            <td><?= htmlspecialchars($e['von']) ?>–<?= htmlspecialchars($e['bis']) ?></td>
            <td><?= htmlspecialchars($e['gruppe']) ?></td>
            <td><?= htmlspecialchars($e['leiter']) ?></td>
            <td><?= nl2br(htmlspecialchars($e['vermerk'])) ?></td>
            <td><a href="admin.php?delete=<?= $e['id'] ?>" onclick="return confirm('Wirklich löschen?')">🗑️</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
<?php #<a href="export.php?filter=woche">📄 Woche als PDF</a> ?>
<?php #<a href="export.php?filter=monat">📄 Monat als PDF</a> ?>

