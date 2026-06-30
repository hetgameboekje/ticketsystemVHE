<?php /** @var array $afdelingen */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/hardware-uitgaven" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuwe hardware-uitgave</div>
  </div>
</div>
<div class="card">
  <form class="new-form" method="post" action="/hardware-uitgaven">
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1"><label class="form-label">Omschrijving</label><input type="text" name="omschrijving" required></div>
      <div class="form-group"><label class="form-label">Leverancier</label><input type="text" name="leverancier"></div>
      <div class="form-group"><label class="form-label">Bedrag (&euro;)</label><input type="number" step="0.01" name="bedrag" required></div>
      <div class="form-group"><label class="form-label">Aankoopdatum</label><input type="date" name="aankoopdatum"></div>
      <div class="form-group">
        <label class="form-label">Afdeling</label>
        <select name="afdeling_id">
          <option value="">— Selecteer afdeling —</option>
          <?php foreach ($afdelingen as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['naam']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/hardware-uitgaven">Annuleren</a>
    </div>
  </form>
</div>
