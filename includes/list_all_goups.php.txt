<?php
require_once(__DIR__ . '/../config.php');

$conn = get_db_connection();

global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'gruppen';

#$sql = "SELECT * FROM $table ORDER BY id DESC LIMIT 10";
$sql = "SELECT * FROM $table ";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  ########################
  echo "<h3>Liste der Gruppen:</h3>";

  $columnConfig = [
    'name'     => ['label' => 'Gruppenname',     'width' => '100px']
  ];

  echo "<table class='last-entries' style='table-layout: fixed; width: 100%; border-collapse: collapse;'>";

  // Tabellenkopf
  echo "<tr>";
  foreach ($columnConfig as $key => $config) {
    echo "<th style='width:{$config['width']}; border: 1px solid #ccc; padding: 4px;'>{$config['label']}</th>";
  }
  echo "</tr>";

  // Datenzeilen
  while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    foreach ($columnConfig as $key => $config) {
      $value = htmlspecialchars($row[$key] ?? '');
      if ($key === 'bemerkung' && strlen($value) > 150) {
        $value = substr($value, 0, 147) . '...';
        echo "<td style='width:{$config['width']}; border: 1px solid #ccc; padding: 4px;'>$value</td>";
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
        echo "<td>$value</td>";
      }
    }
    echo "</tr>";
  }

  echo "</table>";
  #######################
} else {
  echo "<p>Keine Einträge gefunden.</p>";
}

mysqli_close($conn);
