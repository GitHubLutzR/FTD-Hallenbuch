<?php
define('IN_SCRIPT', true);
require 'config.php';

try {
    $pdo = get_db_connection();

    $stmt = $pdo->prepare("
        INSERT INTO hallenbuch (datum, von, bis, gruppe, leiter, vermerk)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $bemerkung = isset($_POST['bemerkung']) ? trim($_POST['bemerkung']) : '';

    $stmt->execute([
        $_POST['datum'],
        $_POST['von'],
        $_POST['bis'],
        $_POST['gruppe'],
        $_POST['leiter'],
        $_POST['vermerk']
    ]);

    echo "<p>✅ Eintrag erfolgreich gespeichert.</p>";
    echo "<p><a href='index.php'>Zurück zum Formular</a></p>";

} catch (Exception $e) {
    echo "<p>❌ Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

