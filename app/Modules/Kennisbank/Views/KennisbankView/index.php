<?php
/** @var array $items */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Kennisbank</div>
  <a class="btn btn-primary" href="/kennisbank/create">+ Nieuw artikel</a>
</div>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Nog geen artikelen geschreven.</div>
  <?php else: ?>
  <table>
    <thead><tr><th class="col-1">#</th><th>Titel</th><th class="col-2">Categorie</th><th class="col-2">Auteur</th></tr></thead>
    <tbody>
      <?php foreach ($items as $k): ?>
      <tr onclick="window.location='/kennisbank/<?= $k['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $k['id'] ?></td>
        <td><?= htmlspecialchars($k['titel']) ?></td>
        <td><?= htmlspecialchars($k['categorie']) ?></td>
        <td><?= htmlspecialchars($k['auteur_naam'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
