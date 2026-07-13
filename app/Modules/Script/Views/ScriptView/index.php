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
  <div class="page-title">Scripts</div>
  <a class="btn btn-primary" href="/scripts/create">+ Nieuw script</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/scripts" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op titel...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('scripts') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen scripts gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $s) => "/scripts/{$s['id']}")
      ->column('id', '#', fn (array $s) => '#' . $s['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('titel', 'Titel', fn (array $s) => '<span class="text-truncate d-block" title="' . htmlspecialchars($s['titel']) . '">' . htmlspecialchars($s['titel']) . '</span>')
      ->column('type', 'Type', fn (array $s) => '<span class="badge">' . htmlspecialchars(ucfirst($s['type'] ?? 'overig')) . '</span>', ['class' => 'col-2'])
      ->column('omschrijving', 'Omschrijving', fn (array $s) => htmlspecialchars($s['omschrijving'] ?? '—'))
      ->column('auteur_naam', 'Auteur', fn (array $s) => htmlspecialchars($s['auteur_naam'] ?? '—'), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
