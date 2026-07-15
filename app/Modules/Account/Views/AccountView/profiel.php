<?php
/** @var array $user */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Mijn profiel</div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/account/locaties">Mijn locaties</a>
    <a class="btn btn-primary" href="/account/bewerken">Bewerken</a>
  </div>
</div>

<div class="card">
  <div style="padding:20px;display:flex;gap:20px;align-items:center;border-bottom:1px solid var(--color-border-tertiary)">
    <?php if (!empty($user['foto'])): ?>
      <img src="<?= htmlspecialchars($user['foto']) ?>" alt="Profielfoto" style="width:80px;height:80px;border-radius:50%;object-fit:cover">
    <?php else: ?>
      <div class="avatar" style="width:80px;height:80px;font-size:26px">
        <?= htmlspecialchars(mb_strtoupper(mb_substr($user['naam'], 0, 1))) ?>
      </div>
    <?php endif; ?>
    <div>
      <div style="font-size:17px;font-weight:600"><?= htmlspecialchars($user['naam']) ?></div>
      <div style="color:var(--color-text-secondary);font-size:13px"><?= htmlspecialchars($user['email']) ?></div>
    </div>
  </div>
  <div style="padding:0 20px">
    <div class="meta-row"><span class="meta-key">Rol</span><span><?= htmlspecialchars(ucfirst($user['rol'])) ?></span></div>
    <div class="meta-row"><span class="meta-key">Telefoonnummer</span><span><?= htmlspecialchars($user['telefoon'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Adres</span><span><?= htmlspecialchars($user['adres'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Account aangemaakt</span><span><?= formatDatum($user['created_at']) ?></span></div>
  </div>
</div>
