<?php
define('IN_SCRIPT', 1);
require_once 'config.php';

//session_start(); // Session starten für Eintragszähler

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'hallenbuch';
//$mysqli->set_charset('utf8mb4');

// Eintragszähler initialisieren
if (!isset($_SESSION['entry_count'])) {
    $_SESSION['entry_count'] = 0;
}
if (!isset($_SESSION['block_until'])) {
    $_SESSION['block_until'] = 0;
}

// Blockierung prüfen
if ($_SESSION['block_until'] > time()) {
    echo "<p>⛔ Zu viele Einträgei nacheinander. Bitte wenden Sie sich an it@freieturner.com .</p>";
    #echo "<p>⛔ Zu viele Einträge. Bitte warte bis " . date('H:i:s', $_SESSION['block_until']) . ".</p>";
    exit;
}

// Captcha ab 3 Einträgen (Platzhalter)
if ($_SESSION['entry_count'] >= 3 && $_SESSION['entry_count'] < 5) {
    if (empty($_POST['captcha']) || $_POST['captcha'] !== 'FTD') {
        echo "<p>🔒 Bitte bestätige, dass du kein Bot bist.</p>";
        echo "<form method='post'>";
        foreach ($_POST as $key => $val) {
            echo "<input type='hidden' name='$key' value='" . htmlspecialchars($val) . "'>";
        }
        echo "<label>Captcha (Gib 'FTD' ein): <input name='captcha'></label>";
        echo "<button type='submit'>Bestätigen</button>";
        echo "</form>";
        //exit;
    }
}

// Blockierung ab 5 Einträgen
if ($_SESSION['entry_count'] >= 5) {
    $_SESSION['block_until'] = time() + (120 * 60); // 30 Minuten Sperre
    echo "<p>⛔ Du hast zu viele Einträge gemacht. Bitte warte 30 Minuten.</p>";
    exit;
}

// Eingaben aus dem Formular
$datum     = $_POST['datum']     ?? '';
$von       = $_POST['von']       ?? '';
$bis       = $_POST['bis']       ?? '';
$gruppe    = htmlentities($_POST['gruppe']    ?? '', ENT_QUOTES, 'UTF-8');
$trainer    = htmlentities($_POST['trainer'] ?? $_POST['leiter'] ?? '', ENT_QUOTES, 'UTF-8');
$vermerk   = htmlentities($_POST['vermerk']   ?? '', ENT_QUOTES, 'UTF-8');
$bemerkung = htmlentities($_POST['bemerkung'] ?? '', ENT_QUOTES, 'UTF-8');
$gruppe_sonstige = htmlentities($_POST['gruppe_sonstige'] ?? '', ENT_QUOTES, 'UTF-8');

//$gruppe    = $_POST['gruppe']    ?? '';
//$trainer    = $_POST['leiter']    ?? '';
//$vermerk   = $_POST['vermerk']   ?? '';
//$bemerkung = $_POST['bemerkung'] ?? '';
//$gruppe_sonstige = $_POST['gruppe_sonstige'] ?? '';

// Pflichtfeldprüfung
if (!$datum || !$von || !$bis || !$gruppe || !$trainer) {
    echo "<p>❌ Fehler: Bitte alle Pflichtfelder ausfüllen.</p>";
    if ($hesk_settings['debug']) {
        echo "<pre>Übermittelte Formulardaten:\n";
        print_r($_POST);
        echo "</pre>";
    }  
    exit;
}

// Gruppe "sonstige" umschreiben
if (strtolower($gruppe) === 'sonstige') {
    $gruppe_sonstige = trim($gruppe_sonstige);
    if ($gruppe_sonstige === '') {
        die("❌ Fehler: Bitte Gruppe angeben, wenn 'sonstige' gewählt wurde.");
        if ($hesk_settings['debug']) {
        echo "<pre>Übermittelte Formulardaten:\n";
        print_r($_POST);
        echo "</pre>";
    }
    }
    $gruppe = $gruppe_sonstige; // Überschreibt die Gruppenwahl mit dem Freitext
}

// Kombinierten Zeitstempel erzeugen
$eintragZeit = strtotime("$datum $von");
$jetzt = time();

// Maximal erlaubte Zukunft: 1 Stunde
$maxZukunft = $jetzt + 3600;

// Validierung
if ($eintragZeit > $maxZukunft) {
        if ($hesk_settings['debug']) {
        $debug = base64_encode(json_encode([
          'EintragZeit' => date('Y-m-d H:i:s', $eintragZeit),
          'MaxZukunft'  => date('Y-m-d H:i:s', $maxZukunft)
        ]));
        header("Location: index.php?rcsubmit=2&debug=$debug");
//        $params = http_build_query([
//            'rcsubmit'    => 2,
//            'EintragZeit' => date('Y-m-d H:i:s', $eintragZeit),
//            'MaxZukunft'  => date('Y-m-d H:i:s', $maxZukunft)
//        ]);
//        header("Location: index.php?$params");
        exit;
            //echo date_default_timezone_get();
        }
        header("Location: index.php?rcsubmit=2");
        exit;
//    die("Fehler: Der Eintrag darf nicht in der Zukunft liegen.");
}

// Verbindung zur Datenbank
$conn = mysqli_connect(
    $hesk_settings['db_host'],
    $hesk_settings['db_user'],
    $hesk_settings['db_pass'],
    $hesk_settings['db_name']
);

if (!$conn) {
    echo "<p>❌ DB-Verbindung fehlgeschlagen: " . mysqli_connect_error() . "</p>";
    exit;
}

// SQL vorbereiten
$stmt = mysqli_prepare($conn, "
    INSERT INTO $table (datum, von, bis, gruppe, trainer, vermerk, bemerkung)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo "<p>❌ Fehler beim Vorbereiten der SQL-Anweisung: " . mysqli_error($conn) . "</p>";
    exit;
}

// Parameter binden und ausführen
mysqli_stmt_bind_param($stmt, 'sssssss', $datum, $von, $bis, $gruppe, $trainer, $vermerk, $bemerkung);

if (mysqli_stmt_execute($stmt)) {
    // Zähler nur erhöhen, wenn kein eingeloggter Benutzer (z.B. Admin) die Einträge macht
    if (empty($_SESSION['user'])) {
        if (!isset($_SESSION['entry_count'])) {
            $_SESSION['entry_count'] = 0;
        }
        $_SESSION['entry_count']++;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: index.php?rcsubmit=1");
    exit;
} else {
    echo "<p>❌ Fehler beim Speichern: " . mysqli_stmt_error($stmt) . "</p>";
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

// statt: $gruppe = $_POST['gruppe'] ...
// sichere Tabellennamen
$safe_grtable = preg_replace('/[^A-Za-z0-9_]/', '', $hesk_settings['db_hb_pfix'] . 'gruppen');

$group_input = trim($_POST['gruppe'] ?? '');
$gruppe_names = [];

// wenn "sonstige" frei eingegeben wurde, bevorzugen
$sonstige = trim($_POST['gruppe_sonstige'] ?? '');
if ($sonstige !== '') {
    $gruppe_names[] = $sonstige;
} elseif ($group_input !== '') {
    // IDs parsen: "3/5" oder "3,5"
    $parts = preg_split('#[\/,]#', $group_input);
    $ids = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') continue;
        $id = (int)$p;
        if ($id > 0) $ids[] = $id;
    }
    $ids = array_values(array_unique($ids));
    if (count($ids) > 0) {
        // sichere IN‑Liste bauen
        $in = implode(',', array_map('intval', $ids));
        $q = "SELECT id, name FROM `{$safe_grtable}` WHERE id IN ({$in})";
        $res = mysqli_query($conn, $q);
        $map = [];
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $map[(int)$r['id']] = $r['name'];
            }
        }
        // in ursprünglicher Reihenfolge die Namen sammeln (nur vorhandene IDs)
        foreach ($ids as $id) {
            if (isset($map[$id])) $gruppe_names[] = $map[$id];
        }
    }
}

// Endergebnis: Namen zusammenfügen (z.B. "GrA/GrB") oder leer lassen
$gruppe = implode('/', $gruppe_names);

