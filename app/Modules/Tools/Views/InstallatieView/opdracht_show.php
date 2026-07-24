<?php
/** @var array $opdracht */
/** @var array $items */
/** @var array $profielNamen */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$totaal = count($items);
$afgevinkt = count(array_filter($items, fn (array $i) => (int) $i['afgevinkt'] === 1));
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/tools/installatie" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($opdracht['apparaat_naam'] ?? '—') ?></div>
    <span class="badge badge-<?= $afgevinkt === $totaal && $totaal > 0 ? 'opgelost' : 'in_behandeling' ?>"><?= $afgevinkt ?> / <?= $totaal ?> afgevinkt</span>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/tools/installatie/opdrachten/<?= (int) $opdracht['id'] ?>/print" target="_blank">Printen</a>
    <?= deleteButton('tools/installatie/opdrachten', (int) $opdracht['id']) ?>
  </div>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="card-header"><span class="card-title">Details</span></div>
  <div style="padding:0 16px">
    <div class="meta-row"><span class="meta-key">Apparaat</span><span><?= htmlspecialchars($opdracht['apparaat_naam'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Profielen</span><span><?= $profielNamen === [] ? '—' : htmlspecialchars(implode(', ', $profielNamen)) ?></span></div>
    <div class="meta-row"><span class="meta-key">Opmerking</span><span><?= htmlspecialchars($opdracht['opmerking'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Aangemaakt</span><span><?= formatDatumTijd($opdracht['created_at']) ?></span></div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Checklist</span></div>
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen items in deze checklist.</div>
  <?php else: ?>
    <div style="padding:8px 16px">
      <?php foreach ($items as $item): ?>
        <form method="post" action="/tools/installatie/opdrachten/<?= (int) $opdracht['id'] ?>/items/<?= (int) $item['id'] ?>/toggle"
              style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--color-border-tertiary)">
          <label style="display:flex;align-items:center;gap:8px;font-weight:normal;flex:1;margin:0">
            <input type="checkbox" onchange="this.form.submit()" <?= (int) $item['afgevinkt'] === 1 ? 'checked' : '' ?>>
            <span style="<?= (int) $item['afgevinkt'] === 1 ? 'text-decoration:line-through;color:var(--color-text-tertiary)' : '' ?>"><?= htmlspecialchars($item['naam']) ?></span>
          </label>
          <?php if (!empty($item['afgevinkt_op'])): ?>
            <span style="font-size:11px;color:var(--color-text-tertiary)"><?= formatDatumTijd($item['afgevinkt_op']) ?></span>
          <?php endif; ?>
        </form>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
