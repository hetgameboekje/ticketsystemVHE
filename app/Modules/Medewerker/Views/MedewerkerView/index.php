<?php
/** @var array $items */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Medewerkers</div>
  <a class="btn btn-primary" href="/medewerkers/create">+ Nieuwe medewerker</a>
</div>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Nog geen medewerkers toegevoegd.</div>
  <?php else: ?>
  <table>
    <thead><tr>
      <th class="col-1">#</th><th>Naam</th><th class="col-3">Functie</th>
      <th class="col-2">Afdeling</th><th class="col-3">E-mail</th><th class="col-2">Status</th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $m): ?>
      <tr onclick="window.location='/medewerkers/<?= $m['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $m['id'] ?></td>
        <td><?= htmlspecialchars($m['achternaam'] . ', ' . $m['voornaam']) ?></td>
        <td><?= htmlspecialchars($m['functie'] ?? '—') ?></td>
        <td><?= htmlspecialchars($m['afdeling_naam'] ?? '—') ?></td>
        <td><?= htmlspecialchars($m['email'] ?? '—') ?></td>
        <td><?= statusBadge($m['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
