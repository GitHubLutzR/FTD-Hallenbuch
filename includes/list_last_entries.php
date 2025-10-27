<?php
require_once(__DIR__ . '/../config.php');

$conn = get_db_connection();

global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'hallenbuch';

#$sql = "SELECT * FROM $table ORDER BY id DESC LIMIT 10";
$sql = "SELECT * FROM $table ORDER BY datum DESC, von DESC LIMIT 10";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  ########################
  echo "<h3>Letzte Einträge:</h3>";

  // schmalere Datum-Spalte, breitere Gruppe; CSS verhindert Zeilenumbruch in Gruppe
  $columnConfig = [
    'datum'     => ['label' => 'Datum',          'width' => '40px'],
    'von'       => ['label' => 'Von',            'width' => '22px'],
    'bis'       => ['label' => 'Bis',            'width' => '22px'],
    'gruppe'    => ['label' => 'Gruppe',         'width' => '220px'],
    'trainer'   => ['label' => 'Trainer/-innen', 'width' => '70px'],
    'bemerkung' => ['label' => 'Bemerkung',      'width' => '200px']
  ];

  // Inline-CSS: verhindere Umbrüche in der Gruppe-Spalte, Ellipsis bei Überlauf
  echo '<style>
    .last-entries td.group-col { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .last-entries { border-collapse:collapse; }
    .last-entries th, .last-entries td { padding:4px; border:1px solid #ccc; vertical-align:middle; font-size:13px; }
  </style>';

  echo "<table class='last-entries' style='table-layout: fixed; width: 100%;'>";

  // Tabellenkopf
  echo "<tr>";
  foreach ($columnConfig as $key => $config) {
    echo "<th style='width:{$config['width']};'>{$config['label']}</th>";
  }
  echo "</tr>";

  // Datenzeilen
  while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    foreach ($columnConfig as $key => $config) {
      // Rohwert holen
      $raw = $row[$key] ?? '';

      // Entities zuerst decodieren, dann sicher für HTML escapen (UTF-8)
      $value = htmlspecialchars(html_entity_decode($raw, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');

      // per-column style und spezielle Darstellung
      $cellClass = ($key === 'gruppe') ? 'group-col' : '';
      $cellStyle = "width:{$config['width']};";

      if ($key === 'bemerkung' && mb_strlen($value) > 150) {
        $short = mb_substr($value, 0, 147) . '...';
        echo "<td class='{$cellClass}' style='{$cellStyle}'>{$short}</td>";
      } else {
        // Zeitfelder kürzen auf HH:MM
        if (in_array($key, ['von', 'bis'], true) && mb_strlen($value) >= 5) {
          $value = mb_substr($value, 0, 5);
        }
        // Datum umwandeln in TT.MM.JJJJ
        if ($key === 'datum' && !empty($value)) {
          $dateObj = DateTime::createFromFormat('Y-m-d', $value);
          if ($dateObj) {
            $value = $dateObj->format('d.m.Y');
          }
        }
        echo "<td class='{$cellClass}' style='{$cellStyle}'>{$value}</td>";
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
