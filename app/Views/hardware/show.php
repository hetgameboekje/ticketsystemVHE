<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/hardware-uitgaven" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['omschrijving']) ?></div>
    <?= statusBadge($item['status']) ?>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/hardware-uitgaven/<?= $item['id'] ?>/edit">Bewerken</a>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Details</span></div>
  <div style="padding:0 16px">
    <div class="meta-row"><span class="meta-key">Leverancier</span><span><?= htmlspecialchars($item['leverancier'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Bedrag</span><span>&euro; <?= number_format((float) $item['bedrag'], 2, ',', '.') ?></span></div>
    <div class="meta-row"><span class="meta-key">Aankoopdatum</span><span><?= formatDatum($item['aankoopdatum']) ?></span></div>
    <div class="meta-row"><span class="meta-key">Afdeling</span><span><?= htmlspecialchars($item['afdeling_naam'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Aangevraagd door</span><span><?= htmlspecialchars($item['aangevraagd_door_naam'] ?? '—') ?></span></div>
  </div>
</div>
