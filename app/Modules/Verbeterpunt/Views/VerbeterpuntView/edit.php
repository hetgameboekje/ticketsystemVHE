<?php
/** @var array $item */
/** @var array $afdelingen */
$statussen = ['nieuw' => 'Nieuw', 'in_overweging' => 'In overweging', 'goedgekeurd' => 'Goedgekeurd', 'afgewezen' => 'Afgewezen', 'uitgevoerd' => 'Uitgevoerd'];
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/verbeterpunten/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Verbeterpunt #<?= $item['id'] ?> bewerken</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/verbeterpunten/<?= $item['id'] ?>">
    <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" value="<?= htmlspecialchars($item['titel']) ?>" required></div>
    <div class="form-group">
      <label class="form-label">Afdeling</label>
      <select name="afdeling_id">
        <option value="">— Selecteer afdeling —</option>
        <?php foreach ($afdelingen as $a): ?>
          <option value="<?= $a['id'] ?>" <?= $item['afdeling_id'] == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['naam']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Omschrijving</label><textarea name="omschrijving" style="min-height:100px" required><?= htmlspecialchars($item['omschrijving']) ?></textarea></div>
    <div class="form-group">
      <label class="form-label">Categorie</label>
      <input type="text" name="categorie" id="categorie-input" list="categorie-opties" value="<?= htmlspecialchars($item['categorie'] ?? 'Algemeen') ?>" autocomplete="off">
      <datalist id="categorie-opties"></datalist>
    </div>
    <div class="form-group">
      <label class="form-label">Status</label>
      <select name="status">
        <?php foreach ($statussen as $val => $label): ?>
          <option value="<?= $val ?>" <?= $item['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/verbeterpunten/<?= $item['id'] ?>">Annuleren</a>
    </div>
  </form>
</div>

<script>
(function () {
    var input = document.getElementById('categorie-input');
    var list = document.getElementById('categorie-opties');
    var timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) {
            list.innerHTML = '';
            return;
        }
        timer = setTimeout(function () {
            fetch('/verbeterpunten/categorieen?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (categorieen) {
                    list.innerHTML = '';
                    categorieen.forEach(function (c) {
                        var opt = document.createElement('option');
                        opt.value = c;
                        list.appendChild(opt);
                    });
                });
        }, 200);
    });
})();
</script>
