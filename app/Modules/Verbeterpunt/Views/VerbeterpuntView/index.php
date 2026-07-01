<?php
/** @var array $items */
/** @var array $pagination */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;

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
  <?php
  $table = (new Table())
      ->emptyText('Geen verbeterpunten gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $v) => "/verbeterpunten/{$v['id']}")
      ->column('id', '#', fn (array $v) => '#' . $v['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('titel', 'Titel', fn (array $v) => '<span class="text-truncate d-block" title="' . htmlspecialchars($v['titel']) . '">' . htmlspecialchars($v['titel']) . '</span>')
      ->column('afdeling_naam', 'Afdeling', fn (array $v) => htmlspecialchars($v['afdeling_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('ingediend_door_naam', 'Ingediend door', fn (array $v) => htmlspecialchars($v['ingediend_door_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('status', 'Status', fn (array $v) => statusBadge($v['status']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
