<?php /** @var array $afdelingen */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/medewerkers" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuwe medewerker</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/medewerkers">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      <div class="form-group"><label class="form-label">Voornaam</label><input type="text" name="voornaam" required></div>
      <div class="form-group"><label class="form-label">Achternaam</label><input type="text" name="achternaam" required></div>
      <div class="form-group"><label class="form-label">E-mail</label><input type="email" name="email"></div>
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
