<?php /** @var array $profielen */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/tools/installatie" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Checklist toewijzen aan apparaat</div>
  </div>
</div>

<div class="card">
  <form class="new-form" method="post" action="/tools/installatie/opdrachten">
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Apparaatnaam (zoek een bestaand apparaat, of typ een nieuwe naam)</label>
        <input type="text" name="apparaat_naam" id="apparaat-naam-input" list="apparaat-namen-list" autocomplete="off" required placeholder="Bijv. Laptop Timo Bergthaler">
        <datalist id="apparaat-namen-list"></datalist>
        <p style="font-size:12px;color:var(--color-text-secondary);margin:4px 0 0">
          Geen match gevonden? Dan wordt het apparaat automatisch aangemaakt in Apparaten.
        </p>
      </div>

      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Profielen (optioneel, voegen extra items toe aan de hoofdlijst)</label>
        <?php if (empty($profielen)): ?>
          <p style="font-size:13px;color:var(--color-text-secondary)">Nog geen profielen aangemaakt.</p>
        <?php else: ?>
          <?php foreach ($profielen as $profiel): ?>
            <label style="display:flex;align-items:center;gap:6px;font-weight:normal;margin-bottom:4px">
              <input type="checkbox" name="profielen[]" value="<?= (int) $profiel['id'] ?>"> <?= htmlspecialchars($profiel['naam']) ?>
              <span style="color:var(--color-text-tertiary);font-size:12px">(<?= count($profiel['items']) ?> item(s))</span>
            </label>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Opmerking (optioneel)</label>
        <textarea name="opmerking"></textarea>
      </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Checklist aanmaken</button>
      <a class="btn" href="/tools/installatie">Annuleren</a>
    </div>
  </form>
</div>

<script>
(function () {
    var input = document.getElementById('apparaat-naam-input');
    var list = document.getElementById('apparaat-namen-list');
    var timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) {
            list.innerHTML = '';
            return;
        }
        timer = setTimeout(function () {
            fetch('/tools/installatie/apparaten?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (apparaten) {
                    list.innerHTML = '';
                    apparaten.forEach(function (apparaat) {
                        var opt = document.createElement('option');
                        opt.value = apparaat.naam;
                        list.appendChild(opt);
                    });
                });
        }, 200);
    });
})();
</script>
