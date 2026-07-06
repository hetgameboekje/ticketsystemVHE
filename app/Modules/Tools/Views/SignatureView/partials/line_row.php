<?php
/** @var string|int $index */
/** @var array $line */
/** @var array $icons */
?>
<div class="line-row" style="border:0.5px solid var(--color-border-secondary);border-radius:var(--border-radius-md);padding:12px;margin-bottom:8px">
  <div class="form-grid">
    <div class="form-group">
      <label class="form-label">Type</label>
      <select name="lines[<?= $index ?>][type]" data-role="type">
        <option value="text" <?= ($line['type'] ?? 'text') === 'text' ? 'selected' : '' ?>>Tekst</option>
        <option value="icon" <?= ($line['type'] ?? 'text') === 'icon' ? 'selected' : '' ?>>Icoon + tekst</option>
      </select>
    </div>
    <div class="form-group" data-role="icon-field">
      <label class="form-label">Icoon</label>
      <select name="lines[<?= $index ?>][icon]">
        <?php foreach ($icons as $key => $icon): ?>
          <option value="<?= htmlspecialchars($key) ?>" <?= ($line['icon'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($icon['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-grid">
    <div class="form-group">
      <label class="form-label">Tekst</label>
      <input type="text" name="lines[<?= $index ?>][text]" value="<?= htmlspecialchars($line['text'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Link (optioneel, bv. mailto: of https://)</label>
      <input type="text" name="lines[<?= $index ?>][href]" value="<?= htmlspecialchars($line['href'] ?? '') ?>">
    </div>
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between">
    <label style="display:flex;align-items:center;gap:6px;font-weight:normal">
      <input type="checkbox" name="lines[<?= $index ?>][bold]" <?= !empty($line['bold']) ? 'checked' : '' ?>>
      Vet
    </label>
    <button type="button" class="btn btn-danger" data-role="remove">Verwijderen</button>
  </div>
</div>
