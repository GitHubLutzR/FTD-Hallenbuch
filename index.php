<?php
require 'include_public.php';
#define('IN_SCRIPT', true);
#require 'config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Digitales Hallenbuch</title>
    <style>
        body { font-family: sans-serif; margin: 2em; }
        label { display: block; margin-top: 1em; }
        input, textarea { width: 100%; max-width: 400px; }
        button { margin-top: 1em; padding: 0.5em 1em; }
    </style>
</head>
<body>
    <h2>Eintrag ins Hallenbuch</h2>
    <form action="submit.php" method="post">
        <label>Datum:
		<input type="date" name="datum" value="<?= date('Y-m-d') ?>" required>
<?php #            <input type="date" name="datum" required> ?>
        </label>
        <label>Uhrzeit von:
            <input type="time" name="von" required>
        </label>
        <label>Uhrzeit bis:
            <input type="time" name="bis" required>
        </label>
        <label>Gruppe:
<select name="gruppe" required>
<?php
require 'include_public.php';
$pdo = get_db_connection();
$stmt = $pdo->query("SELECT name FROM gruppen ORDER BY name");
foreach ($stmt as $row) {
    echo "<option value=\"" . htmlspecialchars($row['name']) . "\">" . htmlspecialchars($row['name']) . "</option>";
}
?>
</select>

<?php #	    <input type="text" name="gruppe" required> ?>
        </label>
        <label>Übungsleiter/in:
            <input type="text" name="leiter" required>
        </label>
        <label>Vermerke / Mängel:
            <textarea name="vermerk" rows="4"></textarea>
        </label>
        <button type="submit">Eintrag speichern</button>
    </form>
</body>
</html>

