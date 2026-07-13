<?php
/** @var array $device */
/** @var array $health */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/schijfgebruik" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($device['naam']) ?></div>
  </div>
  <div>
    <?php if ($health['is_online']): ?>
      <span class="badge" style="background:#198754;color:#fff">Online</span>
    <?php else: ?>
      <span class="badge" style="background:#dc3545;color:#fff">Offline<?= $health['dagen_offline'] !== null ? ' (' . $health['dagen_offline'] . 'd)' : '' ?></span>
    <?php endif; ?>
    <?php if ($health['herstart_nodig']): ?>
      <i class="bi bi-arrow-clockwise" style="color:#fd7e14;margin-left:6px" title="Herstart aanbevolen"></i>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($health['waarschuwingen'])): ?>
  <div class="alert alert-error" style="margin-bottom:16px">
    <strong>Waarschuwingen:</strong> <?= htmlspecialchars(implode(' — ', $health['waarschuwingen'])) ?>
  </div>
<?php endif; ?>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Schijven (<?= count($device['schijven']) ?>)</span></div>
      <?php if (empty($device['schijven'])): ?>
        <div class="empty-state">Geen schijven gevonden voor dit apparaat.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Letter</th><th>Type</th><th>Capaciteit</th><th>Gebruik</th></tr></thead>
            <tbody>
              <?php foreach ($device['schijven'] as $s): ?>
                <?php $pct = (int) $s['gebruik_percentage']; $kleur = $pct >= 90 ? '#dc3545' : ($pct >= 75 ? '#fd7e14' : '#198754'); ?>
                <tr>
                  <td><?= htmlspecialchars($s['letter']) ?></td>
                  <td><?= htmlspecialchars($s['disk_type'] ?? '—') ?></td>
                  <td><?= htmlspecialchars($s['capaciteit_label'] ?? '—') ?></td>
                  <td><span style="font-weight:600;color:<?= $kleur ?>"><?= $pct ?>%</span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Netwerk</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">IP-adressen</span><span><?= htmlspecialchars($device['ip_adressen'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">MAC-adressen</span><span><?= htmlspecialchars($device['mac_adressen'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Publiek IP</span><span><?= htmlspecialchars($device['publiek_ip'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Domein</span><span><?= htmlspecialchars($device['domein'] ?? '—') ?></span></div>
      </div>
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Organisatie</span><span><?= htmlspecialchars($device['organisatie'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Locatie</span><span><?= htmlspecialchars($device['locatie'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Type</span><span><?= htmlspecialchars($device['type'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Rol</span><span><?= htmlspecialchars($device['rol'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Beleid</span><span><?= htmlspecialchars($device['beleid'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Gebruiker</span><span><?= htmlspecialchars($device['laatste_login'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Tags</span><span><?= htmlspecialchars($device['tags'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Tijdzone</span><span><?= htmlspecialchars($device['tijdzone'] ?? '—') ?></span></div>
      </div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Gezondheid</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Laatst online</span><span><?= formatDatumTijd($device['laatst_online']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Laatste update</span><span><?= formatDatumTijd($device['laatst_update']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Laatste boot</span><span><?= formatDatumTijd($device['laatste_boot']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Garantie tot</span><span><?= formatDatum($device['garantie_tot']) ?></span></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Hardware</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Merk</span><span><?= htmlspecialchars($device['merk'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Model</span><span><?= htmlspecialchars($device['model'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Serienummer</span><span><?= htmlspecialchars($device['serienummer'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Processor</span><span><?= htmlspecialchars($device['processor'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Geheugen</span><span><?= $device['geheugen_gib'] !== null ? htmlspecialchars((string) $device['geheugen_gib']) . ' GiB' : '—' ?></span></div>
        <div class="meta-row"><span class="meta-key">OS</span><span><?= htmlspecialchars($device['os_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">OS-architectuur</span><span><?= htmlspecialchars($device['os_architectuur'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">OS-build</span><span><?= htmlspecialchars($device['os_build'] ?? '—') ?></span></div>
      </div>
    </div>
  </div>
</div>
