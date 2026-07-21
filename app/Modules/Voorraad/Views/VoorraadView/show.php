<?php
/** @var array $item */
/** @var string $barcodeSvg */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$statusBadges = [
    'op_voorraad' => ['open', 'Op voorraad'],
    'uitgegeven' => ['gesloten', 'Uitgegeven'],
    'afgeschreven' => ['keyuser', 'Afgeschreven'],
];
[$statusBadgeClass, $statusBadgeLabel] = $statusBadges[$item['status']] ?? ['gesloten', $item['status']];

$specificaties = null;
if (!empty($item['specificaties'])) {
    $specificaties = json_decode($item['specificaties'], true);
}

$systeemLabels = [
    'Machine name' => 'Machine naam',
    'Operating System' => 'Besturingssysteem',
    'System Manufacturer' => 'Fabrikant',
    'System Model' => 'Model',
    'BIOS' => 'BIOS',
    'Processor' => 'Processor',
    'Memory' => 'Geheugen',
    'DirectX Version' => 'DirectX-versie',
];
$videokaartLabels = [
    'Card name' => 'Kaart',
    'Manufacturer' => 'Fabrikant',
    'Chip type' => 'Chip type',
    'Display Memory' => 'Videogeheugen',
    'Dedicated Memory' => 'Dedicated geheugen',
    'Driver Version' => 'Driverversie',
];
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/voorraad" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['type_naam']) ?><?= $item['variant'] ? ' — ' . htmlspecialchars($item['variant']) : '' ?></div>
    <span class="badge badge-<?= $statusBadgeClass ?>"><?= htmlspecialchars($statusBadgeLabel) ?></span>
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

<?php if (!empty($specificaties['systeem']) || !empty($specificaties['videokaarten'])): ?>
<div class="card">
  <div class="card-header"><span class="card-title">Specificaties (DxDiag)</span></div>
  <div style="padding:0 16px">
    <?php foreach ($systeemLabels as $key => $label): ?>
      <?php if (!empty($specificaties['systeem'][$key])): ?>
        <div class="meta-row"><span class="meta-key"><?= htmlspecialchars($label) ?></span><span><?= htmlspecialchars($specificaties['systeem'][$key]) ?></span></div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php foreach ($specificaties['videokaarten'] ?? [] as $kaart): ?>
    <div class="card-header" style="border-top:0.5px solid var(--color-border-tertiary)"><span class="card-title">Videokaart</span></div>
    <div style="padding:0 16px">
      <?php foreach ($videokaartLabels as $key => $label): ?>
        <?php if (!empty($kaart[$key])): ?>
          <div class="meta-row"><span class="meta-key"><?= htmlspecialchars($label) ?></span><span><?= htmlspecialchars($kaart[$key]) ?></span></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header"><span class="card-title">Barcode</span></div>
  <div class="barcode-wrap" style="padding:16px;text-align:center">
    <?= $barcodeSvg ?>
    <div style="margin-top:12px">
      <a class="btn" href="/voorraad/<?= $item['id'] ?>/barcode" target="_blank">Printen</a>
    </div>
  </div>
</div>
