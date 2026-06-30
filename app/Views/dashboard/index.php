<?php
/** @var array $stats */
/** @var array $recenteTickets */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Dashboard</div>
  <a class="btn btn-primary" href="/tickets/create">+ Nieuw ticket</a>
</div>

<div class="stats">
  <div class="stat" style="border-top:2px solid var(--color-text-info)"><div class="stat-label">Open tickets</div><div class="stat-val" style="color:var(--color-text-info)"><?= $stats['tickets_open'] ?></div></div>
  <div class="stat" style="border-top:2px solid var(--color-text-warning)"><div class="stat-label">In behandeling</div><div class="stat-val" style="color:var(--color-text-warning)"><?= $stats['tickets_in_behandeling'] ?></div></div>
  <div class="stat" style="border-top:2px solid var(--color-text-success)"><div class="stat-label">Verbeterpunten</div><div class="stat-val"><?= $stats['verbeterpunten'] ?></div></div>
  <div class="stat" style="border-top:2px solid var(--color-border-secondary)"><div class="stat-label">Medewerkers</div><div class="stat-val"><?= $stats['medewerkers'] ?></div></div>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">Recente tickets</span>
    <a class="btn" href="/tickets" style="font-size:12px">Alle tickets &rarr;</a>
  </div>
  <?php if (empty($recenteTickets)): ?>
    <div class="empty-state">Nog geen tickets aangemaakt.</div>
  <?php else: ?>
  <table>
    <thead><tr><th style="width:60px">#</th><th>Taak</th><th style="width:100px">Afdeling</th><th style="width:100px">Prioriteit</th><th style="width:130px">Status</th></tr></thead>
    <tbody>
      <?php foreach ($recenteTickets as $t): ?>
      <tr onclick="window.location='/tickets/<?= $t['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $t['id'] ?></td>
        <td><?= htmlspecialchars($t['titel']) ?></td>
        <td><?= htmlspecialchars($t['afdeling_naam'] ?? '—') ?></td>
        <td><?= prioBadge($t['prioriteit']) ?></td>
        <td><?= statusBadge($t['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
