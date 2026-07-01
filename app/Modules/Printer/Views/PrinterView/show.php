<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$command = \App\Modules\Printer\Models\PrinterModel::buildInstallCommand($item);
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/printers" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['naam']) ?></div>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/printers/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('printers', $item['id']) ?>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Details</span></div>
  <div style="padding:0 16px">
    <div class="meta-row"><span class="meta-key">Server</span><span><?= htmlspecialchars($item['computer_naam'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Type</span><span><?= htmlspecialchars($item['type']) ?></span></div>
    <div class="meta-row"><span class="meta-key">Driver</span><span><?= htmlspecialchars($item['driver_naam'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">IP-adres / poort</span><span><?= htmlspecialchars($item['ip_adres'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Opmerking</span><span><?= htmlspecialchars($item['opmerking'] ?? '—') ?></span></div>
    <div class="meta-row"><span class="meta-key">Toegevoegd door</span><span><?= htmlspecialchars($item['aangemaakt_door_naam'] ?? '—') ?></span></div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Installatiecommando</span></div>
  <div style="padding:16px">
    <code style="display:block;background:var(--color-background-secondary);padding:10px 12px;border-radius:var(--border-radius-md);font-size:12.5px;word-break:break-all;margin-bottom:12px"><?= htmlspecialchars($command) ?></code>
    <button type="button" class="btn btn-primary js-copy-btn" data-command="<?= htmlspecialchars($command) ?>"><i class="bi bi-copy"></i> Kopieer commando</button>
  </div>
</div>
