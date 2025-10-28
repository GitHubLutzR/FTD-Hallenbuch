<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include.php');

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
    $m = intval($selectedMonth);
    $y = date('Y');
    $dateCondition = "WHERE MONTH(datum) = {$m} AND YEAR(datum) = {$y}";
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
if (!$res) {
    // clear buffers before sending error text
    while (ob_get_level()) ob_end_clean();
    die('DB query failed: ' . mysqli_error($conn));
}

$eintraege = [];
while ($row = mysqli_fetch_assoc($res)) {
    $eintraege[] = $row;
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

// Hilfsfunktion: Text so kürzen, dass er in Breite passt (mit "...")
function fitTextToWidth($pdf, $text, $w) {
    $txt = trim((string)$text);
    $avail = $w - 2; // kleiner Innenabstand
    if ($avail <= 0) return '';
    // schnelle check
    if ($pdf->GetStringWidth($txt) <= $avail) return $txt;
    $ell = '...';
    $len = mb_strlen($txt);
    // reduziert in Schritten
    while ($len > 0) {
        $candidate = mb_substr($txt, 0, $len) . $ell;
        if ($pdf->GetStringWidth($candidate) <= $avail) return $candidate;
        $len -= 1;
    }
    return $ell;
}

// Inhalte ausgeben
if (count($eintraege) === 0) {
    $pdf->Cell(0, $lineH, "Keine Einträge für den ausgewählten Filter.", 1, 1);
} else {
    foreach ($eintraege as $e) {
        // decode any HTML entities, escape, then convert to ISO-8859-1 for FPDF
        $datum_raw = $e['datum'] ?? '';
        $von_raw   = $e['von'] ?? '';
        $bis_raw   = $e['bis'] ?? '';
        $gruppe_raw= $e['gruppe'] ?? '';
        $leiter_raw= $e['leiter'] ?? ($e['trainer'] ?? '');
        // DB-Feld heißt "bemerkung"
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


         $pdf->Cell($wDatum, $lineH, fitTextToWidth($pdf, $datum, $wDatum), 1, 0);
         $pdf->Cell($wVon,   $lineH, fitTextToWidth($pdf, $von, $wVon), 1, 0);
         $pdf->Cell($wBis,   $lineH, fitTextToWidth($pdf, $bis, $wBis), 1, 0);
         $pdf->Cell($wGruppe,$lineH, fitTextToWidth($pdf, $gruppe, $wGruppe), 1, 0);
         $pdf->Cell($wLeiter,$lineH, fitTextToWidth($pdf, $leiter, $wLeiter), 1, 0);
         $pdf->Cell($wBemerk,$lineH, fitTextToWidth($pdf, $vermerk, $wBemerk), 1, 1);
     }
}

// ------------------------------------
// Ensure no previous output prevents PDF send
// ------------------------------------
while (ob_get_level()) ob_end_clean();
ini_set('display_errors', 0);
$pdf->Output('I', "hallenbuch_export_{$activeFilter}.pdf");
exit;

