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

// vorheriger Code ersatzlos ersetzen durch folgende robustere Normalisierung:
$selectedLimitRaw = $_GET['limit'] ?? ($activeFilter ? 'ALLE' : 10);

if ($selectedLimitRaw === 'ALLE') {
    $selectedLimit = 'ALLE';
} else {
    // numerische Strings in int wandeln, sonst Default benutzen
    if (is_numeric($selectedLimitRaw)) {
        $selectedLimit = (int)$selectedLimitRaw;
    } else {
        $selectedLimit = ($activeFilter ? 'ALLE' : 10);
    }
}

// Validierung gegen erlaubte Optionen (streng prüfen)
if (!in_array($selectedLimit, $limitOptions, true)) {
    $selectedLimit = ($activeFilter ? 'ALLE' : 10);
}

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

// --- NEU: Inline-Edit speichern ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    // sanitize inputs
    $datum     = mysqli_real_escape_string($conn, trim($_POST['datum'] ?? ''));
    $von       = mysqli_real_escape_string($conn, trim($_POST['von'] ?? ''));
    $bis       = mysqli_real_escape_string($conn, trim($_POST['bis'] ?? ''));
    $gruppe    = mysqli_real_escape_string($conn, trim($_POST['gruppe'] ?? ''));
    $trainer   = mysqli_real_escape_string($conn, trim($_POST['trainer'] ?? ''));
    $bemerkung = htmlentities(trim($_POST['bemerkung'] ?? ''), ENT_QUOTES, 'UTF-8');

    $updateSql = "
      UPDATE $table
      SET datum = '$datum',
          von = '$von',
          bis = '$bis',
          gruppe = '$gruppe',
          trainer = '$trainer',
          bemerkung = '$bemerkung'
      WHERE id = $id
      LIMIT 1
    ";
    mysqli_query($conn, $updateSql);

    // Redirect to avoid repost
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
// --- ENDE Inline-Edit speichern ---

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

    // Neu: bei Änderung der Limit-Auswahl Formular absenden (ohne andere Filter zu verändern)
    if (limit) {
        limit.addEventListener('change', function(){
            // falls du möchtest, dass bei Limitwechsel immer alle Filter zurückgesetzt werden,
            // entferne die nächsten zwei Zeilen; aktuell bleiben gesetzte Filter erhalten.
            // clearDate(); clearWeek(); clearMonth();
            f.submit();
        });
    }
})();
</script>
JS;

// Reduce table row height: smaller font, tighter line-height and padding
echo '<style>
  /* table global tweaks */
  table { font-size:13px; border-collapse:collapse; }
  table th, table td { line-height:1.15; vertical-align:middle; }
  /* make Gruppe column not wrap (ellipsis on overflow) */
  .no-wrap { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  /* slightly smaller checkboxes/buttons */
  input[type="checkbox"] { transform: scale(0.95); vertical-align:middle; }
</style>';

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

// --- NEU: Export-Button direkt unter den Filtern (nur bei Wochen- oder Monatsfilter) ---
$activeFilter = '';
if (array_key_exists('filter_week', $_GET) && $_GET['filter_week'] !== '') {
    $activeFilter = 'week';
} elseif (array_key_exists('filter_month', $_GET) && $_GET['filter_month'] !== '') {
    $activeFilter = 'month';
} elseif (array_key_exists('filter_date', $_GET) && $_GET['filter_date'] !== '') {
    $activeFilter = 'date';
}

if ($activeFilter === 'week' || $activeFilter === 'month') {
    $qs = $_GET;
    unset($qs['edit_id'], $qs['limit']); // nicht forwarden
    $export_url = '/hallenbuch/includes/export.php' . (!empty($qs) ? ('?' . http_build_query($qs)) : '');
    echo "<div style='margin:10px 0 14px;'><a href='" . htmlspecialchars($export_url, ENT_QUOTES, 'UTF-8') . "' style='padding:6px 10px;background:#2b7cff;color:#fff;text-decoration:none;border-radius:4px;'>PDF exportieren (aktuelle Filter)</a></div>";
}


// Tabelle
if ($result && mysqli_num_rows($result) > 0) {
    $columnConfig = [
        'datum'     => ['label' => 'Datum',     'width' => '50px'],   // schmaler
        'von'       => ['label' => 'Von',       'width' => '50px'],
        'bis'       => ['label' => 'Bis',       'width' => '50px'],
        'gruppe'    => ['label' => 'Gruppe',    'width' => '260px'],  // breiter
        'trainer'   => ['label' => 'Trainer',   'width' => '120px'],
        'bemerkung' => ['label' => 'Bemerkung', 'width' => '200px']
    ];

    echo "<form method='POST' id='deleteForm'>";
    echo "<table style='table-layout: fixed; width: 100%; border-collapse: collapse;'>";

    echo "<tr>";
    echo "<th style='width:30px; border: 1px solid #ccc;'>🗑️</th>";
    echo "<th style='width:120px; border: 1px solid #ccc;'>Bearbeiten</th>";
    foreach ($columnConfig as $key => $config) {
        echo "<th style='width:{$config['width']}; border: 1px solid #ccc;'>{$config['label']}</th>";
    }
    echo "</tr>";

    // which row is in edit mode (via GET)
    $editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $rowId = (int)$row['id'];

        // If this row is being edited, render a single TR that contains the form
        if ($editId === $rowId) {
            $totalCols = 2 + count($columnConfig); // checkbox + edit + defined columns
            // build cancel URL (remove edit_id)
            $parts = parse_url($_SERVER['REQUEST_URI']);
            parse_str($parts['query'] ?? '', $qs);
            unset($qs['edit_id']);
            $query = http_build_query($qs);
            $cancelUrl = $parts['path'] . ($query !== '' ? '?' . $query : '');
            $cancelUrl = htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8');

            echo "<tr>";
            echo "<td colspan='{$totalCols}' style='border:1px solid #ccc;padding:6px;'>";
            echo "<form method='post' style='display:flex;gap:12px;flex-wrap:wrap;align-items:center;'>";
            echo "<input type='hidden' name='action' value='save'>";
            echo "<input type='hidden' name='id' value='" . $rowId . "'>";

            // Render inputs for each configured column
            foreach ($columnConfig as $key => $config) {
                $rawVal = $row[$key] ?? '';
                $value = htmlspecialchars(html_entity_decode($rawVal, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
                $fieldStyle = "min-width:120px; max-width:{$config['width']};";

                if ($key === 'bemerkung') {
                    echo "<div style='flex:1;min-width:200px;'><label style='display:block;font-weight:600;margin-bottom:4px;'>{$config['label']}</label>";
                    echo "<textarea name='bemerkung' style='width:100%;height:64px;'>{$value}</textarea></div>";
                } elseif ($key === 'datum') {
                    echo "<div style='{$fieldStyle}'>";
                    echo "<label style='display:block;font-weight:600;margin-bottom:4px;'>{$config['label']}</label>";
                    echo "<input type='date' name='datum' value='" . htmlspecialchars($row['datum'] ?? '', ENT_QUOTES, 'UTF-8') . "' style='width:100%'></div>";
                } elseif (in_array($key, ['von','bis'], true)) {
                    echo "<div style='{$fieldStyle}'>";
                    echo "<label style='display:block;font-weight:600;margin-bottom:4px;'>{$config['label']}</label>";
                    echo "<input type='time' name='{$key}' value='" . htmlspecialchars(mb_substr($value,0,5), ENT_QUOTES, 'UTF-8') . "' style='width:100%'></div>";
                } else {
                    echo "<div style='{$fieldStyle}'>";
                    echo "<label style='display:block;font-weight:600;margin-bottom:4px;'>{$config['label']}</label>";
                    echo "<input type='text' name='{$key}' value='" . htmlspecialchars($rawVal, ENT_QUOTES, 'UTF-8') . "' style='width:100%'></div>";
                }
            }

            // Actions
            echo "<div style='display:flex;flex-direction:column;gap:6px;'>";
            echo "<div><button type='submit' style='padding:6px 12px;'>💾 Speichern</button></div>";
            echo "<div><a href='{$cancelUrl}' style='padding:6px 12px;border:1px solid #ccc;background:#f3f3f3;text-decoration:none;display:inline-block;'>Abbrechen</a></div>";
            echo "</div>";

            echo "</form>";
            echo "</td>";
            echo "</tr>";

            // skip rendering the normal row cells (we already output the edit row)
            continue;
        }

        // Normal row rendering (unchanged)
        echo "<tr>";
        echo "<td style='border: 1px solid #ccc; text-align: center;'>
                <input type='checkbox' name='delete_ids[]' value='" . $rowId . "'>
              </td>";

        // Edit button cell
        $editUrl = htmlspecialchars($_SERVER['PHP_SELF'] . '?edit_id=' . $rowId . '&limit=' . urlencode($selectedLimitRaw) . ( $selectedDate ? '&filter_date=' . urlencode($selectedDate) : '' ), ENT_QUOTES, 'UTF-8');
        echo "<td style='border: 1px solid #ccc; text-align:center;'>
                <a href=\"{$editUrl}\" title='Bearbeiten' style='display:inline-block;padding:4px 8px;border:1px solid #ccc;border-radius:4px;background:#fff;text-decoration:none;'>✏️ Edit</a>
              </td>";

        // For each column: either input fields (if editing this row) or normal display
        foreach ($columnConfig as $key => $config) {
            $rawVal = $row[$key] ?? '';
            $value = htmlspecialchars(html_entity_decode($rawVal, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');

            $cellStyle = "width:{$config['width']}; border: 1px solid #ccc; padding:4px;";
            if ($key === 'gruppe') {
                $cellStyle .= " white-space:nowrap; overflow:hidden; text-overflow:ellipsis;";
            }

            if ($editId === $rowId) {
                // render inputs matching the column
                if ($key === 'bemerkung') {
                    echo "<td style='{$cellStyle}'>
                            <textarea name='bemerkung' form='{$formId}' style='width:100%;height:48px;'>" . $value . "</textarea>
                          </td>";
                } elseif (in_array($key, ['von','bis'], true)) {
                    echo "<td style='{$cellStyle}'><input form='{$formId}' type='time' name='{$key}' value='" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "' style='width:100%;'></td>";
                } elseif ($key === 'datum') {
                    // date input expects YYYY-MM-DD -> keep DB format
                    $dateValue = $value;
                    // if value displayed as d.m.Y, try to convert back? In DB it's Y-m-d so use raw
                    echo "<td style='{$cellStyle}'><input form='{$formId}' type='date' name='datum' value='" . htmlspecialchars($row['datum'] ?? '', ENT_QUOTES, 'UTF-8') . "' style='width:100%;'></td>";
                } else {
                    // text inputs for gruppe, trainer, etc.
                    echo "<td style='{$cellStyle}'><input form='{$formId}' type='text' name='{$key}' value='" . htmlspecialchars($rawVal, ENT_QUOTES, 'UTF-8') . "' style='width:100%;'></td>";
                }
            } else {
                // normal display (shorten bemerkung if long)
                if ($key === 'bemerkung' && mb_strlen($value) > 150) {
                    $short = mb_substr($value, 0, 147) . '...';
                    echo "<td style='{$cellStyle}'>$short</td>";
                } else {
                    // cut time fields to HH:MM
                    if (in_array($key, ['von', 'bis'], true) && mb_strlen($value) >= 5) {
                        $value = mb_substr($value, 0, 5);
                    }
                    // format date display
                    if ($key === 'datum' && !empty($value)) {
                        $dObj = DateTime::createFromFormat('Y-m-d', $value);
                        if ($dObj) $value = $dObj->format('d.m.Y');
                    }
                    echo "<td style='{$cellStyle}'>$value</td>";
                }
            }
        }

        echo "</tr>";
    }

    echo "</table>";
    echo "<br><button type='submit' form='deleteForm' style='padding: 8px 16px;'>Ausgewählte löschen</button>";
    echo "</form>";
} else {
    echo "<p>Keine Einträge gefunden.</p>";
}


mysqli_close($conn);
require_once(__DIR__ . '/footer.php');
?>
