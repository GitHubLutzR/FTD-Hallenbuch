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
.multi-group-list {
  margin: 0 0 8px 0;
  padding: 0;
  list-style: none;
    /* Einrückung wie die Felder */
  margin-left: 100px;
}
.multi-group-list li {
  display: inline-block;
  background: #eee;
  border-radius: 4px;
  padding: 2px 8px;
  margin-right: 6px;
  font-size: 0.95em;
}
.multi-group-list button {
  background: #c00;
  color: #fff;
  border: none;
  border-radius: 2px;
  margin-left: 4px;
  cursor: pointer;
  font-size: 0.9em;
  padding: 0 4px;
}
.smal-txt {
  font-size: 1em;
  margin-left: 2px;
  vertical-align: super;
}</style>

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
    <label for="Gruppe">Gruppen:</label>
    <select id="GruppeSelect">
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
    <button type="button" id="addGruppe">➕ Hinzufügen</button>
    <button type="button" id="clearGruppe">🗑️ Leeren</button>
    <?php //<span class="required-star" id="GruppeStar" style="display:none;">*</span>?>
    <span class="required-star">*</span>
    <span class="smal-txt">&nbsp;&nbsp;&nbsp; max. 2 Gruppen</span>
  </div>
  <ul class="multi-group-list" id="GruppeList"></ul>
  <!-- Hidden field for submit -->
  <input type="hidden" name="gruppe" id="GruppeHidden">

  <script>
    // Maximal zwei Gruppen
    let Gruppen = [];

    document.getElementById('addGruppe').addEventListener('click', function() {
      const select = document.getElementById('GruppeSelect');
      const value = select.value;
      if (!value) return;
      if (Gruppen.length >= 2) {
        alert("Es können maximal zwei Gruppen ausgewählt werden. Bitte zuerst leeren oder entfernen.");
        return;
      }
      if (Gruppen.includes(value)) {
        alert("Diese Gruppe wurde bereits hinzugefügt.");
        return;
      }
      Gruppen.push(value);
      updateGruppeList();
    });

    document.getElementById('clearGruppe').addEventListener('click', function() {
      Gruppen = [];
      updateGruppeList();
    });

    window.removeGruppe = function(idx) {
      Gruppen.splice(idx, 1);
      updateGruppeList();
    };

    function updateGruppeList() {
      const list = document.getElementById('GruppeList');
      list.innerHTML = "";
      Gruppen.forEach((g, idx) => {
        list.innerHTML += `<li>${g} <button type="button" onclick="removeGruppe(${idx})">Entfernen</button></li>`;
      });
      // Zusammenfassen mit /
      document.getElementById('GruppeHidden').value = Gruppen.join('/');
      document.getElementById('GruppeStar').style.display = Gruppen.length ? 'inline' : 'none';
    }
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
