<?php
/** @var array $items */
/** @var array $pagination */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Reflectie</div>
  <a class="btn btn-primary" href="/reflecties/create">+ Nieuwe reflectie</a>
</div>

<form method="get" action="/reflecties" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op titel...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('reflecties') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen reflecties gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th><?= sortLink('titel', 'Titel', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('periode', 'Periode', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('gebruiker_naam', 'Gebruiker', $sort, $dir) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $r): ?>
      <tr onclick="window.location='/reflecties/<?= $r['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $r['id'] ?></td>
        <td><span class="text-truncate d-block" title="<?= htmlspecialchars($r['titel']) ?>"><?= htmlspecialchars($r['titel']) ?></span></td>
        <td><?= htmlspecialchars($r['periode']) ?></td>
        <td><?= htmlspecialchars($r['gebruiker_naam'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
