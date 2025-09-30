<?php
session_start();
define('IN_SCRIPT', true);
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        $_SESSION['user'] = $user['username'];
        header('Location: admin.php');
        exit;
    } else {
        $error = "❌ Login fehlgeschlagen.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><title>Login</title></head>
<body>
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p>$error</p>"; ?>
    <form method="post">
        <label>Benutzername: <input name="username" required></label><br>
        <label>Passwort: <input type="password" name="password" required></label><br>
        <button type="submit">Einloggen</button>
    </form>
</body>
</html>

