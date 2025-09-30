<?php
session_start();
require_once 'config.php';

// Zugriffsschutz
if (!isset($_SESSION['user'])) {
    echo "<p>⛔ Kein Zugriff. Bitte <a href='login.php'>einloggen</a>.</p>";
    exit;
}

// DB-Verbindung
$conn = get_db_connection();
global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'user';

// Benutzer auslesen
$result = mysqli_query($conn, "SELECT id, username, name, created_at FROM $table ORDER BY id");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin-Portal</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; max-width: 600px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .logout { position: absolute; top: 10px; right: 10px; }
    </style>
</head>
<body>

<div class="logout">
    👤 Angemeldet als <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
    <a href="logout.php">Logout</a>
</div>

<h2>🛠️ Admin-Portal</h2>

<h3>Benutzerübersicht</h3>

<?php
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Benutzername</th><th>Name</th><th>Erstellt am</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>" . date('d.m.Y H:i', strtotime($row['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Keine Benutzer gefunden.</p>";
}

mysqli_close($conn);
?>

</body>
</html>

