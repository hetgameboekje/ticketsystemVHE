<?php /** @var string $barcode */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/uitgiften" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Item toewijzen</div>
  </div>
</div>

<div class="card">
  <form class="new-form" method="post" action="/uitgiften">
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Barcode (scan, typ of zoek op naam)</label>
        <input type="text" name="barcode" id="barcode-input" list="voorraad-items-list" value="<?= htmlspecialchars($barcode) ?>" autocomplete="off" autofocus required placeholder="Scan de barcode of zoek op type/naam...">
        <datalist id="voorraad-items-list"></datalist>
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Naam medewerker</label>
        <input type="text" name="medewerker_naam" id="medewerker-naam-input" list="medewerker-namen-list" autocomplete="off" required>
        <datalist id="medewerker-namen-list"></datalist>
      </div>
      <div class="form-group">
        <label class="form-label">Datum</label>
        <input type="date" name="uitgegeven_op" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="form-group">
        <label class="form-label"><input type="checkbox" name="toestemming_manager" value="1"> Toestemming manager</label>
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Opmerking</label>
        <textarea name="opmerking"></textarea>
      </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Toewijzen</button>
      <a class="btn" href="/uitgiften">Annuleren</a>
    </div>
  </form>
</div>

<script>
(function () {
    var input = document.getElementById('medewerker-naam-input');
    var list = document.getElementById('medewerker-namen-list');
    var timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) {
            list.innerHTML = '';
            return;
        }
        timer = setTimeout(function () {
            fetch('/uitgiften/namen?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (namen) {
                    list.innerHTML = '';
                    namen.forEach(function (naam) {
                        var opt = document.createElement('option');
                        opt.value = naam;
                        list.appendChild(opt);
                    });
                });
        }, 200);
    });
})();

(function () {
    var input = document.getElementById('barcode-input');
    var list = document.getElementById('voorraad-items-list');
    var timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) {
            list.innerHTML = '';
            return;
        }
        timer = setTimeout(function () {
            fetch('/uitgiften/items?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (items) {
                    list.innerHTML = '';
                    items.forEach(function (item) {
                        var opt = document.createElement('option');
                        opt.value = item.barcode;
                        opt.label = item.label;
                        opt.textContent = item.label;
                        list.appendChild(opt);
                    });
                });
        }, 200);
    });
})();
</script>
