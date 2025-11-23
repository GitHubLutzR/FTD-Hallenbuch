<?php
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/include_public.php');

// Ensure session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$base_url = '/hallenbuch/';

// Debug: nur in error_log wenn aktiviert
if (!empty($hesk_settings['debug'])) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', sys_get_temp_dir() . '/php_errors.log');
    error_reporting(E_ALL);
}

// Redirect if already logged in
if (!empty($_SESSION['user'])) {
    header('Location: ' . $base_url . 'index.php');
    exit;
}

$login_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['user'] ?? ''));
    $password = (string)($_POST['pass'] ?? '');

    if ($username === '' || $password === '') {
        $login_error = true;
    } else {
        $conn = get_db_connection();
        if (!$conn) {
            error_log('[login] DB connection failed');
            $login_error = true;
        } else {
            mysqli_set_charset($conn, 'utf8mb4');

            global $hesk_settings;
            $hesk_prefix = $hesk_settings['db_pfix'] ?? ($hesk_settings['db_hb_pfix'] ?? '');
            $hesk_table = $hesk_prefix ? preg_replace('/[^A-Za-z0-9_]/', '', $hesk_prefix) . 'users' : 'hesk_users';

            // determine username/password columns
            $user_col = 'user';
            $pass_col = 'pass';
            $colsRes = @mysqli_query($conn, "SHOW COLUMNS FROM `{$hesk_table}`");
            if ($colsRes) {
                while ($c = mysqli_fetch_assoc($colsRes)) {
                    $col = $c['Field'];
                    if (in_array($col, ['user','username','user_name','user'], true)) $user_col = $col;
                    if (in_array($col, ['pass','password','passwd'], true)) $pass_col = $col;
                }
                mysqli_free_result($colsRes);
            }

            $sql = "SELECT `{$pass_col}` AS stored_pass, `name` AS name FROM `{$hesk_table}` WHERE `{$user_col}` = ? LIMIT 1";
            $stmt = @mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $username);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) === 1) {
                    mysqli_stmt_bind_result($stmt, $stored_pass, $name);
                    mysqli_stmt_fetch($stmt);

                    $stored_pass = (string)trim((string)$stored_pass);
                    $given = (string)$password;

                    if (!function_exists('hesk_Pass2Hash')) {
                        function hesk_Pass2Hash($plaintext) {
                            $majorsalt  = '';
                            $len = strlen($plaintext);
                            for ($i=0;$i<$len;$i++) {
                                $majorsalt .= sha1(substr($plaintext,$i,1));
                            }
                            return sha1($majorsalt);
                        }
                    }

                    $h_hesk = hesk_Pass2Hash($given);

                    $pw_ok = false;
                    if (is_string($stored_pass) && strlen($stored_pass) === strlen($h_hesk) && hash_equals($stored_pass, $h_hesk)) {
                        $pw_ok = true;
                    }
                    if (!$pw_ok && function_exists('password_verify') && password_verify($given, $stored_pass)) {
                        $pw_ok = true;
                    }

                    if (!empty($hesk_settings['debug'])) {
                        error_log('[login] user=' . $username . ' pw_ok=' . ($pw_ok ? '1' : '0') . ' hesk_table=' . $hesk_table);
                    }

                    if ($pw_ok) {
                        $_SESSION['user'] = $username;
                        $_SESSION['name'] = $name;
                        mysqli_stmt_close($stmt);
                        mysqli_close($conn);
                        header('Location: ' . $base_url . 'index.php');
                        exit;
                    }
                }
                mysqli_stmt_close($stmt);
            } else {
                if (!empty($hesk_settings['debug'])) {
                    error_log('[login] prepare failed: ' . mysqli_error($conn) . ' -- SQL: ' . $sql);
                }
            }
            mysqli_close($conn);
            $login_error = true;
        }
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

    <form method="post" autocomplete="off">
        <label>Benutzername: <input type="text" name="user" required></label><br>
        <label>Passwort: <input type="password" name="pass" required></label><br>
        <button type="submit">Login</button>
    </form>

    <div style="margin:12px 0 30px 0; font-size:0.9em; color:#666;">
      Hinweis: Die Benutzerverwaltung und Passwörter werden in der HESK‑Installation verwaltet.
      Änderungen an Benutzern oder Passwörtern bitte in HESK vornehmen.
    </div>
</body>
</html>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
