<?php
require_once(__DIR__ . '/../config.php');
session_start();

// Zugriffsschutz
if (!isset($_SESSION['user'])) {
    echo "<p>⛔ Kein Zugriff. Bitte <a href='login.php'>einloggen</a>.</p>";
    exit;
}

$id = $_GET['id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['new_password'];
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE hb_user SET password_hash = ? WHERE id = ?");
    $stmt->execute([$hash, $id]);
    echo "Passwort aktualisiert.";
} else {
    echo "<form method='post'>
        Neues Passwort: <input type='password' name='new_password'>
        <button type='submit'>Setzen</button>
    </form>";
}
?>

