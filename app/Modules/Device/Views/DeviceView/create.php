<?php
/** @var array $medewerkers */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/apparaten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Apparaat / CSV importeren</div>
  </div>
</div>

<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<div class="card new-form" style="max-width:520px">
  <form method="post" action="/apparaten" enctype="multipart/form-data">
    <div class="form-group">
      <label class="form-label">Software-inventaris CSV</label>
      <input type="file" name="bestand" accept=".csv" required>
    </div>
    <div class="form-group">
      <label class="form-label">Naam apparaat</label>
      <input type="text" name="naam" placeholder="Bijv. Laptop Timo Bergthaler">
      <div style="font-size:11px;color:var(--color-text-tertiary);margin-top:4px">
        Alleen verplicht als dit apparaat nog niet eerder geïmporteerd is — herkenning gebeurt
        automatisch via het apparaat-ID in de CSV. Bij een herkend apparaat wordt de naam alleen
        overschreven als je hier iets invult.
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Medewerker</label>
      <select name="medewerker_id">
        <option value="">Geen</option>
        <?php foreach ($medewerkers as $m): ?>
          <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['voornaam'] . ' ' . $m['achternaam']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary" type="submit">Importeren</button>
  </form>
</div>
