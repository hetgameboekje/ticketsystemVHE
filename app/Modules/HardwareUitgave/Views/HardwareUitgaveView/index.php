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
  <div class="page-title">Uitgaven hardware</div>
  <a class="btn btn-primary" href="/hardware-uitgaven/create">+ Nieuwe uitgave</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/hardware-uitgaven" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op omschrijving...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('hardware-uitgaven') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen hardware-uitgaven gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $h) => "/hardware-uitgaven/{$h['id']}")
      ->column('id', '#', fn (array $h) => '#' . $h['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('omschrijving', 'Omschrijving', fn (array $h) => '<span class="text-truncate d-block" title="' . htmlspecialchars($h['omschrijving']) . '">' . htmlspecialchars($h['omschrijving']) . '</span>')
      ->column('leverancier', 'Leverancier', fn (array $h) => htmlspecialchars($h['leverancier'] ?? '—'), ['class' => 'col-2'])
      ->column('bedrag', 'Bedrag', fn (array $h) => '&euro; ' . number_format((float) $h['bedrag'], 2, ',', '.'), ['class' => 'col-1'])
      ->column('afdeling_naam', 'Afdeling', fn (array $h) => htmlspecialchars($h['afdeling_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('status', 'Status', fn (array $h) => statusBadge($h['status']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
