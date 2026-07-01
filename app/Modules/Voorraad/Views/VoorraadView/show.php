<?php
/** @var array $item */
/** @var string $barcodeSvg */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/voorraad" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['type_naam']) ?><?= $item['variant'] ? ' — ' . htmlspecialchars($item['variant']) : '' ?></div>
    <span class="badge badge-<?= $item['status'] === 'op_voorraad' ? 'open' : 'gesloten' ?>"><?= $item['status'] === 'op_voorraad' ? 'Op voorraad' : 'Uitgegeven' ?></span>
  </div>
  <div style="display:flex;gap:8px">
    <?php if ($item['status'] === 'op_voorraad'): ?>
      <a class="btn btn-primary" href="/uitgiften/create?barcode=<?= urlencode($item['barcode']) ?>">Toewijzen aan medewerker</a>
    <?php endif; ?>
    <a class="btn" href="/voorraad/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('voorraad', $item['id']) ?>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Details</span></div>
  <div style="padding:0 16px">
    <div class="meta-row"><span class="meta-key">Barcode</span><span><?= htmlspecialchars($item['barcode']) ?></span></div>
    <div class="meta-row"><span class="meta-key">Type</span><span><?= htmlspecialchars($item['type_naam']) ?></span></div>
    <div class="meta-row"><span class="meta-key">Variant</span><span><?= htmlspecialchars($item['variant'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Serienummer</span><span><?= htmlspecialchars($item['serienummer'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Locatie</span><span><?= htmlspecialchars($item['locatie'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Opmerking</span><span><?= htmlspecialchars($item['opmerking'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Toegevoegd op</span><span><?= htmlspecialchars(substr((string) $item['created_at'], 0, 10)) ?></span></div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Barcode</span></div>
  <div class="barcode-wrap" style="padding:16px;text-align:center">
    <?= $barcodeSvg ?>
    <div style="margin-top:12px">
      <a class="btn" href="/voorraad/<?= $item['id'] ?>/barcode" target="_blank">Printen</a>
    </div>
  </div>
</div>
