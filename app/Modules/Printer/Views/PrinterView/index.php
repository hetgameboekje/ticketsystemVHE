<?php
/** @var array $items */
/** @var array $pagination */
/** @var array $filterOptions */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;
use App\Modules\Printer\Models\PrinterModel;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="page-header">
  <div class="page-title">Printers</div>
  <a class="btn btn-primary" href="/printers/create">+ Nieuwe printer</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/printers" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op naam...">
  <?= filterSelect('computer_naam', 'Alle servers', $filterOptions['computer_naam']) ?>
  <?= filterSelect('type', 'Alle typen', $filterOptions['type']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('printers') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen printers gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $p) => "/printers/{$p['id']}")
      ->column('naam', 'Naam', fn (array $p) => '<span class="text-truncate d-block" title="' . htmlspecialchars($p['naam']) . '">' . htmlspecialchars($p['naam']) . '</span>')
      ->column('computer_naam', 'Server', fn (array $p) => htmlspecialchars($p['computer_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('driver_naam', 'Driver', fn (array $p) => '<span class="text-truncate d-block" title="' . htmlspecialchars($p['driver_naam'] ?? '') . '">' . htmlspecialchars($p['driver_naam'] ?? '—') . '</span>', ['class' => 'col-2'])
      ->column('ip_adres', 'IP / poort', fn (array $p) => htmlspecialchars($p['ip_adres'] ?? '—'), ['class' => 'col-2'])
      ->column('acties', '', fn (array $p) => '<button type="button" class="btn js-copy-btn" style="font-size:12px" '
          . 'data-command="' . htmlspecialchars(PrinterModel::buildInstallCommand($p)) . '" title="Kopieer shell commando">'
          . '<i class="bi bi-copy"></i></button>', ['class' => 'col-2', 'sortable' => false, 'stopPropagation' => true])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
