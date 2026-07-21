<?php
/** @var array $items */
/** @var array $pagination */
/** @var array $filterOptions */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$statusBadges = [
    'op_voorraad' => ['open', 'Op voorraad'],
    'uitgegeven' => ['gesloten', 'Uitgegeven'],
    'afgeschreven' => ['keyuser', 'Afgeschreven'],
];
?>
<div class="page-header">
  <div class="page-title">Voorraad</div>
  <a class="btn btn-primary" href="/voorraad/create">+ Item toevoegen</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/voorraad" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op barcode...">
  <?= filterSelect('type_naam', 'Alle typen', $filterOptions['type_naam']) ?>
  <?= filterSelect('status', 'Alle statussen', $filterOptions['status']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('voorraad') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen voorraaditems gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $i) => "/voorraad/{$i['id']}")
      ->column('id', '#', fn (array $i) => '#' . $i['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('barcode', 'Barcode', null, ['class' => 'col-2'])
      ->column('type_naam', 'Type', fn (array $i) => htmlspecialchars($i['type_naam'] ?? '—'))
      ->column('variant', 'Variant', fn (array $i) => htmlspecialchars($i['variant'] ?? '—'), ['class' => 'col-2'])
      ->column('serienummer', 'Serienummer', fn (array $i) => htmlspecialchars($i['serienummer'] ?? '—'), ['class' => 'col-2'])
      ->column('status', 'Status', function (array $i) use ($statusBadges) {
          [$class, $label] = $statusBadges[$i['status']] ?? ['gesloten', $i['status']];
          return '<span class="badge badge-' . $class . '">' . htmlspecialchars($label) . '</span>';
      }, ['class' => 'col-2'])
      ->column('locatie', 'Locatie', fn (array $i) => htmlspecialchars($i['locatie'] ?? '—'), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
