<?php
/** @var array $items */
/** @var array $pagination */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="page-header">
  <div class="page-title">Verbeterpunten</div>
  <a class="btn btn-primary" href="/verbeterpunten/create">+ Nieuw verbeterpunt</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/verbeterpunten" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op titel...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('verbeterpunten') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen verbeterpunten gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th><?= sortLink('titel', 'Titel', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('afdeling_naam', 'Afdeling', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('ingediend_door_naam', 'Ingediend door', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('status', 'Status', $sort, $dir) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $v): ?>
      <tr onclick="window.location='/verbeterpunten/<?= $v['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $v['id'] ?></td>
        <td><span class="text-truncate d-block" title="<?= htmlspecialchars($v['titel']) ?>"><?= htmlspecialchars($v['titel']) ?></span></td>
        <td><?= htmlspecialchars($v['afdeling_naam'] ?? '—') ?></td>
        <td><?= htmlspecialchars($v['ingediend_door_naam'] ?? '—') ?></td>
        <td><?= statusBadge($v['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
