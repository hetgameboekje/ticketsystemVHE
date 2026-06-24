<?php
/** @var array $items */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Verbeterpunten</div>
  <a class="btn btn-primary" href="/verbeterpunten/create">+ Nieuw verbeterpunt</a>
</div>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Nog geen verbeterpunten ingediend.</div>
  <?php else: ?>
  <table>
    <thead><tr><th class="col-1">#</th><th>Titel</th><th class="col-2">Afdeling</th><th class="col-2">Ingediend door</th><th class="col-2">Status</th></tr></thead>
    <tbody>
      <?php foreach ($items as $v): ?>
      <tr onclick="window.location='/verbeterpunten/<?= $v['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $v['id'] ?></td>
        <td><?= htmlspecialchars($v['titel']) ?></td>
        <td><?= htmlspecialchars($v['afdeling_naam'] ?? '—') ?></td>
        <td><?= htmlspecialchars($v['ingediend_door_naam'] ?? '—') ?></td>
        <td><?= statusBadge($v['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
