<?php
/** @var array $items */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Alle tickets</div>
  <a class="btn btn-primary" href="/tickets/create">+ Nieuw ticket</a>
</div>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Nog geen tickets aangemaakt.</div>
  <?php else: ?>
  <table>
    <thead><tr>
      <th style="width:60px">#</th><th>Taak</th><th style="width:110px">Opdrachtgever</th>
      <th style="width:90px">Afdeling</th><th style="width:90px">Prioriteit</th>
      <th style="width:130px">Status</th><th style="width:110px">Behandelaar</th><th style="width:100px">Deadline</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $t): ?>
      <tr onclick="window.location='/tickets/<?= $t['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $t['id'] ?></td>
        <td><?= htmlspecialchars($t['titel']) ?></td>
        <td><?= htmlspecialchars($t['opdrachtgever_naam']) ?></td>
        <td><?= htmlspecialchars($t['afdeling_naam'] ?? '—') ?></td>
        <td><?= prioBadge($t['prioriteit']) ?></td>
        <td><?= statusBadge($t['status']) ?></td>
        <td><?= htmlspecialchars($t['behandelaar_naam'] ?? '—') ?></td>
        <td><?= formatDatum($t['deadline']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
