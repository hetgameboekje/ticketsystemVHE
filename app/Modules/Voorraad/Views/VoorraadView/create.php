<?php /** @var array $types */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/voorraad" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Item toevoegen aan voorraad</div>
  </div>
</div>

<div class="card">
  <form class="new-form" method="post" action="/voorraad">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Type</label>
        <select name="type_id" required>
          <option value="">— Kies type —</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['naam']) ?> (<?= htmlspecialchars($t['code']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Variant (bv. lengte: 3m, 5m)</label>
        <input type="text" name="variant" placeholder="optioneel">
      </div>
      <div class="form-group">
        <label class="form-label">Aantal</label>
        <input type="number" name="aantal" id="aantalInput" value="1" min="1" step="1">
      </div>
      <div class="form-group">
        <label class="form-label">Locatie</label>
        <input type="text" name="locatie" placeholder="bv. Serverroom schap 3">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Serienummer(s)</label>
      <div id="serienummerVelden">
        <input type="text" name="serienummers[]" placeholder="optioneel — laat leeg bij kabels e.d." style="margin-bottom:8px">
      </div>
      <p style="font-size:12px;color:var(--color-text-secondary);margin:4px 0 0">
        Met serienummer(s) krijgt elk item een eigen unieke barcode — vul er dan voor elk van de <span id="aantalLabel">1</span> item(en) één in.
        Laat ze allemaal leeg voor items zonder serienummer (bv. kabels, muizen): die delen dezelfde barcode per type + variant.
      </p>
    </div>

    <div class="form-group">
      <label class="form-label">Opmerking</label>
      <textarea name="opmerking"></textarea>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Toevoegen</button>
      <a class="btn" href="/voorraad">Annuleren</a>
    </div>
  </form>
</div>

<script>
(function () {
    var aantalInput = document.getElementById('aantalInput');
    var container = document.getElementById('serienummerVelden');
    var aantalLabel = document.getElementById('aantalLabel');

    function syncVelden() {
        var aantal = Math.max(1, parseInt(aantalInput.value, 10) || 1);
        aantalLabel.textContent = aantal;

        var inputs = container.querySelectorAll('input');
        while (inputs.length < aantal) {
            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'serienummers[]';
            input.placeholder = 'Serienummer ' + (inputs.length + 1);
            input.style.marginBottom = '8px';
            container.appendChild(input);
            inputs = container.querySelectorAll('input');
        }
        while (inputs.length > aantal) {
            container.removeChild(inputs[inputs.length - 1]);
            inputs = container.querySelectorAll('input');
        }
        inputs.forEach(function (input, i) {
            input.placeholder = aantal > 1 ? 'Serienummer ' + (i + 1) : 'optioneel — laat leeg bij kabels e.d.';
        });
    }

    aantalInput.addEventListener('input', syncVelden);
})();
</script>
