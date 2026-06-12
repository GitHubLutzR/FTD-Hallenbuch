````php
// ...existing code...
// Filterlogik (jetzt flexibel mehrere WHERE-Bedingungen und Gruppenfilter)
$whereClauses = [];

if ($activeFilter === 'month') {
    // bestimme korrekt das Jahr (wenn ausgewählter Monat > aktueller Monat -> Vorjahr)
    $year = (intval($selectedMonth) > intval(date('n'))) ? (int)date('Y') - 1 : (int)date('Y');
    $whereClauses[] = "MONTH(datum) = " . intval($selectedMonth);
    $whereClauses[] = "YEAR(datum) = " . intval($year);
} elseif ($activeFilter === 'week') {
    $whereClauses[] = "WEEK(datum, 1) = " . intval($selectedWeek);
    $whereClauses[] = "YEAR(datum) = " . intval(date('Y'));
} elseif ($activeFilter === 'date') {
    $dateObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
    if ($dateObj) {
        $formattedDate = $dateObj->format('Y-m-d');
        $whereClauses[] = "datum = '" . $formattedDate . "'";
    }
}

// Gruppenauswahl als zusätzlicher Filter (ID numerisch behandeln)
if ($selectedGroup !== '') {
    $whereClauses[] = "gruppe = " . intval($selectedGroup);
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}
// ...existing code...
$limitClause = ($selectedLimit === 'ALLE') ? '' : "LIMIT " . intval($selectedLimit);
$sql = "SELECT * FROM $table $whereSql ORDER BY datum DESC, von DESC $limitClause";
$result = mysqli_query($conn, $sql);
// ...existing code...
```// filepath: /home/risse/github/FTD-Hallenbuch/includes/view_entries-n.php
// ...existing code...
// Filterlogik (jetzt flexibel mehrere WHERE-Bedingungen und Gruppenfilter)
$whereClauses = [];

if ($activeFilter === 'month') {
    // bestimme korrekt das Jahr (wenn ausgewählter Monat > aktueller Monat -> Vorjahr)
    $year = (intval($selectedMonth) > intval(date('n'))) ? (int)date('Y') - 1 : (int)date('Y');
    $whereClauses[] = "MONTH(datum) = " . intval($selectedMonth);
    $whereClauses[] = "YEAR(datum) = " . intval($year);
} elseif ($activeFilter === 'week') {
    $whereClauses[] = "WEEK(datum, 1) = " . intval($selectedWeek);
    $whereClauses[] = "YEAR(datum) = " . intval(date('Y'));
} elseif ($activeFilter === 'date') {
    $dateObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
    if ($dateObj) {
        $formattedDate = $dateObj->format('Y-m-d');
        $whereClauses[] = "datum = '" . $formattedDate . "'";
    }
}

// Gruppenauswahl als zusätzlicher Filter (ID numerisch behandeln)
if ($selectedGroup !== '') {
    $whereClauses[] = "gruppe = " . intval($selectedGroup);
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}
// ...existing code...
$limitClause = ($selectedLimit === 'ALLE') ? '' : "LIMIT " . intval($selectedLimit);
$sql = "SELECT * FROM $table $whereSql ORDER BY datum DESC, von DESC $limitClause";
$result = mysqli_query($conn, $sql);
// ...existing code...
