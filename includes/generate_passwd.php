<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plain = $_POST['plain'] ?? '';
    if ($plain !== '') {
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        echo "<p><strong>Hash:</strong><br><code>$hash</code></p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Passwort-Hash-Generator</title></head>
<body>
    <h2>🔐 Passwort-Hash erzeugen</h2>
    <form method="post">
        <label>Passwort: <input type="text" name="plain" required></label>
        <button type="submit">Hash erzeugen</button>
    </form>
</body>
</html>

