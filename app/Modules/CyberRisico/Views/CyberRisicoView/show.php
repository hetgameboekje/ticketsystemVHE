<?php
/** @var array $item */
/** @var array $logs */
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
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Bewijs / notities</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary)">
        <?= nl2br(htmlspecialchars($item['bewijs_notities'])) ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><span class="card-title">Opmerkingen</span></div>

      <div style="padding:16px;border-bottom:0.5px solid var(--color-border-tertiary)">
        <form method="post" action="/cyberrisicos/<?= $item['id'] ?>/log">
          <div class="form-group">
            <label class="form-label">Titel</label>
            <input type="text" name="titel" placeholder="Korte titel voor deze opmerking">
          </div>
          <div class="form-group">
            <label class="form-label">Omschrijving</label>
            <textarea name="omschrijving" placeholder="Opmerking..."></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Opslaan</button>
        </form>
      </div>

      <?php if (empty($logs)): ?>
        <div class="empty-state">Nog geen opmerkingen.</div>
      <?php else: ?>
        <?php foreach ($logs as $log): ?>
        <div class="log-item">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars($log['user_naam'] ?? 'Onbekend') ?></span>
            <span class="log-time"><?= formatDatumTijd($log['created_at']) ?></span>
          </div>
          <div class="log-title" style="font-weight:600;margin-bottom:2px"><?= htmlspecialchars($log['titel']) ?></div>
          <div class="log-text"><?= nl2br(htmlspecialchars($log['omschrijving'])) ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
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
