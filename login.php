<?php
require_once(__DIR__ . '/config.php');
// Stelle sicher: session_set_cookie_params(...) und session_start() bereits aufgerufen
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$base_url = '/hallenbuch/'; 

// Debug aktivieren (nur wenn Debug-Flag gesetzt)
if (!empty($hesk_settings['debug'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', sys_get_temp_dir() . '/php_errors.log');
    error_reporting(E_ALL);
    register_shutdown_function(function() {
        $err = error_get_last();
        if ($err) {
            error_log('SHUTDOWN ERROR: ' . print_r($err, true));
        }
    });
}

// Zugriffsschutz: bereits eingeloggt
if (isset($_SESSION['user'])) {
    header('Location: ' . $base_url . 'admin/admin_menu.php');
    exit;
}

$login_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['user'] ?? '');
    $password = $_POST['pass'] ?? '';

    if ($username === '' || $password === '') {
        $login_error = true;
    } else {
        $conn = get_db_connection();
        global $hesk_settings;

        // bestimme HESK-User-Tabelle (Fallback auf 'hesk_users')
        $hesk_prefix = $hesk_settings['db_pfix'] ?? ($hesk_settings['db_hb_pfix'] ?? '');
        $hesk_table = $hesk_prefix ? $hesk_prefix . 'users' : 'hesk_users';

        // prüfe, welche Spalten vorhanden sind (username-field und pass-field)
        $user_col = null;
        $pass_col = null;

        $colsRes = mysqli_query($conn, "SHOW COLUMNS FROM `{$hesk_table}`");
        if ($colsRes) {
            while ($c = mysqli_fetch_assoc($colsRes)) {
                $col = $c['Field'];
                if (in_array($col, ['user','username','user_name','user'])) $user_col = $col;
                if (in_array($col, ['pass','password','passwd'])) $pass_col = $col;
            }
            mysqli_free_result($colsRes);
        }

        // Fallbacks falls nicht erkannt
        if (!$user_col) $user_col = 'user';
        if (!$pass_col) $pass_col = 'pass';

        // sichere Prepared-Query
        $sql = "SELECT `{$pass_col}` AS stored_pass, `name` AS name FROM `{$hesk_table}` WHERE `{$user_col}` = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 1) {
                mysqli_stmt_bind_result($stmt, $stored_pass, $name);
                mysqli_stmt_fetch($stmt);

                // normalize
                $stored_pass = trim((string)$stored_pass);
                $given     = (string)$password;

                // HESK-Password-Hash (hesk_Pass2Hash) verwenden
                if (!function_exists('hesk_Pass2Hash')) {
                    function hesk_Pass2Hash($plaintext) {
                        $majorsalt  = '';
                        $len = strlen($plaintext);
                        for ($i=0;$i<$len;$i++) {
                            $majorsalt .= sha1(substr($plaintext,$i,1));
                        }
                        $corehash = sha1($majorsalt);
                        return $corehash;
                    }
                }

                $h_hesk = hesk_Pass2Hash($given);

                // Prüfung: zuerst HESK-Hash, fallback auf password_verify() falls DB moderner Hash enthält
                $pw_ok = false;
                if (is_string($stored_pass) && hash_equals($stored_pass, $h_hesk)) {
                    $pw_ok = true;
                }
                if (!$pw_ok && function_exists('password_verify') && password_verify($given, $stored_pass)) {
                    $pw_ok = true;
                }

                // Debug-Ausgaben (sichtbar nur wenn debug-Flag gesetzt)
                if (!empty($hesk_settings['debug'])) {
                    $target_hash = 'a26c3bb15017c9ef8833eda1e4c22571077e22a9';
                    $db_hash_escaped = htmlspecialchars($stored_pass, ENT_QUOTES, 'UTF-8');
                    echo "<!-- DEBUG LOGIN: hesk_table={$hesk_table}, user_col={$user_col}, pass_col={$pass_col} -->\n";
                    echo "<!-- DB stored_pass: {$db_hash_escaped} -->\n";
                    echo "<!-- computed hesk_Pass2Hash: {$h_hesk} -->\n";
                    echo "<!-- compare hesk vs DB: " . (hash_equals($stored_pass, $h_hesk) ? 'MATCH' : 'DIFFERENT') . " -->\n";
                    echo "<!-- compare to target: {$target_hash} -->\n";
                    $cmp = (hash_equals($stored_pass, $target_hash) ? 'MATCH' : 'DIFFERENT');
                    echo "<!-- target comparison: {$cmp} -->\n";
                    echo "<!-- password_verify supported: " . (function_exists('password_verify') ? 'yes' : 'no') . " -->\n";
                    echo "<!-- pw_ok: " . ($pw_ok ? 'true' : 'false') . " -->\n";
                }

                if ($pw_ok) {
                    // erfolgreich
                    $_SESSION['user'] = $username;
                    $_SESSION['name'] = $name;
                    //header('Location: ' . $base_url . 'admin/admin_menu.php');
                    header('Location: ' . $base_url . 'index.php');
                    exit;
                }
                // Ende Fetch/Check
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($conn);
        $login_error = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Admin-Login</title></head>
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

<!-- Hinweis: Benutzerverwaltung/Passwörter in HESK -->
<div style="margin:12px 0 30px 0; font-size:0.9em; color:#666;">
  Hinweis: Die Benutzerverwaltung und Passwörter werden in der HESK‑Installation verwaltet.
  Änderungen an Benutzern oder Passwörtern bitte in HESK vornehmen.
</div>

<?php require_once 'includes/footer.php'; ?>
