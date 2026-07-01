<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/uitgiften" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['type_naam'] ?? 'Item') ?> &rarr; <?= htmlspecialchars($item['medewerker_naam']) ?></div>
    <span class="badge badge-<?= $item['status'] === 'uitgegeven' ? 'in_behandeling' : 'opgelost' ?>"><?= $item['status'] === 'uitgegeven' ? 'Uitgegeven' : 'Geretourneerd' ?></span>
  </div>
</div>

<div class="detail-layout">
  <div class="card">
    <div class="card-header"><span class="card-title">Details</span></div>
    <div style="padding:0 16px">
      <div class="meta-row"><span class="meta-key">Item</span><span><?= htmlspecialchars($item['type_naam'] ?? '—') ?><?= $item['variant'] ? ' (' . htmlspecialchars($item['variant']) . ')' : '' ?></span></div>
      <div class="meta-row"><span class="meta-key">Barcode</span><span><?= htmlspecialchars($item['barcode'] ?? '—') ?></span></div>
      <div class="meta-row"><span class="meta-key">Serienummer</span><span><?= htmlspecialchars($item['serienummer'] ?? '—') ?></span></div>
      <div class="meta-row"><span class="meta-key">Medewerker</span><span><?= htmlspecialchars($item['medewerker_naam']) ?></span></div>
      <div class="meta-row"><span class="meta-key">Uitgegeven op</span><span><?= formatDatum($item['uitgegeven_op']) ?></span></div>
      <div class="meta-row"><span class="meta-key">Teruggegeven op</span><span><?= formatDatum($item['teruggegeven_op']) ?></span></div>
      <div class="meta-row"><span class="meta-key">Opmerking</span><span><?= htmlspecialchars($item['opmerking'] ?? '—') ?></span></div>
      <?php if ($item['status'] !== 'uitgegeven'): ?>
        <div class="meta-row"><span class="meta-key">Retour opmerking</span><span><?= htmlspecialchars($item['retour_opmerking'] ?? '—') ?></span></div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($item['status'] === 'uitgegeven'): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Retour nemen</span></div>
      <form method="post" action="/uitgiften/<?= $item['id'] ?>/retour" style="padding:16px">
        <div class="form-group">
          <label class="form-label">Opmerking over staat (optioneel)</label>
          <textarea name="opmerking" placeholder="Bijv. krasje op deksel, kabel ontbreekt..."></textarea>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Retour nemen</button>
      </form>
    </div>
  <?php endif; ?>
</div>
