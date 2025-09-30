<?php
require 'include.php';
#session_start();
#define('IN_SCRIPT', true);
#require 'config.php';

#if (empty($_SESSION['user'])) {
#    header('Location: login.php');
#    exit;
#}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['old_pw'], $user['password_hash'])) {
        $new_hash = password_hash($_POST['new_pw'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$new_hash, $_SESSION['user']]);
        $msg = "✅ Passwort erfolgreich geändert.";
    } else {
        $msg = "❌ Altes Passwort stimmt nicht.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><title>Passwort ändern</title></head>
<body>
    <h2>Passwort ändern</h2>
    <?php if (!empty($msg)) echo "<p>$msg</p>"; ?>
    <form method="post">
        <label>Altes Passwort: <input type="password" name="old_pw" required></label><br>
        <label>Neues Passwort: <input type="password" name="new_pw" required></label><br>
        <button type="submit">Ändern</button>
    </form>
    <p><a href="admin.php">Zurück</a></p>
</body>
</html>

