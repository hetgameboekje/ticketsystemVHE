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
  <div class="page-title">Reflectie</div>
  <a class="btn btn-primary" href="/reflecties/create">+ Nieuwe reflectie</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/reflecties" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op titel...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('reflecties') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen reflecties gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $r) => "/reflecties/{$r['id']}")
      ->column('id', '#', fn (array $r) => '#' . $r['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('titel', 'Titel', fn (array $r) => '<span class="text-truncate d-block" title="' . htmlspecialchars($r['titel']) . '">' . htmlspecialchars($r['titel']) . '</span>')
      ->column('periode', 'Periode', fn (array $r) => htmlspecialchars($r['periode']), ['class' => 'col-2'])
      ->column('gebruiker_naam', 'Gebruiker', fn (array $r) => htmlspecialchars($r['gebruiker_naam'] ?? '—'), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
