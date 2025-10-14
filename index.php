<?php 
session_start();

require_once 'includes/header.php'; 
require_once(__DIR__ . '/config.php');


if (isset($_GET['rcsubmit'])) {
    if ($_GET['rcsubmit'] == 1) {
        echo "<p>✅ Eintrag erfolgreich gespeichert!</p>";
    } elseif ($_GET['rcsubmit'] == 2) {
        echo "<p>⚠️ Fehler: Der Eintrag liegt zu weit in der Zukunft und wurde nicht gespeichert.</p>";
    } elseif ($_GET['rcsubmit'] == 3) {
        echo "<p>⚠️ Fehler: Ungültige Eingabe – bitte Datum und Uhrzeit prüfen.</p>";
    } else {
        echo "<p>❌ Unbekannter Status – bitte erneut versuchen.</p>";
    }
}

?>
<div style="position:absolute; top:10px; right:10px; font-size:0.9em;">
<?php

$conn = get_db_connection();

global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'gruppen';

?>
</div>

<style>
.form-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}
.form-row label {
  width: 100px;
}
.form-row input[type="date"],
.form-row input[type="time"] {
  width: 140px;
}
.form-row input[type="text"] {
  width: 300px;
}
.form-row input[type="text"][name="vermerk"] {
  width: 200px;
}
.form-row textarea {
  width: 300px;
  height: 60px;
}
.required-star {
  color: red;
  font-size: 1em;
  margin-left: 2px;
  vertical-align: super;
}
</style>

<form action="submit.php" method="post">
  <div class="form-row">
    <label for="datum">Datum:</label>
    <input type="date" name="datum" id="datum" value="<?= date('Y-m-d') ?>" required>
    <span class="required-star">*</span>
  </div>
  <div class="form-row">
    <label for="von">Von:</label>
    <input type="time" name="von" id="von" required>
    <span class="required-star">*</span>
  </div>
  <div class="form-row">
    <label for="bis">Bis:</label>
    <input type="time" name="bis" id="bis" required>
    <span class="required-star">*</span>
  </div>
  <div class="form-row">
    <label for="gruppe">Gruppe:</label>
    <select name="gruppe" id="gruppe" required>
      <option value="">Bitte wählen</option>
      <?php
      $result = mysqli_query($conn, "SELECT name FROM hb_gruppen ORDER BY name = 'sonstige' DESC, name ASC");
      while ($row = mysqli_fetch_assoc($result)) {
          $g = htmlspecialchars($row['name']);
          echo "<option value=\"$g\">$g</option>";
      }
      ?>
    </select>
    <span class="required-star">*</span>
  </div>

  <div class="form-row" id="gruppe-sonstige-row" style="display:none;">
    <label for="gruppe_sonstige">Bitte Gruppe angeben:</label>
    <input type="text" name="gruppe_sonstige" id="gruppe_sonstige" maxlength="100">
    <span class="required-star">*</span>
  </div>
  <script>
  document.getElementById('gruppe').addEventListener('change', function () {
    const sonstigeFeld = document.getElementById('gruppe-sonstige-row');
    sonstigeFeld.style.display = (this.value.toLowerCase() === 'sonstige') ? 'block' : 'none';
  });
  </script>
  <div class="form-row">
    <label for="leiter">Leiter: </label>
    <input type="text" name="leiter" id="leiter" required>
    <span class="required-star">*</span>
  </div>
<!-- 
  <div class="form-row">
    <label for="vermerk">Vermerk:</label>
    <input type="text" name="vermerk" id="vermerk">
  </div>
-->
  <div class="form-row">
    <label for="bemerkung">Bemerkung:</label>
    <textarea name="bemerkung" id="bemerkung"></textarea>
  </div>
  <button type="submit">Eintragen</button>
</form>

<?php 
    include 'includes/list_last_entries.php';
    require_once 'includes/footer.php'; 
?>

