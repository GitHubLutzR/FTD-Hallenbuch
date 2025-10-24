<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../include.php');
#require_once(__DIR__ . '/../include_public.php');
// Debug aktivieren (nur wenn Debug-Flag gesetzt)
if (!empty($hesk_settings['debug'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', sys_get_temp_dir() . '/php_errors.log'); // z.B. /tmp/php_errors.log
    error_reporting(E_ALL);

    // Auf Shutdown prüfen (z.B. für fatale Fehler)
    register_shutdown_function(function() {
        $err = error_get_last();
        if ($err) {
            error_log('SHUTDOWN ERROR: ' . print_r($err, true));
        }
    });
}
$conn = get_db_connection();
global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'hallenbuch';

// Filterparameter (robust: Priorität nach tatsächlich gesendeten Parametern)
$selectedDate   = $_GET['filter_date'] ?? '';
$selectedWeek   = $_GET['filter_week'] ?? '';
$selectedMonth  = $_GET['filter_month'] ?? '';
$activeFilter   = '';

// Wenn filter_date explizit im Query-String ist und nicht leer, priorisiere Datum.
// Ansonsten prüfe explizit auf filter_week, dann filter_month.
// Dadurch kann man zuverlässig von Woche/Monat zurück auf Datum wechseln.
if (array_key_exists('filter_date', $_GET) && $_GET['filter_date'] !== '') {
    $activeFilter = 'date';
    $selectedWeek = '';
    $selectedMonth = '';
} elseif (array_key_exists('filter_week', $_GET) && $_GET['filter_week'] !== '') {
    $activeFilter = 'week';
    $selectedDate = '';
    $selectedMonth = '';
} elseif (array_key_exists('filter_month', $_GET) && $_GET['filter_month'] !== '') {
    $activeFilter = 'month';
    $selectedDate = '';
    $selectedWeek = '';
} else {
    // Keine expliziten Filter-Parameter -> keine Filter aktiv
    $selectedDate = '';
    $selectedWeek = '';
    $selectedMonth = '';
    $activeFilter = '';
}

// Limit-Auswahl: wenn ein Filter aktiv ist, Default auf 'ALLE' (oder über GET)
$limitOptions = [10, 25, 50, 100, 'ALLE'];
$selectedLimit = $_GET['limit'] ?? ($activeFilter ? 'ALLE' : 10);
$selectedLimit = in_array($selectedLimit, $limitOptions, true) ? $selectedLimit : 10;

// Filterlogik
$dateCondition = '';
if ($activeFilter === 'month') {
    $dateCondition = "WHERE MONTH(datum) = " . intval($selectedMonth) . " AND YEAR(datum) = " . date('Y');
} elseif ($activeFilter === 'week') {
    $dateCondition = "WHERE WEEK(datum, 1) = " . intval($selectedWeek) . " AND YEAR(datum) = " . date('Y');
} elseif ($activeFilter === 'date') {
    $dateObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
    if ($dateObj) {
        $formattedDate = $dateObj->format('Y-m-d');
        $dateCondition = "WHERE datum = '$formattedDate'";
    }
}

// Löschvorgang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $idsToDelete = array_map('intval', $_POST['delete_ids']);
    if (!empty($idsToDelete)) {
        $idList = implode(',', $idsToDelete);
        $deleteSql = "DELETE FROM $table WHERE id IN ($idList)";
        mysqli_query($conn, $deleteSql);
    }
}

// Einträge abrufen
$limitClause = ($selectedLimit === 'ALLE') ? '' : "LIMIT " . intval($selectedLimit);
$sql = "SELECT * FROM $table $dateCondition ORDER BY datum DESC, von DESC $limitClause";
$result = mysqli_query($conn, $sql);

// Datum zurück / vor berechnen
if (!empty($selectedDate) && isset($dateObj)) {
    $yesterday = (clone $dateObj)->modify('-1 day')->format('Y-m-d');
    $tomorrow  = (clone $dateObj)->modify('+1 day')->format('Y-m-d');
    $today     = date('Y-m-d');
}

// Auswahlformular (ohne inline onchange - JS übernimmt das Submit nach dem Leeren)
echo "<form id='filterForm' method='GET' style='margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center;'>";

// Limit-Auswahl
echo "<label for='limit'>Anzahl Einträge:</label> ";
echo "<select name='limit' id='limitSelect'>";
foreach ($limitOptions as $option) {
    $selected = ($selectedLimit == $option || ($activeFilter && !isset($_GET['limit']) && $option === 'ALLE')) ? "selected" : "";
    echo "<option value='$option' $selected>$option</option>";
}
echo "</select>";

// Datumsfilter (kein inline onchange)
echo "<label for='filter_date'>Datum:</label>";
echo "<input type='date' name='filter_date' id='filter_date' value='" . htmlspecialchars($selectedDate) . "'>";

// Zurück/Vor Buttons
if (!empty($selectedDate)) {
    echo "<a id='prevDate' href='?filter_date=$yesterday&limit=ALLE' style='padding: 4px 8px; border: 1px solid #ccc;'>◀</a>";
    if ($formattedDate < $today) {
        echo "<a id='nextDate' href='?filter_date=$tomorrow&limit=ALLE' style='padding: 4px 8px; border: 1px solid #ccc;'>▶</a>";
    }
}

// Wochenfilter (kein inline onchange)
echo "<label for='filter_week'>Wochenfilter:</label>";
echo "<select name='filter_week' id='filter_week'>";
echo "<option value=''>–</option>";
for ($i = 0; $i < 8; $i++) {
    $weekNum = date('W', strtotime("-$i week"));
    $selected = ($selectedWeek == $weekNum) ? "selected" : "";
    echo "<option value='$weekNum' $selected>KW $weekNum</option>";
}
echo "</select>";

// Monatsfilter (kein inline onchange)
echo "<label for='filter_month'>Monatsfilter:</label>";
echo "<select name='filter_month' id='filter_month'>";
echo "<option value=''>–</option>";
for ($i = 0; $i < 8; $i++) {
    $monthNum = date('n', strtotime("-$i month"));
    $monthName = date('F', strtotime("-$i month"));
    $selected = ($selectedMonth == $monthNum) ? "selected" : "";
    echo "<option value='$monthNum' $selected>$monthName</option>";
}
echo "</select>";

// Filter aufheben
if ($activeFilter) {
    echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "' style='padding: 4px 8px; border: 1px solid #ccc; background-color: #eee;'>Filter aufheben</a>";
}

echo "</form>";

// JS: beim Wechsel eines Filters die anderen Filterfelder LEEREN (nicht disablen) und dann SUBMIT
echo <<<'JS'
<script>
(function(){
    var f = document.getElementById('filterForm');
    if (!f) return;
    var date = f.querySelector('input[name="filter_date"]');
    var week = f.querySelector('select[name="filter_week"]');
    var month = f.querySelector('select[name="filter_month"]');
    var limit = f.querySelector('select[name="limit"]') || document.getElementById('limitSelect');

    function clearDate(){ if (date) { date.value = ''; } }
    function clearWeek(){ if (week) { week.value = ''; week.selectedIndex = 0; } }
    function clearMonth(){ if (month) { month.value = ''; month.selectedIndex = 0; } }

    // Beim Laden: falls ein Feld bereits gesetzt ist, die anderen leeren (nur optisch/cleanup)
    (function initClear() {
        if (week && week.value) {
            clearDate(); clearMonth();
        } else if (month && month.value) {
            clearDate(); clearWeek();
        } else if (date && date.value) {
            clearWeek(); clearMonth();
        }
    })();

    // Ändere-Handler: LEEREN -> ggf limit anpassen -> SUBMIT
    function onWeekChange(){
        clearDate();
        clearMonth();
        if(limit) limit.value = 'ALLE';
        f.submit();
    }
    function onMonthChange(){
        clearDate();
        clearWeek();
        if(limit) limit.value = 'ALLE';
        f.submit();
    }
    function onDateChange(){
        clearWeek();
        clearMonth();
        if(limit) limit.value = 'ALLE';
        f.submit();
    }

    if (week) week.addEventListener('change', onWeekChange);
    if (month) month.addEventListener('change', onMonthChange);
    if (date) date.addEventListener('change', onDateChange);
})();
</script>
JS;

// Überschrift
if ($activeFilter === 'date') {
    echo "<h3>Einträge für den " . date('d.m.Y', strtotime($formattedDate)) . ":</h3>";
} elseif ($activeFilter === 'week') {
    echo "<h3>Einträge für KW $selectedWeek:</h3>";
} elseif ($activeFilter === 'month') {
    echo "<h3>Einträge für Monat " . date('F', mktime(0, 0, 0, $selectedMonth, 10)) . ":</h3>";
} else {
    echo "<h3>Letzte Einträge:</h3>";
}

// Tabelle
if ($result && mysqli_num_rows($result) > 0) {
    $columnConfig = [
        'datum'     => ['label' => 'Datum',     'width' => '80px'],
        'von'       => ['label' => 'Von',       'width' => '50px'],
        'bis'       => ['label' => 'Bis',       'width' => '50px'],
        'gruppe'    => ['label' => 'Gruppe',    'width' => '100px'],
        'trainer'   => ['label' => 'Trainer',   'width' => '100px'],
        'bemerkung' => ['label' => 'Bemerkung', 'width' => '200px']
    ];

    echo "<form method='POST'>";
    echo "<table style='table-layout: fixed; width: 100%; border-collapse: collapse;'>";

    echo "<tr>";
    echo "<th style='width:30px; border: 1px solid #ccc;'>🗑️</th>";
    foreach ($columnConfig as $key => $config) {
        echo "<th style='width:{$config['width']}; border: 1px solid #ccc;'>{$config['label']}</th>";
    }
    echo "</tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ccc; text-align: center;'>
                <input type='checkbox' name='delete_ids[]' value='{$row['id']}'>
              </td>";
        foreach ($columnConfig as $key => $config) {
            // Werte holen, HTML-Entities zurückübersetzen (Umlaute wiederherstellen)
            $value = htmlspecialchars($row[$key] ?? '');
            $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

            if ($key === 'bemerkung' && strlen($value) > 150) {
                $value = substr($value, 0, 147) . '...';
                echo "<td style='width:{$config['width']}; border: 1px solid #ccc;'>$value</td>";
            } else {
                // Zeitfelder kürzen auf HH:MM
                if (in_array($key, ['von', 'bis']) && strlen($value) >= 5) {
                    $value = substr($value, 0, 5);
                }
                // Datum umwandeln in TT.MM.JJJJ
                if ($key === 'datum' && !empty($value)) {
                    $dateObj = DateTime::createFromFormat('Y-m-d', $value);
                    if ($dateObj) {
                        $value = $dateObj->format('d.m.Y');
                    }
                }
                echo "<td style='width:{$config['width']}; border: 1px solid #ccc;'>$value</td>";
            }
        }
        echo "</tr>";
    }

    echo "</table>";
    echo "<br><button type='submit' style='padding: 8px 16px;'>Ausgewählte löschen</button>";
    echo "</form>";
} else {
    echo "<p>Keine Einträge gefunden.</p>";
}

mysqli_close($conn);
require_once(__DIR__ . '/footer.php');
?>
