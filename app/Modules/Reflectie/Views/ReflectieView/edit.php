<?php /** @var array $item */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/reflecties/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Reflectie #<?= $item['id'] ?> bewerken</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/reflecties/<?= $item['id'] ?>">
    <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" value="<?= htmlspecialchars($item['titel']) ?>" required></div>
    <div class="form-group"><label class="form-label">Periode</label><input type="text" name="periode" value="<?= htmlspecialchars($item['periode']) ?>" required></div>
    <div class="form-group"><label class="form-label">Inhoud</label><textarea name="inhoud" style="min-height:140px" required><?= htmlspecialchars($item['inhoud']) ?></textarea></div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/reflecties/<?= $item['id'] ?>">Annuleren</a>
    </div>
  </form>
</div>
