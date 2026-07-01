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
        <label class="form-label">Serienummer</label>
        <input type="text" name="serienummer" placeholder="optioneel — laat leeg bij kabels e.d.">
      </div>
      <div class="form-group">
        <label class="form-label">Aantal</label>
        <input type="number" name="aantal" value="1" min="1" step="1">
      </div>
      <div class="form-group">
        <label class="form-label">Locatie</label>
        <input type="text" name="locatie" placeholder="bv. Serverroom schap 3">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Opmerking</label>
        <textarea name="opmerking"></textarea>
      </div>
    </div>
    <p style="font-size:12px;color:var(--color-text-secondary);margin-bottom:12px">
      Met een serienummer krijgt het item een eigen unieke barcode en kan je maar 1 stuk tegelijk toevoegen.
      Zonder serienummer (bv. kabels, muizen) krijgen alle items van hetzelfde type + variant dezelfde barcode,
      zodat je die meerdere keren kan printen.
    </p>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Toevoegen</button>
      <a class="btn" href="/voorraad">Annuleren</a>
    </div>
  </form>
</div>
