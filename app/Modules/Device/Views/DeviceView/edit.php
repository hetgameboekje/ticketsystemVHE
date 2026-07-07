<?php
/** @var array $item */
/** @var array $medewerkers */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/apparaten/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Apparaat bewerken</div>
  </div>
</div>

<div class="card new-form" style="max-width:520px">
  <form method="post" action="/apparaten/<?= $item['id'] ?>">
    <div class="form-group">
      <label class="form-label">Naam</label>
      <input type="text" name="naam" value="<?= htmlspecialchars($item['naam']) ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label">Medewerker</label>
      <select name="medewerker_id">
        <option value="">Geen</option>
        <?php foreach ($medewerkers as $m): ?>
          <option value="<?= $m['id'] ?>" <?= (int) ($item['medewerker_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['voornaam'] . ' ' . $m['achternaam']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary" type="submit">Opslaan</button>
  </form>
</div>
