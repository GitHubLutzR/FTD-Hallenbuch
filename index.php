<?php
//session_start();

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/include_public.php');
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
#$table = $hesk_settings['db_hb_pfix'] . 'gruppen';
$grtable = $hesk_settings['db_hb_pfix'] . 'gruppen';
$trtable = $hesk_settings['db_hb_pfix'] . 'trainer';

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
  font-size: 0.5em;
  margin-left: 2px;
  vertical-align: super;
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
    <label for="Gruppe">Gruppen:</label>
    <select id="GruppeSelect">
      <option value="">Bitte wählen</option>
      <?php
      $safe_grtable = preg_replace('/[^A-Za-z0-9_]/', '', $grtable);
      $query_gr = "SELECT id, name FROM `{$safe_grtable}` ORDER BY name = 'sonstige' DESC, name ASC";
      $grresult = mysqli_query($conn, $query_gr);
      if ($grresult === false) {
          echo "<option value=''>Fehler beim Laden der Gruppen</option>";
          echo "<!-- QUERY ERROR: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . " -->";
      } else {
          while ($row = mysqli_fetch_assoc($grresult)) {
              $id = (int) $row['id'];
              // decode entities from DB then escape for safe HTML output
              $rawName = $row['name'] ?? '';
              $decodedName = html_entity_decode($rawName, ENT_QUOTES, 'UTF-8');
              $safeName = htmlspecialchars($decodedName, ENT_QUOTES, 'UTF-8');
              echo "<option value=\"$id\">$safeName</option>";
          }
      }
      ?>
    </select>
    <button type="button" id="addGruppe">➕ Hinzufügen</button>
    <button type="button" id="clearGruppe">🗑️ Leeren</button>
    <span class="required-star">*</span>
    <span class="smal-txt">&nbsp;&nbsp;&nbsp; max. 2 Gruppen</span>
  </div>

  <div class="form-row" id="gruppe-sonstige-row" style="display:none;">
    <label for="gruppe_sonstige">Bitte Gruppe angeben:</label>
    <input type="text" name="gruppe_sonstige" id="gruppe_sonstige" maxlength="100">
    <span class="required-star">*</span>
  </div>
  <div class="form-row" id="trainer-sonstige-row" style="display:none;">
    <label for="trainer_sonstige">Bitte Trainer/ -innen angeben:</label>
    <input type="text" name="trainer_sonstige" id="trainer_sonstige" maxlength="100">
    <span class="required-star">*</span>
  </div>

  <ul class="multi-group-list" id="GruppeList"></ul>
  <div id="GruppeHint" style="margin-left:100px; color:#888; font-size:0.95em;">Bitte mindestens eine Gruppe auswählen und auf ➕ Hinzufügen klicken.</div>

  <!-- Hidden-Felder: gruppe (Namen, für submit.php) und gruppe_ids (IDs, für JS/AJAX intern) -->
  <input type="hidden" name="gruppe" id="GruppeHidden">         <!-- z.B. "GrA/GrB" -->
  <input type="hidden" name="gruppe_ids" id="GruppeHiddenIDs">  <!-- z.B. "3/5" -->

<script>
(function(){
  // Array von Objekten {id:..., name:...}
  let Gruppen = [];
  const maxGroups = 2;

  // helper: normalize (lowercase, remove punctuation, collapse spaces)
  function normalizeName(s){
    return (s||'').toLowerCase().replace(/[^a-z0-9äöüß\s]/g,'').replace(/\s+/g,' ').trim();
  }

  function isSonstigeSelected() {
    return Gruppen.length === 1 && normalizeName(Gruppen[0].name) === 'sonstige';
  }

  // prüft nur den Anfang des Gruppennamens (z.B. "Kita Wingertstr." passt auch für "Kita Wingertstr. - Gruppe A")
  function isKiTaWSelected() {
    return Gruppen.length === 1 && normalizeName(Gruppen[0].name).startsWith('kita');
  }

  function updateGruppeList() {
      const list = document.getElementById('GruppeList');
      list.innerHTML = '';
      Gruppen.forEach((g, idx) => {
        const li = document.createElement('li');
        li.innerHTML = `${g.name} <button type="button" onclick="removeGruppe(${idx})">Entfernen</button>`;
        list.appendChild(li);
      });
      // Hidden mit IDs und Hidden mit Namen füllen (IDs: "3/5", Namen: "GrA/GrB")
      document.getElementById('GruppeHiddenIDs').value = Gruppen.map(g => g.id).join('/');
      document.getElementById('GruppeHidden').value = Gruppen.map(g => g.name).join('/');
      document.getElementById('GruppeHint').style.display = Gruppen.length ? 'none' : 'block';

      // Sonstige-Feld anzeigen/verstecken
      if (isSonstigeSelected() || isKiTaWSelected()) {
        document.getElementById('GruppeSelect').disabled = true;
        document.getElementById('addGruppe').disabled = true;
        document.getElementById('gruppe-sonstige-row').style.display = 'flex';
        document.getElementById('trainer-sonstige-row').style.display = 'flex';
        document.getElementById('TrainerSelect').style.display = 'none';
        document.getElementById('addTrainer').style.display = 'none';
        document.getElementById('clearTrainer').style.display = 'none';
        if (isSonstigeSelected() ) {
          document.getElementById('gruppe_sonstige').value = 'sonstige';
        } else {
          document.getElementById('gruppe_sonstige').value = 'Kita Wingertstr.';
        }
        document.getElementById('TrainerHidden').value = 'sonstige';
      } else {
        document.getElementById('GruppeSelect').disabled = false;
        document.getElementById('addGruppe').disabled = false;
        document.getElementById('gruppe-sonstige-row').style.display = 'none';
        document.getElementById('trainer-sonstige-row').style.display = 'none';
        document.getElementById('TrainerSelect').style.display = 'flex';
        document.getElementById('addTrainer').style.display = 'flex';
        document.getElementById('clearTrainer').style.display = 'flex';
        document.getElementById('gruppe_sonstige').value = '';
        document.getElementById('trainer_sonstige').value = '';
        document.getElementById('TrainerHidden').value = '';
      }

     // aktualisiere Trainerliste anhand aktueller Gruppen (IDs)
     if (typeof refreshTrainerOptions === 'function') {
       refreshTrainerOptions(Gruppen.map(g => g.id));
     }
    }

  document.getElementById('addGruppe').addEventListener('click', function() {
    const sel = document.getElementById('GruppeSelect');
    const val = sel.value;
    if (!val) return;
    const name = sel.options[sel.selectedIndex].text.trim();
    const id = val;
    if (Gruppen.some(g => g.id == id)) {
      alert("Diese Gruppe wurde bereits hinzugefügt.");
      return;
    }
    if (Gruppen.length >= maxGroups) {
      alert("Es können maximal " + maxGroups + " Gruppen ausgewählt werden.");
      return;
    }
    // Sonstige (Name) behandeln: wenn Name == 'sonstige' nur diese zulassen
    if (name.toLowerCase() === 'sonstige') {
      Gruppen = [{id: id, name: name}];
      updateGruppeList();
      return;
    }
    if (Gruppen.length === 1 && Gruppen[0].name.toLowerCase() === 'sonstige') {
      alert("Wenn 'sonstige' gewählt wurde, kann keine weitere Gruppe hinzugefügt werden.");
      return;
    }
    Gruppen.push({id: id, name: name});
    updateGruppeList();
  });

  document.getElementById('clearGruppe').addEventListener('click', function() {
    Gruppen = [];
    updateGruppeList();
  });

  // removeGruppe als global verfügbar machen (wird in onclick verwendet)
  window.removeGruppe = function(idx) {
    Gruppen.splice(idx, 1);
    updateGruppeList();
  };

  // initial: wenn du bereits IDs/Namen serverseitig vorbefüllst, hier parsen und setzen
  updateGruppeList();
  // initial Trainer-Refresh (falls keine Gruppen gesetzt, werden alle Trainer gezeigt)
  if (typeof refreshTrainerOptions === 'function') {
   refreshTrainerOptions(Gruppen.map(g => g.id));
  }
})();
</script>


  <div class="form-row">
    <label for="Trainer">Trainer/<br>-innen:</label>
    <select id="TrainerSelect">
      <option value="">Bitte wählen</option>
    </select>
    <button type="button" id="addTrainer">➕ Hinzufügen</button>
    <button type="button" id="clearTrainer">🗑️ Leeren</button>
    <span class="required-star">*</span>
    <span class="smal-txt">&nbsp;&nbsp;&nbsp; max. 4 Trainer/-innen</span>
  </div>
  <ul class="multi-group-list" id="TrainerList"></ul>
  <div id="TrainerHint" style="margin-left:100px; color:#888; font-size:0.95em;">Bitte mindestens einer Trainer/-innen auswählen und auf ➕ Hinzufügen klicken.</div>
  <BR><BR> <input type="hidden" name="trainer" id="TrainerHidden">

  <?php
// Serverseitig: Map group_id => [trainerNames] und Gesamtliste aller Trainer
$safe_trtable = preg_replace('/[^A-Za-z0-9_]/', '', $trtable);
$gtq = "SELECT trname, gruppe_id FROM `{$safe_trtable}` ORDER BY trname ASC";
$gtr = mysqli_query($conn, $gtq);
$group_trainers = [];
$all_set = [];
if ($gtr) {
    while ($r = mysqli_fetch_assoc($gtr)) {
        // decode HTML entities from DB, trim and skip empty names
        $raw = $r['trname'] ?? '';
        $name = trim(html_entity_decode($raw, ENT_QUOTES, 'UTF-8'));
        if ($name === '') continue;
        $gid  = (int)($r['gruppe_id'] ?? 0);
        $group_trainers[$gid][] = $name;
        $all_set[$name] = true;
    }
}
$all_trainers = array_values(array_keys($all_set));
?>
<script>
// GROUP_TRAINERS: { groupId: [names...] }, ALL_TRAINERS: [names...]
const GROUP_TRAINERS = <?php echo json_encode($group_trainers, JSON_UNESCAPED_UNICODE); ?>;
const ALL_TRAINERS = <?php echo json_encode($all_trainers, JSON_UNESCAPED_UNICODE); ?>;

function refreshTrainerOptions(selectedGroupIds) {
  const sel = document.getElementById('TrainerSelect');
  if (!sel) return;
  // Sammle zuerst Trainer aus ausgewählten Gruppen (unique)
  const seen = new Set();
  const primary = [];
  (selectedGroupIds || []).forEach(gid => {
    const arr = GROUP_TRAINERS[gid] || [];
    arr.forEach(n => {
      if (!seen.has(n)) { seen.add(n); primary.push(n); }
    });
  });
  primary.sort((a,b)=> a.localeCompare(b, undefined, {sensitivity:'base'}));
  // Dann die restlichen Trainer
  const secondary = [];
  ALL_TRAINERS.forEach(n => { if (!seen.has(n)) secondary.push(n); });
  secondary.sort((a,b)=> a.localeCompare(b, undefined, {sensitivity:'base'}));

  // Optionen neu aufbauen
  // behalte erste Option (placeholder)
  sel.innerHTML = '<option value="">Bitte wählen</option>';
  primary.concat(secondary).forEach(name => {
    const opt = document.createElement('option');
    opt.value = name;
    opt.textContent = name;
    sel.appendChild(opt);
  });
}

// Expose for group JS
window.refreshTrainerOptions = refreshTrainerOptions;
</script>

<script>
(function(){
  // Trainer UI: Hinzufügen / Entfernen / Hidden-Feld pflegen
  const sel = document.getElementById('TrainerSelect');
  const addBtn = document.getElementById('addTrainer');
  const clearBtn = document.getElementById('clearTrainer');
  const listEl = document.getElementById('TrainerList');
  const hint  = document.getElementById('TrainerHint');
  const hidden = document.getElementById('TrainerHidden');
  const MAX_TR = 4;
  let Trainers = []; // array of names

  function updateTrainerUI(){
    listEl.innerHTML = '';
    Trainers.forEach((t, idx) => {
      const li = document.createElement('li');
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = 'Entfernen';
      btn.addEventListener('click', () => { removeTrainer(idx); });
      li.textContent = t + ' ';
      li.appendChild(btn);
      listEl.appendChild(li);
    });
    hidden.value = Trainers.join('/');
    if (hint) hint.style.display = Trainers.length ? 'none' : 'block';
  }

  function addTrainer(){
    if (!sel) return;
    const v = sel.value;
    if (!v) return alert('Bitte einen Trainer auswählen.');
    if (Trainers.includes(v)) return alert('Trainer bereits hinzugefügt.');
    if (Trainers.length >= MAX_TR) return alert('Maximal ' + MAX_TR + ' Trainer möglich.');
    // optional: handle 'sonstige' specially if needed
    Trainers.push(v);
    updateTrainerUI();
  }

  function clearTrainers(){
    Trainers = [];
    updateTrainerUI();
  }

  window.removeTrainer = function(idx){
    Trainers.splice(idx, 1);
    updateTrainerUI();
  };

  if (addBtn) addBtn.addEventListener('click', addTrainer);
  if (clearBtn) clearBtn.addEventListener('click', clearTrainers);

  // Falls bereits ein Wert im hidden (z.B. bei Reload), initial befüllen
  if (hidden && hidden.value) {
    Trainers = hidden.value.split('/').map(s=>s.trim()).filter(Boolean);
    updateTrainerUI();
  }
})();
</script>

  <!--
  <div class="form-row">
    <label for="leiter">Leiter: </label>
    <input type="text" name="leiter" id="leiter" required>
    <span class="required-star">*</span>
  </div>
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
