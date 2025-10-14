<?php
require 'include.php';

$pdo = get_db_connection();
$msg = '';

// Gruppe hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['neue_gruppe'])) {
    $name = trim($_POST['neue_gruppe']);
    if ($name !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO gruppen (name) VALUES (?)");
            $stmt->execute([$name]);
            $msg = "✅ Gruppe hinzugefügt: " . htmlspecialchars($name);
        } catch (PDOException $e) {
            $msg = "❌ Gruppe existiert bereits oder Fehler: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Gruppe löschen
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM gruppen WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: gruppen_admin.php");
    exit;
}

// Gruppen laden
$stmt = $pdo->query("SELECT * FROM gruppen ORDER BY name");
$gruppen = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Gruppenverwaltung</title>
</head>
<body>
    <h2>Gruppen verwalten</h2>
    <?php if ($msg) echo "<p>$msg</p>"; ?>
    <form method="post">
        <label>Neue Gruppe:
            <input name="neue_gruppe" required>
        </label>
        <button type="submit">Hinzufügen</button>
    </form>

    <h3>Bestehende Gruppen</h3>
    <table border="1" cellpadding="5">
        <tr><th>Name</th><th>Löschen</th></tr>
        <?php foreach ($gruppen as $g): ?>
        <tr>
            <td><?= htmlspecialchars($g['name']) ?></td>
            <td><a href="gruppen_admin.php?delete=<?= $g['id'] ?>" onclick="return confirm('Gruppe wirklich löschen?')">🗑️</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="admin.php">Zurück zur Admin-Übersicht</a></p>
</body>
</html>

