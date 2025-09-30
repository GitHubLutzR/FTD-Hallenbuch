<?php
require 'include.php';
require 'fpdf.php';

$filter = $_GET['filter'] ?? 'woche';
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Hallenbuch-Export (' . ucfirst($filter) . ')', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);

$pdo = get_db_connection();
$eintraege = [];

if ($filter === 'woche') {
    $start = (new DateTimeImmutable('monday this week'))->format('Y-m-d');
    $ende = (new DateTimeImmutable('sunday this week'))->format('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM hallenbuch WHERE datum BETWEEN ? AND ?");
    $stmt->execute([$start, $ende]);
    $eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($filter === 'monat') {
    $monat = date('Y-m');
    $stmt = $pdo->prepare("SELECT * FROM hallenbuch WHERE DATE_FORMAT(datum, '%Y-%m') = ?");
    $stmt->execute([$monat]);
    $eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM hallenbuch ORDER BY datum DESC");
    $eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($eintraege as $e) {
    $pdf->Cell(0, 6, "{$e['datum']} {$e['von']}-{$e['bis']} | Gruppe: {$e['gruppe']} | Leiter: {$e['leiter']}", 0, 1);
    if (!empty($e['vermerk'])) {
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->MultiCell(0, 5, "Vermerk: " . $e['vermerk']);
        $pdf->SetFont('Arial', '', 10);
    }
    $pdf->Ln(2);
}

$pdf->Output('I', "hallenbuch_{$filter}.pdf");

