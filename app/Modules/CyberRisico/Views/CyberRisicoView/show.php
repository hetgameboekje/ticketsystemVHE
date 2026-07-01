<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$categorieLabels = [
    'fysieke_toegang' => 'Fysieke toegang',
    'social_engineering' => 'Social engineering',
    'onveilige_opslag' => 'Onveilige opslag',
    'papieren_informatie' => 'Papieren informatie',
    'device_exposure' => 'Device exposure',
    'overig' => 'Overig',
];
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <a class="btn" href="/cyberrisicos" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['titel']) ?></div>
    <?= statusBadge($item['status']) ?>
    <?= prioBadge($item['prioriteit']) ?>
    <?php if (!empty($item['is_gevoelig'])): ?>
      <span class="badge" style="background:#FBEAEA;color:#b3261e">Bevat gevoelige informatie</span>
    <?php endif; ?>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/cyberrisicos/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('cyberrisicos', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Omschrijving</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary)">
        <?= nl2br(htmlspecialchars($item['omschrijving'])) ?>
      </div>
    </div>

    <?php if (!empty($item['oplossingsadvies'])): ?>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Oplossingsadvies</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary)">
        <?= nl2br(htmlspecialchars($item['oplossingsadvies'])) ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($item['bewijs_notities'])): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Bewijs / notities</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary)">
        <?= nl2br(htmlspecialchars($item['bewijs_notities'])) ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Type risico</span><span><?= htmlspecialchars($categorieLabels[$item['categorie']] ?? $item['categorie']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Locatie</span><span><?= htmlspecialchars($item['locatie'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Gemeld door</span><span><?= htmlspecialchars($item['gemeld_door'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Eigenaar</span><span><?= htmlspecialchars($item['eigenaar_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Datum geconstateerd</span><span><?= formatDatum($item['datum_geconstateerd']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Datum gemeld</span><span><?= formatDatum($item['datum_gemeld']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Aangemaakt</span><span><?= formatDatumTijd($item['created_at']) ?></span></div>
      </div>
    </div>
  </div>
</div>
