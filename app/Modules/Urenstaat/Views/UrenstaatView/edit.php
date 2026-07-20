<?php
/** @var array $item */
/** @var array $locaties */
/** @var array $keyusers */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/urenstaat/<?= (int) $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Registratie bewerken</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/urenstaat/<?= (int) $item['id'] ?>">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Datum</label><input type="date" name="datum" value="<?= htmlspecialchars($item['datum']) ?>" required></div>
      <div class="form-group"><label class="form-label">Locatie</label>
        <select name="locatie_id" required>
          <option value="">Kies een locatie...</option>
          <?php foreach ($locaties as $locatie): ?>
            <option value="<?= (int) $locatie['id'] ?>" <?= (int) $item['locatie_id'] === (int) $locatie['id'] ? 'selected' : '' ?>><?= htmlspecialchars($locatie['naam']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Keyuser/klant</label>
        <select name="keyuser_id">
          <option value="">— Geen —</option>
          <?php foreach ($keyusers as $k): ?>
            <option value="<?= (int) $k['id'] ?>" <?= (int) ($item['keyuser_id'] ?? 0) === (int) $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['achternaam'] . ', ' . $k['voornaam']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Starttijd</label><input type="time" name="start_tijd" value="<?= substr($item['start_tijd'], 0, 5) ?>" required></div>
      <div class="form-group"><label class="form-label">Eindtijd</label><input type="time" name="eind_tijd" value="<?= substr($item['eind_tijd'], 0, 5) ?>" required></div>
    </div>
    <div class="form-group"><label class="form-label">Omschrijving</label><textarea name="omschrijving" placeholder="Optioneel"><?= htmlspecialchars($item['omschrijving'] ?? '') ?></textarea></div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/urenstaat/<?= (int) $item['id'] ?>">Annuleren</a>
    </div>
  </form>
</div>
