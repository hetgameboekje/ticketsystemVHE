<?php
/** @var string $titel */
/** @var array $tiles */
?>
<div class="page-header">
  <div class="page-title"><?= htmlspecialchars($titel) ?></div>
</div>

<?php if (empty($tiles)): ?>
  <div class="empty-state">Je hebt nog geen toegang tot een van de <?= htmlspecialchars($titel) ?>-modules.</div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:16px">
    <?php foreach ($tiles as $tile): ?>
      <div class="card">
        <div class="card-header"><span class="card-title"><?= htmlspecialchars($tile['titel']) ?></span></div>
        <div style="padding:16px">
          <p style="font-size:13px;color:var(--color-text-secondary);margin-top:0"><?= htmlspecialchars($tile['omschrijving']) ?></p>
          <a class="btn btn-primary" href="<?= htmlspecialchars($tile['link']) ?>">Openen</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
