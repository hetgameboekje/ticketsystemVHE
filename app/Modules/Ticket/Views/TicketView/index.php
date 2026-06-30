<?php
/** @var array $items */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Alle tickets</div>
  <a class="btn btn-primary" href="/tickets/create">+ Nieuw ticket</a>
</div>

<?= activeFilterChip('tickets') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Nog geen tickets aangemaakt.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th><?= sortLink('titel', 'Taak', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('opdrachtgever_naam', 'Opdrachtgever', $sort, $dir) ?></th>
      <th class="col-1"><?= sortLink('afdeling_naam', 'Afdeling', $sort, $dir) ?></th>
      <th class="col-1"><?= sortLink('prioriteit', 'Prioriteit', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('status', 'Status', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('behandelaar_naam', 'Behandelaar', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('deadline', 'Deadline', $sort, $dir) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $t): ?>
      <tr onclick="window.location='/tickets/<?= $t['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $t['id'] ?></td>
        <td><span class="text-truncate d-block" title="<?= htmlspecialchars($t['titel']) ?>"><?= htmlspecialchars($t['titel']) ?></span></td>
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
  </div>
  <?php endif; ?>
</div>
