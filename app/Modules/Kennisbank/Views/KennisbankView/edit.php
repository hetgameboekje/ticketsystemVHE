<?php
/** @var array $item */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/kennisbank/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Artikel #<?= $item['id'] ?> bewerken</div>
  </div>
</div>

<form method="post" action="/kennisbank/<?= $item['id'] ?>">
  <div class="card" style="margin-bottom:16px">
    <div style="padding:16px">
      <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" value="<?= htmlspecialchars($item['titel']) ?>" required></div>
      <div class="form-group">
        <label class="form-label">Categorie</label>
        <input type="text" name="categorie" id="categorie-input" list="categorie-opties" value="<?= htmlspecialchars($item['categorie']) ?>" autocomplete="off">
        <datalist id="categorie-opties"></datalist>
      </div>
      <div class="form-group"><label class="form-label">Inhoud</label><textarea name="inhoud" style="min-height:160px" required><?= htmlspecialchars($item['inhoud']) ?></textarea></div>
    </div>
  </div>

  <div style="display:flex;gap:8px;margin-top:8px">
    <button class="btn btn-primary" type="submit">Opslaan</button>
    <a class="btn" href="/kennisbank/<?= $item['id'] ?>">Annuleren</a>
  </div>
</form>

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
            fetch('/kennisbank/categorieen?q=' + encodeURIComponent(q))
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
