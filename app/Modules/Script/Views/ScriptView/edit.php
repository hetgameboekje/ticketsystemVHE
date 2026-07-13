<?php
/** @var array $item */
/** @var array $scriptTypes */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/scripts/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Script #<?= $item['id'] ?> bewerken</div>
  </div>
</div>

<form method="post" action="/scripts/<?= $item['id'] ?>">
  <div class="card" style="margin-bottom:16px">
    <div style="padding:16px">
      <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" value="<?= htmlspecialchars($item['titel']) ?>" required></div>
      <div class="form-group">
        <label class="form-label">Script type</label>
        <select name="type">
          <?php foreach ($scriptTypes as $val => $label): ?>
            <option value="<?= htmlspecialchars($val) ?>" <?= ($item['type'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Omschrijving (kort: welke actie voert dit uit?)</label>
        <input type="text" name="omschrijving" value="<?= htmlspecialchars($item['omschrijving'] ?? '') ?>" placeholder="Bijv. Printerpool resetten">
      </div>
      <div class="form-group">
        <label class="form-label">Code (script-inhoud)</label>
        <textarea name="inhoud" style="min-height:200px;font-family:monospace" required><?= htmlspecialchars($item['inhoud'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:8px;margin-top:8px">
    <button class="btn btn-primary" type="submit">Opslaan</button>
    <a class="btn" href="/scripts/<?= $item['id'] ?>">Annuleren</a>
  </div>
</form>
