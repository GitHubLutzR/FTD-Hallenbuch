<?php
// Prevent template/header output and start buffering
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
ob_start();

// Konfiguration + "public" include (keine HTML-Ausgaben)
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include_public.php');

// DEBUG: Log starten (temporär, entfernen nach Diagnose)
error_log('[export] start: ' . date('c'));
error_log('[export] PHP SAPI: ' . php_sapi_name() . ' ; PHP version: ' . phpversion());
error_log('[export] _GET: ' . json_encode($_GET));
error_log('[export] fpdf exists in includes? ' . (file_exists(__DIR__ . '/fpdf.php') ? 'yes' : 'no'));
error_log('[export] fpdf exists in project fpdf186? ' . (file_exists(__DIR__ . '/../fpdf186/fpdf.php') ? 'yes' : 'no'));
error_log('[export] vendor/autoload? ' . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'yes' : 'no'));
error_log('[export] class FPDF before include? ' . (class_exists('FPDF') ? 'yes' : 'no'));

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start buffering to avoid accidental output before PDF
if (!ob_get_level()) {
    ob_start();
}

$conn = get_db_connection();
if (!$conn) {
    die('DB connection failed');
}

global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'hallenbuch';

// FPDF prüfen / einbinden (includes -> Projekt fpdf186 -> composer)
if (!class_exists('FPDF')) {
    if (file_exists(__DIR__ . '/fpdf.php')) {
        require_once __DIR__ . '/fpdf.php';
    } elseif (file_exists(__DIR__ . '/../fpdf186/fpdf.php')) {
        require_once __DIR__ . '/../fpdf186/fpdf.php';
    } elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    } else {
        die('FPDF class not found. Bitte fpdf.php in includes/ oder fpdf186/ legen oder Composer installieren.');
    }
    if (!class_exists('FPDF')) {
        die('FPDF class still not available after include.');
    }
}

// --- Filter aus GET übernehmen (gleich wie list_entries_for_delete.php) ---
$selectedDate  = $_GET['filter_date'] ?? '';
$selectedWeek  = $_GET['filter_week'] ?? '';
$selectedMonth = $_GET['filter_month'] ?? '';
$activeFilter  = '';

if (array_key_exists('filter_date', $_GET) && $_GET['filter_date'] !== '') {
    $activeFilter = 'date';
} elseif (array_key_exists('filter_week', $_GET) && $_GET['filter_week'] !== '') {
    $activeFilter = 'week';
} elseif (array_key_exists('filter_month', $_GET) && $_GET['filter_month'] !== '') {
    $activeFilter = 'month';
} else {
    $activeFilter = ''; // keine Filter -> exportiere alles (oder Standardwoche, siehe Wunsch)
}

// Baue SQL entsprechend dem aktiven Filter (kein LIMIT – exportiere alle Treffer)
$dateCondition = '';
if ($activeFilter === 'month') {
    // akzeptiere "YYYY-MM" oder "MM"
    if (preg_match('/^(\d{4})-(\d{1,2})$/', $selectedMonth, $mm)) {
        $y = (int)$mm[1];
        $m = (int)$mm[2];
    } else {
        $m = intval($selectedMonth);
        $y = date('Y');
    }
    if ($m >= 1 && $m <= 12) {
        $dateCondition = "WHERE MONTH(datum) = {$m} AND YEAR(datum) = {$y}";
    } else {
        // ungültiger Monatswert -> keine Trefferbedingung (oder optional: Abbruch)
        $dateCondition = '';
    }
} elseif ($activeFilter === 'week') {
    $w = intval($selectedWeek);
    $y = date('Y');
    $dateCondition = "WHERE WEEK(datum, 1) = {$w} AND YEAR(datum) = {$y}";
} elseif ($activeFilter === 'date') {
    $dObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
    if ($dObj) {
        $d = $dObj->format('Y-m-d');
        $dateCondition = "WHERE datum = '{$d}'";
    }
}

// Query
$sql = "SELECT * FROM `{$table}` " . ($dateCondition ? $dateCondition . ' ' : '') . "ORDER BY datum, von";
$res = mysqli_query($conn, $sql);
if ($res === false) {
    // DB-Fehler diagnostizieren, Buffer sauber machen und Abbruch
    while (ob_get_level()) ob_end_clean();
    die('DB query failed: ' . mysqli_error($conn) . ' -- SQL: ' . htmlspecialchars($sql, ENT_QUOTES, 'UTF-8'));
}

$eintraege = [];
while ($row = mysqli_fetch_assoc($res)) {
    $eintraege[] = $row;
}

// Sicherheit: stelle sicher, dass $eintraege ein Array ist (vermeidet foreach-Warnung)
if (!is_array($eintraege)) {
    // Falls mysqli_fetch_assoc fehlschlug oder eine unerwartete Rückgabe kam, leeren wir die Liste
    $eintraege = [];
}

// Sicherstellen, dass $eintraege iterierbar ist (vermeidet "foreach() argument must be of type array|object, bool given")
if (!is_array($eintraege)) {
    // Debug-Info in error_log schreiben, falls es weiter auftritt
    error_log('[export.php] $eintraege ist vom Typ: ' . gettype($eintraege) . ' -- $_GET: ' . var_export($_GET, true));
    $eintraege = [];
}

// PDF: Querformat (L)
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

// konkreten Filter-Text (Datum / Woche / Monat mit Jahr)
$titleFilter = 'alle';
if ($activeFilter === 'date' && !empty($selectedDate)) {
    $dObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
    $titleFilter = $dObj ? $dObj->format('d.m.Y') : $selectedDate;
} elseif ($activeFilter === 'week' && !empty($selectedWeek)) {
    $w = intval($selectedWeek);
    $y = date('Y');
    $dto = new DateTime();
    $dto->setISODate($y, $w);
    $start = $dto->format('d.m.Y');
    $dto->modify('+6 days');
    $end = $dto->format('d.m.Y');
    $titleFilter = "Woche {$w} ({$start} - {$end})";
} elseif ($activeFilter === 'month' && !empty($selectedMonth)) {
    // akzeptiert 'YYYY-MM' oder 'MM'
    if (preg_match('/^(\d{4})-(\d{1,2})$/', $selectedMonth, $m)) {
        $titleFilter = DateTime::createFromFormat('!m', $m[2])->format('F') . ' ' . $m[1];
    } else {
        $mnum = intval($selectedMonth);
        $titleFilter = DateTime::createFromFormat('!m', $mnum)->format('F') . ' ' . date('Y');
    }
}

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Hallenbuch-Export: ' . ucfirst(htmlspecialchars($titleFilter, ENT_QUOTES, 'UTF-8')), 0, 1, 'C');
$pdf->Ln(4);

// Tabellen-Header
$pdf->SetFont('Arial', 'B', 10);

// Spalten-Breiten (Landscape A4)
$wDatum   = 25;
$wVon     = 20;
$wBis     = 20;
$wGruppe  = 100;
$wLeiter  = 70;
$wBemerk  = 50;
$lineH = 6;

$pdf->SetFillColor(230,230,230);
$pdf->Cell($wDatum, $lineH, 'Datum', 1, 0, 'L', true);
$pdf->Cell($wVon,   $lineH, 'Von',   1, 0, 'L', true);
$pdf->Cell($wBis,   $lineH, 'Bis',   1, 0, 'L', true);
$pdf->Cell($wGruppe, $lineH, 'Gruppe', 1, 0, 'L', true);
$pdf->Cell($wLeiter, $lineH, 'Leiter', 1, 0, 'L', true);
$pdf->Cell($wBemerk, $lineH, 'Bemerkung', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 10);

// Hilfsfunktion: berechnet wie viele Zeilen ein Text in gegebener Breite benötigt
function nbLines($pdf, $w, $txt, $lineH) {
    $txt = trim((string)$txt);
    if ($txt === '') return 1;
    // split in words (inkl. Trennung), fallback wenn preg_split fehlschlägt
    $words = @preg_split('/(\s+)/u', $txt, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($words === false || !is_array($words)) {
        // try a simpler split as fallback
        $words = preg_split('/(\s+)/', $txt, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($words === false || !is_array($words)) {
            $words = array($txt);
        }
    }
    $lines = 0;
    $line = '';
    foreach ($words as $part) {
        $test = ($line === '') ? $part : $line . $part;
        if ($pdf->GetStringWidth($test) <= $w - 2) {
            $line = $test;
        } else {
            if ($line === '') {
                // Wort zu lang -> breche das Wort
                $chunk = '';
                $len = mb_strlen($part);
                for ($i = 0; $i < $len; $i++) {
                    $ch = mb_substr($part, $i, 1);
                    if ($pdf->GetStringWidth($chunk . $ch) <= $w - 2) {
                        $chunk .= $ch;
                    } else {
                        $lines++;
                        $chunk = $ch;
                    }
                }
                if ($chunk !== '') $line = $chunk;
            } else {
                $lines++;
                $line = $part;
            }
        }
    }
    if ($line !== '') $lines++;
    return max(1, $lines);
}

// Inhalte ausgeben mit MultiCell & Zellrändern (mehrzeilig)
if (count($eintraege) === 0) {
    $pdf->Cell(0, $lineH, "Keine Einträge für den ausgewählten Filter.", 1, 1);
} else {
    foreach ($eintraege as $e) {
        // decode/escape/convert (wie vorher)
        $datum_raw   = $e['datum'] ?? '';
        $von_raw     = $e['von'] ?? '';
        $bis_raw     = $e['bis'] ?? '';
        $gruppe_raw  = $e['gruppe'] ?? '';
        $leiter_raw  = $e['leiter'] ?? ($e['trainer'] ?? '');
        $vermerk_raw = $e['bemerkung'] ?? '';

        $datum  = htmlspecialchars(html_entity_decode($datum_raw,  ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $von    = htmlspecialchars(html_entity_decode($von_raw,    ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $bis    = htmlspecialchars(html_entity_decode($bis_raw,    ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $gruppe = htmlspecialchars(html_entity_decode($gruppe_raw, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $leiter = htmlspecialchars(html_entity_decode($leiter_raw, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $vermerk= htmlspecialchars(html_entity_decode($vermerk_raw,ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');

        // convert to ISO-8859-1 for FPDF (fallback to original if conversion fails)
        $conv = function($s){
            $out = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);
            return $out === false ? $s : $out;
        };
        $datum  = $conv($datum);
        $von    = $conv($von);
        $bis    = $conv($bis);
        $gruppe = $conv($gruppe);
        $leiter = $conv($leiter);
        $vermerk= $conv($vermerk);

        // Anzahl Zeilen pro Spalte berechnen
        $linesDatum  = nbLines($pdf, $wDatum,  $datum,  $lineH);
        $linesVon    = nbLines($pdf, $wVon,    $von,    $lineH);
        $linesBis    = nbLines($pdf, $wBis,    $bis,    $lineH);
        $linesGruppe = nbLines($pdf, $wGruppe, $gruppe, $lineH);
        $linesLeiter = nbLines($pdf, $wLeiter, $leiter, $lineH);
        $linesBemerk = nbLines($pdf, $wBemerk, $vermerk,$lineH);

        $nb = max($linesDatum, $linesVon, $linesBis, $linesGruppe, $linesLeiter, $linesBemerk);
        $h = $nb * $lineH;

        // Seitenumbruch prüfen: wenn nicht genug Platz, neue Seite mit Header
        if ($pdf->GetY() + $h + 20 > $pdf->GetPageHeight()) {
            $pdf->AddPage();
            // optional: re-draw header row (Datum/ Von / ...)
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(230,230,230);
            $pdf->Cell($wDatum, $lineH, 'Datum', 1, 0, 'L', true);
            $pdf->Cell($wVon,   $lineH, 'Von',   1, 0, 'L', true);
            $pdf->Cell($wBis,   $lineH, 'Bis',   1, 0, 'L', true);
            $pdf->Cell($wGruppe, $lineH, 'Gruppe', 1, 0, 'L', true);
            $pdf->Cell($wLeiter, $lineH, 'Leiter', 1, 0, 'L', true);
            $pdf->Cell($wBemerk, $lineH, 'Bemerkung', 1, 1, 'L', true);
            $pdf->SetFont('Arial', '', 10);
        }

        // Startposition merken
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // Datum
        $pdf->Rect($x, $y, $wDatum, $h);
        $pdf->SetXY($x + 1, $y + 1);
        $pdf->MultiCell($wDatum - 2, $lineH, $datum, 0, 'L');
        $pdf->SetXY($x + $wDatum, $y);

        // Von
        $pdf->Rect($x + $wDatum, $y, $wVon, $h);
        $pdf->SetXY($x + $wDatum + 1, $y + 1);
        $pdf->MultiCell($wVon - 2, $lineH, $von, 0, 'L');
        $pdf->SetXY($x + $wDatum + $wVon, $y);

        // Bis
        $pdf->Rect($x + $wDatum + $wVon, $y, $wBis, $h);
        $pdf->SetXY($x + $wDatum + $wVon + 1, $y + 1);
        $pdf->MultiCell($wBis - 2, $lineH, $bis, 0, 'L');
        $pdf->SetXY($x + $wDatum + $wVon + $wBis, $y);

        // Gruppe
        $pdf->Rect($x + $wDatum + $wVon + $wBis, $y, $wGruppe, $h);
        $pdf->SetXY($x + $wDatum + $wVon + $wBis + 1, $y + 1);
        $pdf->MultiCell($wGruppe - 2, $lineH, $gruppe, 0, 'L');
        $pdf->SetXY($x + $wDatum + $wVon + $wBis + $wGruppe, $y);

        // Leiter
        $pdf->Rect($x + $wDatum + $wVon + $wBis + $wGruppe, $y, $wLeiter, $h);
        $pdf->SetXY($x + $wDatum + $wVon + $wBis + $wGruppe + 1, $y + 1);
        $pdf->MultiCell($wLeiter - 2, $lineH, $leiter, 0, 'L');
        $pdf->SetXY($x + $wDatum + $wVon + $wBis + $wGruppe + $wLeiter, $y);

        // Bemerkung
        $pdf->Rect($x + $wDatum + $wVon + $wBis + $wGruppe + $wLeiter, $y, $wBemerk, $h);
        $pdf->SetXY($x + $wDatum + $wVon + $wBis + $wGruppe + $wLeiter + 1, $y + 1);
        $pdf->MultiCell($wBemerk - 2, $lineH, $vermerk, 0, 'L');

        // neue Zeile
        $pdf->SetXY($x, $y + $h);
    }
}

// ------------------------------------
// Ensure no previous output prevents PDF send
// ------------------------------------
while (ob_get_level()) ob_end_clean();
ini_set('display_errors', 0);
$pdf->Output('I', "hallenbuch_export_{$activeFilter}.pdf");
exit;

