<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/reflecties" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['titel']) ?></div>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/reflecties/<?= $item['id'] ?>/edit">Bewerken</a>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Inhoud</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary)">
        <?= nl2br(htmlspecialchars($item['inhoud'])) ?>
      </div>
    </div>
  </div>
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Periode</span><span><?= htmlspecialchars($item['periode']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Gebruiker</span><span><?= htmlspecialchars($item['gebruiker_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Aangemaakt</span><span><?= formatDatum($item['created_at']) ?></span></div>
      </div>
    </div>
  </div>
</div>
