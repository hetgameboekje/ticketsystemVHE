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
      <div class="form-group">
        <label class="form-label">Subcategorie</label>
        <input type="text" name="subcategorie" id="subcategorie-input" list="subcategorie-opties" value="<?= htmlspecialchars($item['subcategorie'] ?? '') ?>" placeholder="optioneel" autocomplete="off">
        <datalist id="subcategorie-opties"></datalist>
      </div>
      <div class="form-group">
        <label class="form-label">Samenvatting</label>
        <input type="text" name="samenvatting" value="<?= htmlspecialchars($item['samenvatting'] ?? '') ?>" placeholder="Korte omschrijving voor in de artikellijst" maxlength="255">
      </div>
      <div class="form-group">
        <label class="form-label">Tags</label>
        <input type="text" name="tags" id="tags-input" list="tags-opties" value="<?= htmlspecialchars($item['tags'] ?? '') ?>" placeholder="komma-gescheiden, bijv. vpn, netwerk, remote" autocomplete="off">
        <datalist id="tags-opties"></datalist>
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
    function bindAutocomplete(inputId, listId, urlFn) {
        var input = document.getElementById(inputId);
        var list = document.getElementById(listId);
        var timer = null;

        input.addEventListener('input', function () {
            clearTimeout(timer);
            var q = input.value.trim();
            if (q.length < 2) {
                list.innerHTML = '';
                return;
            }
            timer = setTimeout(function () {
                fetch(urlFn(q))
                    .then(function (r) { return r.json(); })
                    .then(function (opties) {
                        list.innerHTML = '';
                        opties.forEach(function (o) {
                            var opt = document.createElement('option');
                            opt.value = o;
                            list.appendChild(opt);
                        });
                    });
            }, 200);
        });
    }

    bindAutocomplete('categorie-input', 'categorie-opties', function (q) {
        return '/kennisbank/categorieen?q=' + encodeURIComponent(q);
    });

    bindAutocomplete('subcategorie-input', 'subcategorie-opties', function (q) {
        var categorie = document.getElementById('categorie-input').value.trim();
        return '/kennisbank/subcategorieen?categorie=' + encodeURIComponent(categorie) + '&q=' + encodeURIComponent(q);
    });

    // Tags: autocomplete op het laatste, nog niet afgeronde tag-fragment na de laatste komma.
    var tagsInput = document.getElementById('tags-input');
    var tagsList = document.getElementById('tags-opties');
    var tagsTimer = null;

    tagsInput.addEventListener('input', function () {
        clearTimeout(tagsTimer);
        var parts = tagsInput.value.split(',');
        var fragment = parts[parts.length - 1].trim();
        if (fragment.length < 2) {
            tagsList.innerHTML = '';
            return;
        }
        tagsTimer = setTimeout(function () {
            fetch('/kennisbank/tags?q=' + encodeURIComponent(fragment))
                .then(function (r) { return r.json(); })
                .then(function (tags) {
                    var prefix = parts.slice(0, -1).map(function (p) { return p.trim(); }).filter(Boolean);
                    tagsList.innerHTML = '';
                    tags.forEach(function (t) {
                        var opt = document.createElement('option');
                        opt.value = prefix.concat([t]).join(', ');
                        tagsList.appendChild(opt);
                    });
                });
        }, 200);
    });
})();
</script>
