<?php /** @var array $item */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/printers/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Printer #<?= $item['id'] ?> bewerken</div>
  </div>
</div>

<div class="card">
  <form class="new-form" method="post" action="/printers/<?= $item['id'] ?>">
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Naam</label>
        <input type="text" name="naam" value="<?= htmlspecialchars($item['naam']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Server (ComputerName)</label>
        <input type="text" name="computer_naam" value="<?= htmlspecialchars($item['computer_naam'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Type</label>
        <input type="text" name="type" value="<?= htmlspecialchars($item['type']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Driver</label>
        <input type="text" name="driver_naam" value="<?= htmlspecialchars($item['driver_naam'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">IP-adres / poort</label>
        <input type="text" name="ip_adres" value="<?= htmlspecialchars($item['ip_adres'] ?? '') ?>">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Opmerking</label>
        <textarea name="opmerking"><?= htmlspecialchars($item['opmerking'] ?? '') ?></textarea>
      </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/printers/<?= $item['id'] ?>">Annuleren</a>
    </div>
  </form>
</div>
