<?php
/** @var array $item */
/** @var array $afdelingen */
$statussen = ['aangevraagd' => 'Aangevraagd', 'goedgekeurd' => 'Goedgekeurd', 'afgekeurd' => 'Afgekeurd', 'besteld' => 'Besteld', 'geleverd' => 'Geleverd'];
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/hardware-uitgaven/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Uitgave #<?= $item['id'] ?> bewerken</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/hardware-uitgaven/<?= $item['id'] ?>">
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1"><label class="form-label">Omschrijving</label><input type="text" name="omschrijving" value="<?= htmlspecialchars($item['omschrijving']) ?>" required></div>
      <div class="form-group"><label class="form-label">Leverancier</label><input type="text" name="leverancier" value="<?= htmlspecialchars($item['leverancier'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Bedrag (&euro;)</label><input type="number" step="0.01" name="bedrag" value="<?= htmlspecialchars((string) $item['bedrag']) ?>" required></div>
      <div class="form-group"><label class="form-label">Aankoopdatum</label><input type="date" name="aankoopdatum" value="<?= htmlspecialchars($item['aankoopdatum'] ?? '') ?>"></div>
      <div class="form-group">
        <label class="form-label">Afdeling</label>
        <select name="afdeling_id">
          <option value="">— Selecteer afdeling —</option>
          <?php foreach ($afdelingen as $a): ?>
            <option value="<?= $a['id'] ?>" <?= $item['afdeling_id'] == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['naam']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status">
          <?php foreach ($statussen as $val => $label): ?>
            <option value="<?= $val ?>" <?= $item['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/hardware-uitgaven/<?= $item['id'] ?>">Annuleren</a>
    </div>
  </form>
</div>
