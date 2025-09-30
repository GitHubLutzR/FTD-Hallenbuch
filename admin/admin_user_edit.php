<?php
require_once 'init.php';
check_admin();

$id = $_GET['id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $stmt = $pdo->prepare("UPDATE hb_users SET username = ? WHERE id = ?");
    $stmt->execute([$username, $id]);
    echo "Benutzername aktualisiert.";
} else {
    $stmt = $pdo->prepare("SELECT username FROM hb_users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    echo "<form method='post'>
        Benutzername: <input name='username' value='{$user['username']}'>
        <button type='submit'>Speichern</button>
    </form>";
}
?>

