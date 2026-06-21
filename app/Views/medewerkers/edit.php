<?php
/** @var array $item */
/** @var array $afdelingen */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/medewerkers/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Medewerker #<?= $item['id'] ?> bewerken</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/medewerkers/<?= $item['id'] ?>">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      <div class="form-group"><label class="form-label">Voornaam</label><input type="text" name="voornaam" value="<?= htmlspecialchars($item['voornaam']) ?>" required></div>
      <div class="form-group"><label class="form-label">Achternaam</label><input type="text" name="achternaam" value="<?= htmlspecialchars($item['achternaam']) ?>" required></div>
      <div class="form-group"><label class="form-label">E-mail</label><input type="email" name="email" value="<?= htmlspecialchars($item['email'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Telefoon</label><input type="tel" name="telefoon" value="<?= htmlspecialchars($item['telefoon'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Functie</label><input type="text" name="functie" value="<?= htmlspecialchars($item['functie'] ?? '') ?>"></div>
      <div class="form-group">
        <label class="form-label">Afdeling</label>
        <select name="afdeling_id">
          <option value="">— Selecteer afdeling —</option>
          <?php foreach ($afdelingen as $a): ?>
            <option value="<?= $a['id'] ?>" <?= $item['afdeling_id'] == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['naam']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Startdatum</label><input type="date" name="startdatum" value="<?= htmlspecialchars($item['startdatum'] ?? '') ?>"></div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status">
          <option value="actief" <?= $item['status'] === 'actief' ? 'selected' : '' ?>>Actief</option>
          <option value="inactief" <?= $item['status'] === 'inactief' ? 'selected' : '' ?>>Inactief</option>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/medewerkers/<?= $item['id'] ?>">Annuleren</a>
    </div>
  </form>
</div>
