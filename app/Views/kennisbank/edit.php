<?php /** @var array $item */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/kennisbank/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Artikel #<?= $item['id'] ?> bewerken</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/kennisbank/<?= $item['id'] ?>">
    <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" value="<?= htmlspecialchars($item['titel']) ?>" required></div>
    <div class="form-group"><label class="form-label">Categorie</label><input type="text" name="categorie" value="<?= htmlspecialchars($item['categorie']) ?>"></div>
    <div class="form-group"><label class="form-label">Inhoud</label><textarea name="inhoud" style="min-height:160px" required><?= htmlspecialchars($item['inhoud']) ?></textarea></div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/kennisbank/<?= $item['id'] ?>">Annuleren</a>
    </div>
  </form>
</div>
