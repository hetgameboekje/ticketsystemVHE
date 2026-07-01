<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/verbeterpunten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">#<?= $item['id'] ?> — <?= htmlspecialchars($item['titel']) ?></div>
    <?= statusBadge($item['status']) ?>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/verbeterpunten/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('verbeterpunten', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Omschrijving</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary)">
        <?= nl2br(htmlspecialchars($item['omschrijving'])) ?>
      </div>
    </div>
  </div>
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Afdeling</span><span><?= htmlspecialchars($item['afdeling_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Ingediend door</span><span><?= htmlspecialchars($item['ingediend_door_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Aangemaakt</span><span><?= formatDatum($item['created_at']) ?></span></div>
      </div>
    </div>
  </div>
</div>
