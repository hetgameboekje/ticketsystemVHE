<?php
/** @var array $item */
/** @var array $software */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/apparaten" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['naam']) ?></div>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/apparaten/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('apparaten', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div class="card">
    <div class="card-header">
      <span class="card-title">Applicaties (<?= count($software) ?>)</span>
    </div>
    <?php if (!empty($software)): ?>
      <div style="padding:12px 16px;border-bottom:0.5px solid var(--color-border-tertiary)">
        <input type="text" id="software-zoek" placeholder="Zoeken op naam of uitgever...">
      </div>
    <?php endif; ?>

    <?php if (empty($software)): ?>
      <div class="empty-state">Nog geen software geïmporteerd.</div>
    <?php else: ?>
      <div class="log-list">
        <?php foreach ($software as $s): ?>
        <div class="log-item software-row" data-zoek="<?= htmlspecialchars(strtolower(($s['naam'] ?? '') . ' ' . ($s['publisher'] ?? ''))) ?>">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars($s['naam']) ?></span>
            <span class="log-time"><?= htmlspecialchars($s['versie'] ?? '—') ?></span>
          </div>
          <div class="log-text"><?= htmlspecialchars($s['publisher'] ?? '—') ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">Details</span></div>
    <div style="padding:0 16px">
      <div class="meta-row">
        <span class="meta-key">Medewerker</span>
        <span><?= !empty($item['medewerker_id']) ? '<a href="/medewerkers/' . $item['medewerker_id'] . '">' . htmlspecialchars($item['medewerker_naam']) . '</a>' : '—' ?></span>
      </div>
      <div class="meta-row"><span class="meta-key">Apparaat-ID</span><span><?= htmlspecialchars($item['extern_apparaat_id'] ?? '—') ?></span></div>
      <div class="meta-row"><span class="meta-key">Laatst geïmporteerd</span><span><?= formatDatumTijd($item['laatst_geimporteerd_op']) ?></span></div>
    </div>
  </div>
</div>

<script>
(function () {
  var zoek = document.getElementById('software-zoek');
  if (!zoek) return;
  zoek.addEventListener('input', function () {
    var q = zoek.value.trim().toLowerCase();
    document.querySelectorAll('.software-row').forEach(function (row) {
      row.style.display = row.dataset.zoek.indexOf(q) === -1 ? 'none' : '';
    });
  });
})();
</script>
