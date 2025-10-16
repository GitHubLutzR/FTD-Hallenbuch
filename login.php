<?php
session_start();
require_once 'config.php';

$login_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['user'] ?? '';
    $password = $_POST['pass'] ?? '';

    $conn = get_db_connection();
    global $hesk_settings;
    $table = $hesk_settings['db_hb_pfix'] . 'user';

    $stmt = mysqli_prepare($conn, "SELECT password, name FROM $table WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) === 1) {
        mysqli_stmt_bind_result($stmt, $hashed_password, $name);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user'] = $username;
            $_SESSION['name'] = $name;
            header('Location: index.php');
            #header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    $login_error = true;
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
require_once 'includes/footer.php'; 
?>

<!DOCTYPE html>
<html>
<head><title>Admin-Login</title></head>
<body>
    <h2>🔐 Admin-Login</h2>

    <?php if ($login_error): ?>
        <p style="color:red;">❌ Benutzername oder Passwort falsch.</p>
    <?php endif; ?>

    <form method="post">
        <label>Benutzername: <input type="text" name="user" required></label><br>
        <label>Passwort: <input type="password" name="pass" required></label><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>

