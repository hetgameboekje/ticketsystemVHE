<?php
/** @var array $item */
/** @var array $logs */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/kennisbank" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['titel']) ?></div>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/kennisbank/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('kennisbank', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Inhoud</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary)">
        <?= nl2br(htmlspecialchars($item['inhoud'])) ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Opmerkingen</span></div>

      <div style="padding:16px;border-bottom:0.5px solid var(--color-border-tertiary)">
        <form method="post" action="/kennisbank/<?= $item['id'] ?>/log">
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
        <div class="meta-row"><span class="meta-key">Categorie</span><span><?= htmlspecialchars($item['categorie']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Auteur</span><span><?= htmlspecialchars($item['auteur_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Aangemaakt</span><span><?= formatDatum($item['created_at']) ?></span></div>
      </div>
    </div>
  </div>
</div>
