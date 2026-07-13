<?php
/** @var array $item */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/scripts" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['titel']) ?></div>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/scripts/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('scripts', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header">
        <span class="card-title">Script</span>
        <span class="badge" style="margin-left:8px"><?= htmlspecialchars(ucfirst($item['type'] ?? 'overig')) ?></span>
      </div>
      <div style="padding:16px">
        <?php if (!empty($item['omschrijving'])): ?>
          <div style="margin-bottom:8px;color:var(--color-text-secondary)"><?= htmlspecialchars($item['omschrijving']) ?></div>
        <?php endif; ?>
        <div style="position:relative">
          <pre id="scriptInhoud" style="background:var(--color-bg-secondary);border-radius:6px;padding:12px;font-size:12px;overflow-x:auto;margin:0"><?= htmlspecialchars($item['inhoud']) ?></pre>
          <button type="button" class="btn" style="position:absolute;top:8px;right:8px" onclick="navigator.clipboard.writeText(document.getElementById('scriptInhoud').innerText); this.innerText='Gekopieerd!'; setTimeout(() => this.innerText='Kopiëren', 1500)">Kopiëren</button>
        </div>
      </div>
    </div>
  </div>
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Type</span><span><?= htmlspecialchars(ucfirst($item['type'] ?? 'overig')) ?></span></div>
        <div class="meta-row"><span class="meta-key">Auteur</span><span><?= htmlspecialchars($item['auteur_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Aangemaakt</span><span><?= formatDatum($item['created_at']) ?></span></div>
      </div>
    </div>
  </div>
</div>
