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

$conn = get_db_connection();

global $hesk_settings;
$table = $hesk_settings['db_hb_pfix'] . 'gruppen';

?>
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
.extra-group-list {
  margin: 0 0 8px 0;
  padding: 0;
  list-style: none;
}
.extra-group-list li {
  display: inline-block;
  background: #eee;
  border-radius: 4px;
  padding: 2px 8px;
  margin-right: 6px;
  font-size: 0.95em;
}
.extra-group-list button {
  background: #c00;
  color: #fff;
  border: none;
  border-radius: 2px;
  margin-left: 4px;
  cursor: pointer;
  font-size: 0.9em;
  padding: 0 4px;
}
</style>

<form action="submit.php" method="post" id="hallenbuch-form">
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

  <div class="form-row">
    <label for="extraGruppe">Extra-Gruppe:</label>
    <select id="extraGruppeSelect">
      <option value="">Bitte wählen</option>
      <?php
      // Nochmals alle Gruppen für das Extra-Feld
      $result = mysqli_query($conn, "SELECT name FROM hb_gruppen ORDER BY name = 'sonstige' DESC, name ASC");
      while ($row = mysqli_fetch_assoc($result)) {
          $g = htmlspecialchars($row['name']);
          echo "<option value=\"$g\">$g</option>";
      }
      ?>
    </select>
    <button type="button" id="addExtraGruppe">➕ Hinzufügen</button>
    <button type="button" id="clearExtraGruppe">🗑️ Leeren</button>
    <span class="required-star" id="extraGruppeStar" style="display:none;">*</span>
  </div>
  <ul class="extra-group-list" id="extraGruppeList"></ul>
  <!-- Hidden field for submit -->
  <input type="hidden" name="extraGruppe" id="extraGruppeHidden">

  <script>
    // Maximal eine Extra-Gruppe
    let extraGruppe = "";

    document.getElementById('addExtraGruppe').addEventListener('click', function() {
      const select = document.getElementById('extraGruppeSelect');
      const value = select.value;
      if (!value) return;
      if (extraGruppe) {
        alert("Es kann nur eine Extra-Gruppe ausgewählt werden. Bitte zuerst leeren.");
        return;
      }
      extraGruppe = value;
      document.getElementById('extraGruppeList').innerHTML =
        `<li>${value} <button type="button" onclick="removeExtraGruppe()">Entfernen</button></li>`;
      document.getElementById('extraGruppeHidden').value = value;
      document.getElementById('extraGruppeStar').style.display = 'inline';
    });

    document.getElementById('clearExtraGruppe').addEventListener('click', function() {
      extraGruppe = "";
      document.getElementById('extraGruppeList').innerHTML = "";
      document.getElementById('extraGruppeHidden').value = "";
      document.getElementById('extraGruppeStar').style.display = 'none';
    });

    window.removeExtraGruppe = function() {
      extraGruppe = "";
      document.getElementById('extraGruppeList').innerHTML = "";
      document.getElementById('extraGruppeHidden').value = "";
      document.getElementById('extraGruppeStar').style.display = 'none';
    };
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
