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
  <div class="page-title">Kennisbank</div>
  <a class="btn btn-primary" href="/kennisbank/create">+ Nieuw artikel</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/kennisbank" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op titel...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('kennisbank') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen artikelen gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $k) => "/kennisbank/{$k['id']}")
      ->column('id', '#', fn (array $k) => '#' . $k['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('titel', 'Titel', fn (array $k) => '<span class="text-truncate d-block" title="' . htmlspecialchars($k['titel']) . '">' . htmlspecialchars($k['titel']) . '</span>')
      ->column('categorie', 'Categorie', fn (array $k) => htmlspecialchars($k['categorie']), ['class' => 'col-2'])
      ->column('auteur_naam', 'Auteur', fn (array $k) => htmlspecialchars($k['auteur_naam'] ?? '—'), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
