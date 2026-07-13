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
?>
<div class="page-header">
  <div class="page-title">Applicaties</div>
  <a class="btn btn-primary" href="/apparaten/create">+ Apparaat / CSV importeren</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/apparaten" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op naam...">
  <?= filterSelect('medewerker_naam', 'Alle medewerkers', $filterOptions['medewerker_naam']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('apparaten') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen apparaten gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $i) => "/apparaten/{$i['id']}")
      ->column('naam', 'Naam', fn (array $i) => htmlspecialchars($i['naam']))
      ->column('medewerker_naam', 'Medewerker', fn (array $i) => htmlspecialchars($i['medewerker_naam'] ?? '—'))
      ->column('software_aantal', 'Software', fn (array $i) => (int) $i['software_aantal'], ['class' => 'col-1'])
      ->column('laatst_geimporteerd_op', 'Laatst geïmporteerd', fn (array $i) => formatDatumTijd($i['laatst_geimporteerd_op']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
