<?php
/** @var array $gebruiker */
/** @var array $modules */
/** @var array $rechten */
/** @var bool $magVerwijderen */
/** @var string|null $succes */
/** @var string|null $fout */

use App\Core\Table;

$rows = [];
foreach ($modules as $module => $label) {
    $r = $rechten[$module] ?? ['mag_lezen' => 0, 'mag_schrijven' => 0, 'mag_verwijderen' => 0];
    $rows[] = [
        'module' => $module,
        'label' => $label,
        'mag_lezen' => $r['mag_lezen'],
        'mag_schrijven' => $r['mag_schrijven'],
        'mag_verwijderen' => $r['mag_verwijderen'],
    ];
}

$checkbox = fn (string $module, string $veld, mixed $checked): string => '<input type="checkbox" style="width:auto" name="rechten[' . htmlspecialchars($module) . '][' . $veld . ']" value="1" ' . ($checked ? 'checked' : '') . '>';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/beheer/rechten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Rechten — <?= htmlspecialchars($gebruiker['naam']) ?></div>
  </div>
</div>

<?php if ($succes): ?>
  <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>
<?php if ($fout): ?>
  <div class="alert alert-error"><?= htmlspecialchars($fout) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
  <form method="post" action="/beheer/rechten/<?= $gebruiker['id'] ?>/gebruiker" style="padding:16px">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Naam</label>
        <input type="text" name="naam" value="<?= htmlspecialchars($gebruiker['naam']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">E-mailadres</label>
        <input type="email" name="email" value="<?= htmlspecialchars($gebruiker['email']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Rol</label>
        <select name="rol">
          <option value="medewerker" <?= $gebruiker['rol'] === 'medewerker' ? 'selected' : '' ?>>Medewerker</option>
          <option value="admin" <?= $gebruiker['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
      </div>
    </div>
    <div style="margin-top:16px">
      <button class="btn btn-primary" type="submit">Gegevens opslaan</button>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:16px">
  <form method="post" action="/beheer/rechten/<?= $gebruiker['id'] ?>/wachtwoord" style="padding:16px">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Nieuw wachtwoord</label>
        <input type="password" name="wachtwoord" required minlength="8">
      </div>
      <div class="form-group">
        <label class="form-label">Bevestig wachtwoord</label>
        <input type="password" name="wachtwoord_bevestiging" required minlength="8">
      </div>
    </div>
    <div style="margin-top:16px">
      <button class="btn btn-primary" type="submit">Wachtwoord wijzigen</button>
    </div>
  </form>
</div>

<?php if ($gebruiker['rol'] === 'admin'): ?>
  <div class="card" style="margin-bottom:16px;padding:16px">
    <i class="bi bi-unlock"></i> Admin heeft altijd volledige toegang tot alle modules.
  </div>
<?php else: ?>
  <div class="card" style="margin-bottom:16px">
    <form method="post" action="/beheer/rechten/<?= $gebruiker['id'] ?>" style="padding:16px">
      <?php
      $table = (new Table())
          ->column('label', 'Module', fn (array $r) => htmlspecialchars($r['label']), ['sortable' => false])
          ->column('lezen', 'Lezen', fn (array $r) => $checkbox($r['module'], 'lezen', $r['mag_lezen']), ['class' => 'col-2', 'sortable' => false])
          ->column('schrijven', 'Schrijven', fn (array $r) => $checkbox($r['module'], 'schrijven', $r['mag_schrijven']), ['class' => 'col-2', 'sortable' => false])
          ->column('verwijderen', 'Verwijderen', fn (array $r) => $checkbox($r['module'], 'verwijderen', $r['mag_verwijderen']), ['class' => 'col-2', 'sortable' => false])
          ->rows($rows);
      echo $table->render();
      ?>
      <div style="display:flex;gap:8px;margin-top:16px">
        <button class="btn btn-primary" type="submit">Rechten opslaan</button>
        <a class="btn" href="/beheer/rechten">Annuleren</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php if ($magVerwijderen): ?>
  <div class="card" style="padding:16px;display:flex;align-items:center;justify-content:space-between;gap:12px">
    <div>
      <div style="font-weight:600">Gebruiker verwijderen</div>
      <div class="text-body-secondary" style="font-size:13px">Dit verwijdert <?= htmlspecialchars($gebruiker['naam']) ?> definitief. Deze actie kan niet ongedaan worden gemaakt.</div>
    </div>
    <form method="post" action="/beheer/rechten/<?= $gebruiker['id'] ?>/verwijderen" onsubmit="return confirm('Gebruiker <?= htmlspecialchars(addslashes($gebruiker['naam']), ENT_QUOTES) ?> definitief verwijderen?')">
      <button class="btn btn-danger" type="submit">Verwijderen</button>
    </form>
  </div>
<?php endif; ?>
