<?php
/** @var array $items */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Medewerkers</div>
  <a class="btn btn-primary" href="/medewerkers/create">+ Nieuwe medewerker</a>
</div>

<?= activeFilterChip('medewerkers') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Nog geen medewerkers toegevoegd.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th><?= sortLink('achternaam', 'Naam', $sort, $dir) ?></th>
      <th class="col-3"><?= sortLink('functie', 'Functie', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('afdeling_naam', 'Afdeling', $sort, $dir) ?></th>
      <th class="col-3"><?= sortLink('email', 'E-mail', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('status', 'Status', $sort, $dir) ?></th>
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
  </div>
  <?php endif; ?>
</div>
