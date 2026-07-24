<?php
/** @var array $item */
/** @var array|null $analyse */
/** @var array $bijlagen */
/** @var array $logs */
/** @var array|null $gekoppeldConcept */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/email-verwerking/logboek" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['onderwerp']) ?></div>
  </div>
  <?= statusBadge($item['status']) ?>
</div>

<div class="detail-layout">
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Inhoud</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary);white-space:pre-line"><?= htmlspecialchars($item['body_schoon']) ?></div>
    </div>

    <?php if ($analyse !== null): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">AI-analyse</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Herkend onderwerp</span><span><?= htmlspecialchars($analyse['herkend_onderwerp'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Categorie</span><span><?= htmlspecialchars($analyse['categorie'] ?? '—') ?><?= $analyse['subcategorie'] ? ' / ' . htmlspecialchars($analyse['subcategorie']) : '' ?></span></div>
        <div class="meta-row"><span class="meta-key">Sentiment / urgentie</span><span><?= htmlspecialchars($analyse['sentiment'] ?? '—') ?> &middot; <?= htmlspecialchars(ucfirst($analyse['urgentie'] ?? '—')) ?></span></div>
        <div class="meta-row"><span class="meta-key">Confidence</span><span><?= number_format(((float) $analyse['confidence']) * 100, 0) ?>%</span></div>
      </div>
      <div style="padding:14px 16px;border-top:0.5px solid var(--color-border-tertiary)">
        <div style="font-weight:600;margin-bottom:4px">Samenvatting</div>
        <div style="font-size:13px;color:var(--color-text-secondary);margin-bottom:12px"><?= nl2br(htmlspecialchars($analyse['samenvatting'] ?? '')) ?></div>
        <div style="font-weight:600;margin-bottom:4px">Probleem</div>
        <div style="font-size:13px;color:var(--color-text-secondary);margin-bottom:12px"><?= nl2br(htmlspecialchars($analyse['probleem'] ?? '')) ?></div>
        <div style="font-weight:600;margin-bottom:4px">Oplossing (voorstel)</div>
        <div style="font-size:13px;color:var(--color-text-secondary)"><?= nl2br(htmlspecialchars($analyse['oplossing_suggestie'] ?? '')) ?></div>
      </div>
    </div>
    <?php elseif ($item['status'] === 'failed'): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Verwerking mislukt</span></div>
      <div style="padding:16px;font-size:13px;color:var(--color-text-secondary)"><?= htmlspecialchars($item['laatste_fout'] ?? 'Onbekende fout.') ?></div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><span class="card-title">Verwerkingslog</span></div>
      <?php if (empty($logs)): ?>
        <div class="empty-state">Nog geen logregels.</div>
      <?php else: ?>
        <?php foreach ($logs as $log): ?>
        <div class="log-item">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars(ucfirst($log['stap'])) ?></span>
            <span class="log-time"><?= formatDatumTijd($log['created_at']) ?></span>
          </div>
          <div style="font-size:13px;color:var(--color-text-secondary)"><?= htmlspecialchars($log['bericht'] ?? '') ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Afzender</span><span><?= htmlspecialchars($item['afzender_naam'] ?: '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">E-mailadres</span><span><?= htmlspecialchars($item['afzender_email']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Ontvangen</span><span><?= formatDatumTijd($item['ontvangen_at'] ?? $item['created_at']) ?></span></div>
      </div>
    </div>

    <?php if (!empty($bijlagen)): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Bijlagen</span></div>
      <div style="padding:0 16px">
        <?php foreach ($bijlagen as $bijlage): ?>
          <div class="meta-row"><span><?= htmlspecialchars($bijlage['bestandsnaam']) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($gekoppeldConcept !== null): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Kennisbankconcept</span></div>
      <div style="padding:16px">
        <div style="font-weight:600;margin-bottom:8px"><?= htmlspecialchars($gekoppeldConcept['titel']) ?></div>
        <a class="btn btn-primary" href="/email-verwerking/concepten/<?= $gekoppeldConcept['id'] ?>">Bekijken / bewerken</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
