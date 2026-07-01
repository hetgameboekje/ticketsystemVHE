<?php
/** @var array $afdelingen */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/medewerkers" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuwe medewerker</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/medewerkers">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Voornaam</label><input type="text" name="voornaam" required></div>
      <div class="form-group"><label class="form-label">Achternaam</label><input type="text" name="achternaam" required></div>
      <div class="form-group">
        <label class="form-label">E-mail</label>
        <div style="display:flex;align-items:center;gap:8px">
          <input type="email" name="email" id="medewerkerEmail" style="flex:1">
          <span id="loginCheckIcon"></span>
        </div>
        <div id="loginCheckTekst" style="font-size:12px;color:var(--color-text-secondary);margin-top:4px"></div>
      </div>
      <div class="form-group"><label class="form-label">Telefoon</label><input type="tel" name="telefoon"></div>
      <div class="form-group"><label class="form-label">Functie</label><input type="text" name="functie"></div>
      <div class="form-group">
        <label class="form-label">Afdeling</label>
        <select name="afdeling_id">
          <option value="">— Selecteer afdeling —</option>
          <?php foreach ($afdelingen as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['naam']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Startdatum</label><input type="date" name="startdatum"></div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/medewerkers">Annuleren</a>
    </div>
  </form>
</div>

<script>
(function () {
    var emailInput = document.getElementById('medewerkerEmail');
    var icon = document.getElementById('loginCheckIcon');
    var tekst = document.getElementById('loginCheckTekst');
    var timer = null;

    function toon(status) {
        if (status === 'gevonden') {
            icon.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#27500A"></i>';
            tekst.textContent = 'Login gevonden — wordt automatisch gekoppeld.';
        } else if (status === 'bezet') {
            icon.innerHTML = '<i class="bi bi-x-circle-fill" style="color:#b3261e"></i>';
            tekst.textContent = 'Er bestaat een login met dit e-mailadres, maar die is al aan een andere medewerker gekoppeld.';
        } else {
            icon.innerHTML = '';
            tekst.textContent = '';
        }
    }

    emailInput.addEventListener('input', function () {
        var email = emailInput.value.trim();
        clearTimeout(timer);

        if (email === '' || !email.includes('@')) {
            toon(null);
            return;
        }

        timer = setTimeout(function () {
            fetch('/medewerkers/login-check?email=' + encodeURIComponent(email))
                .then(function (r) { return r.json(); })
                .then(function (data) { toon(data.status); })
                .catch(function () { toon(null); });
        }, 400);
    });
})();
</script>
