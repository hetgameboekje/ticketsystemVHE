<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/urenstaat" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= formatDatum($item['datum']) ?> — <?= htmlspecialchars($item['locatie_naam'] ?? '—') ?></div>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/urenstaat/<?= (int) $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('urenstaat', (int) $item['id']) ?>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Details</span></div>
  <div style="padding:0 16px">
    <div class="meta-row"><span class="meta-key">Datum</span><span><?= formatDatum($item['datum']) ?></span></div>
    <div class="meta-row"><span class="meta-key">Tijd</span><span><?= substr($item['start_tijd'], 0, 5) ?>–<?= $item['eind_tijd'] !== null ? substr($item['eind_tijd'], 0, 5) : 'loopt nog' ?></span></div>
    <div class="meta-row"><span class="meta-key">Locatie</span><span><?= htmlspecialchars($item['locatie_naam'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Keyuser/klant</span><span><?= !empty($item['keyuser_id']) ? htmlspecialchars($item['keyuser_naam']) : '—' ?></span></div>
    <div class="meta-row"><span class="meta-key">Gebruiker</span><span><?= htmlspecialchars($item['gebruiker_naam'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Omschrijving</span><span><?= htmlspecialchars($item['omschrijving'] ?? '—') ?></span></div>
  </div>
</div>
