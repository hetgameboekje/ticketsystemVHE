<?php
/** @var array $user */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/account" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Profiel bewerken</div>
  </div>
</div>

<div class="card">
  <form class="new-form" method="post" action="/account" enctype="multipart/form-data" style="padding:20px">
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1;display:flex;align-items:center;gap:16px">
        <?php if (!empty($user['foto'])): ?>
          <img src="<?= htmlspecialchars($user['foto']) ?>" alt="Profielfoto" style="width:64px;height:64px;border-radius:50%;object-fit:cover">
        <?php else: ?>
          <div class="avatar" style="width:64px;height:64px;font-size:20px">
            <?= htmlspecialchars(mb_strtoupper(mb_substr($user['naam'], 0, 1))) ?>
          </div>
        <?php endif; ?>
        <div style="flex:1">
          <label class="form-label">Profielfoto</label>
          <input type="file" name="foto" accept=".jpg,.jpeg,.png,.gif,.webp">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Naam</label>
        <input type="text" name="naam" required value="<?= htmlspecialchars($user['naam']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">E-mailadres</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Nieuw wachtwoord (optioneel)</label>
        <input type="password" name="wachtwoord" placeholder="Laat leeg om huidige wachtwoord te behouden" autocomplete="new-password">
      </div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="/account">Annuleren</a>
    </div>
  </form>
</div>
