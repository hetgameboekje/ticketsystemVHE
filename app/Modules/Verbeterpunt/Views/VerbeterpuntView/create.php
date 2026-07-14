<?php /** @var array $afdelingen */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/verbeterpunten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuw verbeterpunt</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/verbeterpunten">
    <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" required></div>
    <div class="form-group">
      <label class="form-label">Afdeling</label>
      <select name="afdeling_id">
        <option value="">— Selecteer afdeling —</option>
        <?php foreach ($afdelingen as $a): ?>
          <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['naam']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Omschrijving</label><textarea name="omschrijving" style="min-height:100px" required></textarea></div>
    <div class="form-group">
      <label class="form-label">Categorie</label>
      <input type="text" name="categorie" id="categorie-input" list="categorie-opties" placeholder="bijv. Proces, Veiligheid, Communicatie" value="Algemeen" autocomplete="off">
      <datalist id="categorie-opties"></datalist>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Indienen</button>
      <a class="btn" href="/verbeterpunten">Annuleren</a>
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
