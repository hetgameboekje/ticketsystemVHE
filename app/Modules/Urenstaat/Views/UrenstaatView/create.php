<?php
/** @var array $locaties */
/** @var array $keyusers */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/urenstaat" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Tijd registreren</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/urenstaat">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Datum</label><input type="date" name="datum" required></div>
      <div class="form-group"><label class="form-label">Locatie</label>
        <select name="locatie_id" required>
          <option value="">Kies een locatie...</option>
          <?php foreach ($locaties as $locatie): ?>
            <option value="<?= (int) $locatie['id'] ?>"><?= htmlspecialchars($locatie['naam']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Keyuser/klant</label>
        <select name="keyuser_id">
          <option value="">— Geen —</option>
          <?php foreach ($keyusers as $k): ?>
            <option value="<?= (int) $k['id'] ?>"><?= htmlspecialchars($k['achternaam'] . ', ' . $k['voornaam']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Starttijd</label><input type="time" name="start_tijd" required></div>
      <div class="form-group"><label class="form-label">Eindtijd</label><input type="time" name="eind_tijd" required></div>
    </div>
    <div class="form-group"><label class="form-label">Omschrijving</label><textarea name="omschrijving" placeholder="Optioneel"></textarea></div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/urenstaat">Annuleren</a>
    </div>
  </form>
</div>
