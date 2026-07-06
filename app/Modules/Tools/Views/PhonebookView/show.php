<?php /** @var array $job */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/tools/telefoonlijst" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($job['original_filename']) ?></div>
  </div>
</div>

<div class="card">
  <div style="padding:16px">
    <?php if ($job['status'] === 'done'): ?>
      <div class="alert alert-success" style="margin-bottom:16px">
        Verwerkt: <?= (int) $job['contact_count'] ?> contact(en) gevonden.
      </div>
      <a class="btn btn-primary" href="/tools/telefoonlijst/<?= (int) $job['id'] ?>/download">Download .vcf</a>
    <?php elseif ($job['status'] === 'error'): ?>
      <div class="alert alert-error">
        Verwerken is mislukt: <?= htmlspecialchars($job['error_message'] ?? 'Onbekende fout') ?>
      </div>
    <?php else: ?>
      <div class="alert alert-success">Wordt verwerkt…</div>
    <?php endif; ?>
  </div>
</div>
