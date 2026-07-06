<?php /** @var array $modules */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/beheer" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Exporteren</div>
  </div>
</div>

<div class="card">
  <form method="post" action="/beheer/exporteren/uitvoeren" style="padding:16px">
    <div class="form-group">
      <label class="form-label">Modules</label>
      <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:8px;margin-top:4px">
        <?php foreach ($modules as $key => $label): ?>
          <label style="display:flex;align-items:center;gap:6px;font-weight:normal">
            <input type="checkbox" name="modules[]" value="<?= htmlspecialchars($key) ?>">
            <?= htmlspecialchars($label) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-group" style="margin-top:16px">
      <label class="form-label">Formaat</label>
      <div style="display:flex;gap:16px">
        <label style="display:flex;align-items:center;gap:6px;font-weight:normal">
          <input type="radio" name="formaat" value="excel" checked>
          Excel (elke module een eigen werkblad)
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-weight:normal">
          <input type="radio" name="formaat" value="csv">
          CSV (los bestand per module, gezipt bij meerdere)
        </label>
      </div>
    </div>

    <button class="btn btn-primary" type="submit" style="margin-top:16px">Exporteren</button>
  </form>
</div>
