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
  <div class="page-title">Uitgifte</div>
  <a class="btn btn-primary" href="/uitgiften/create">+ Item toewijzen</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/uitgiften" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op medewerker...">
  <?= filterSelect('status', 'Alle statussen', $filterOptions['status']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('uitgiften') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen uitgiften gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $u) => "/uitgiften/{$u['id']}")
      ->column('id', '#', fn (array $u) => '#' . $u['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('medewerker_naam', 'Medewerker', fn (array $u) => htmlspecialchars($u['medewerker_naam']))
      ->column('type_naam', 'Item', fn (array $u) => htmlspecialchars($u['type_naam'] ?? '—') . ($u['variant'] ? ' (' . htmlspecialchars($u['variant']) . ')' : ''), ['class' => 'col-2'])
      ->column('barcode', 'Barcode', fn (array $u) => htmlspecialchars($u['barcode'] ?? '—'), ['class' => 'col-2'])
      ->column('uitgegeven_op', 'Uitgegeven op', fn (array $u) => formatDatum($u['uitgegeven_op']), ['class' => 'col-2'])
      ->column('status', 'Status', fn (array $u) => '<span class="badge badge-' . ($u['status'] === 'uitgegeven' ? 'in_behandeling' : 'opgelost') . '">' . ($u['status'] === 'uitgegeven' ? 'Uitgegeven' : 'Geretourneerd') . '</span>', ['class' => 'col-2'])
      ->column('acties', '', function (array $u): string {
          if ($u['status'] !== 'uitgegeven') {
              return '';
          }
          return '<form method="post" action="/uitgiften/' . $u['id'] . '/retour" onsubmit="return retourPrompt(this)">'
              . '<input type="hidden" name="opmerking" value="">'
              . '<button class="btn" type="submit" style="font-size:12px">Retour</button></form>';
      }, ['class' => 'col-1', 'sortable' => false])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>

<script>
function retourPrompt(form) {
    var opmerking = window.prompt('Opmerking over de staat van het item (optioneel, leeg = geen opmerking):', '');
    if (opmerking === null) {
        return false;
    }
    form.querySelector('input[name=opmerking]').value = opmerking;
    return confirm('Retour nemen?');
}
</script>
