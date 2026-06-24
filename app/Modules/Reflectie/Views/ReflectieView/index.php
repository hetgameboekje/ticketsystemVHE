<?php
/** @var array $items */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Reflectie</div>
  <a class="btn btn-primary" href="/reflecties/create">+ Nieuwe reflectie</a>
</div>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Nog geen reflecties geschreven.</div>
  <?php else: ?>
  <table>
    <thead><tr><th class="col-1">#</th><th>Titel</th><th class="col-2">Periode</th><th class="col-2">Gebruiker</th></tr></thead>
    <tbody>
      <?php foreach ($items as $r): ?>
      <tr onclick="window.location='/reflecties/<?= $r['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['titel']) ?></td>
        <td><?= htmlspecialchars($r['periode']) ?></td>
        <td><?= htmlspecialchars($r['gebruiker_naam'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
