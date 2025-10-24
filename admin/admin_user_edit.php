<?php
require_once(__DIR__ . '/../config.php');
//session_start();

// Zugriffsschutz
if (!isset($_SESSION['user'])) {
    echo "<p>⛔ Kein Zugriff. Bitte <a href='login.php'>einloggen</a>.</p>";
    exit;
}

$id = $_GET['id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $stmt = $pdo->prepare("UPDATE hb_user SET username = ? WHERE id = ?");
    $stmt->execute([$username, $id]);
    echo "Benutzername aktualisiert.";
} else {
    $stmt = $pdo->prepare("SELECT username FROM hb_user WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    echo "<form method='post'>
        Benutzername: <input name='username' value='{$user['username']}'>
        <button type='submit'>Speichern</button>
    </form>";
}
?>

