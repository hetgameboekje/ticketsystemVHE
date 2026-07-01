<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/medewerkers" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['voornaam'] . ' ' . $item['achternaam']) ?></div>
    <?= statusBadge($item['status']) ?>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/medewerkers/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('medewerkers', $item['id']) ?>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Details</span></div>
  <div style="padding:0 16px">
    <div class="meta-row"><span class="meta-key">Functie</span><span><?= htmlspecialchars($item['functie'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Afdeling</span><span><?= htmlspecialchars($item['afdeling_naam'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">E-mail</span><span><?= htmlspecialchars($item['email'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Telefoon</span><span><?= htmlspecialchars($item['telefoon'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Startdatum</span><span><?= formatDatum($item['startdatum']) ?></span></div>
  </div>
</div>
