<?php
/** @var string|int $index */
/** @var array $line */
/** @var array $icons */
/** @var array $logos */
$type = $line['type'] ?? 'text';
?>
<div class="line-row" style="border:0.5px solid var(--color-border-secondary);border-radius:var(--border-radius-md);padding:12px;margin-bottom:8px">
  <div class="form-grid">
    <div class="form-group">
      <label class="form-label">Type</label>
      <select name="lines[<?= $index ?>][type]" data-role="type">
        <option value="text" <?= $type === 'text' ? 'selected' : '' ?>>Tekst</option>
        <option value="icon" <?= $type === 'icon' ? 'selected' : '' ?>>Icoon + tekst</option>
        <option value="logo" <?= $type === 'logo' ? 'selected' : '' ?>>Logo</option>
      </select>
    </div>
    <div class="form-group" data-role="icon-field">
      <label class="form-label">Icoon</label>
      <select name="lines[<?= $index ?>][icon]" data-role="icon-select">
        <?php foreach ($icons as $key => $icon): ?>
          <option value="<?= htmlspecialchars($key) ?>" <?= ($line['icon'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($icon['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" data-role="logo-field">
      <label class="form-label">Logo</label>
      <select name="lines[<?= $index ?>][logo_id]" data-role="logo-select">
        <option value="">— Kies een logo —</option>
        <?php foreach ($logos as $logo): ?>
          <option value="<?= (int) $logo['id'] ?>" <?= (int) ($line['logo_id'] ?? 0) === (int) $logo['id'] ? 'selected' : '' ?>><?= htmlspecialchars($logo['naam']) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (empty($logos)): ?>
        <div style="font-size:12px;color:var(--color-text-tertiary);margin-top:4px">Nog geen logo's geüpload — zie hieronder.</div>
      <?php endif; ?>
    </div>
  </div>
  <div class="form-grid">
    <div class="form-group" data-role="text-field">
      <label class="form-label">Tekst</label>
      <input type="text" name="lines[<?= $index ?>][text]" data-role="text" value="<?= htmlspecialchars($line['text'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Link (optioneel, bv. mailto: of https://)</label>
      <input type="text" name="lines[<?= $index ?>][href]" data-role="href" value="<?= htmlspecialchars($line['href'] ?? '') ?>">
    </div>
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between">
    <label style="display:flex;align-items:center;gap:6px;font-weight:normal" data-role="bold-field">
      <input type="checkbox" name="lines[<?= $index ?>][bold]" data-role="bold" <?= !empty($line['bold']) ? 'checked' : '' ?>>
      Vet
    </label>
    <button type="button" class="btn btn-danger" data-role="remove">Verwijderen</button>
  </div>
</div>
