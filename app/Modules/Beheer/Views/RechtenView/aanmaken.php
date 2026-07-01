<?php
/** @var array $oud */
/** @var string|null $fout */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/beheer/rechten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuwe gebruiker</div>
  </div>
</div>

<?php if ($fout): ?>
  <div class="alert alert-error"><?= htmlspecialchars($fout) ?></div>
<?php endif; ?>

<div class="card">
  <form class="new-form" method="post" action="/beheer/rechten">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Naam</label>
        <input type="text" name="naam" value="<?= htmlspecialchars($oud['naam']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">E-mailadres</label>
        <input type="email" name="email" value="<?= htmlspecialchars($oud['email']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Wachtwoord</label>
        <input type="password" name="wachtwoord" required minlength="8">
      </div>
      <div class="form-group">
        <label class="form-label">Rol</label>
        <select name="rol">
          <option value="medewerker" <?= $oud['rol'] === 'medewerker' ? 'selected' : '' ?>>Medewerker</option>
          <option value="admin" <?= $oud['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Aanmaken</button>
      <a class="btn" href="/beheer/rechten">Annuleren</a>
    </div>
  </form>
</div>
