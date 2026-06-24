<?php
/** @var array $items */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Uitgaven hardware</div>
  <a class="btn btn-primary" href="/hardware-uitgaven/create">+ Nieuwe uitgave</a>
</div>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Nog geen hardware-uitgaven geregistreerd.</div>
  <?php else: ?>
  <table>
    <thead><tr>
      <th class="col-1">#</th><th>Omschrijving</th><th class="col-2">Leverancier</th>
      <th class="col-1">Bedrag</th><th class="col-2">Afdeling</th><th class="col-2">Status</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $h): ?>
      <tr onclick="window.location='/hardware-uitgaven/<?= $h['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $h['id'] ?></td>
        <td><?= htmlspecialchars($h['omschrijving']) ?></td>
        <td><?= htmlspecialchars($h['leverancier'] ?? '—') ?></td>
        <td>&euro; <?= number_format((float) $h['bedrag'], 2, ',', '.') ?></td>
        <td><?= htmlspecialchars($h['afdeling_naam'] ?? '—') ?></td>
        <td><?= statusBadge($h['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
