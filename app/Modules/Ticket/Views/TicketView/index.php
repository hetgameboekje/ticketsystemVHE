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
  <div class="page-title">Alle tickets</div>
  <a class="btn btn-primary" href="/tickets/create">+ Nieuw ticket</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<div class="filters" style="margin-bottom:14px">
  <a class="btn" href="/tickets/export"><i class="bi bi-download"></i> Exporteren naar Excel</a>

  <button type="button" class="btn" id="import-trigger-btn"><i class="bi bi-upload"></i> Importeren vanuit Excel</button>
  <form method="post" action="/tickets/import" enctype="multipart/form-data" id="import-form">
    <input type="file" name="bestand" accept=".xlsx" id="import-file-input" style="display:none" required>
  </form>
</div>

<script>
(function () {
    var triggerBtn = document.getElementById('import-trigger-btn');
    var fileInput = document.getElementById('import-file-input');
    var form = document.getElementById('import-form');

    triggerBtn.addEventListener('click', function () {
        fileInput.click();
    });
    fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files.length > 0) {
            form.submit();
        }
    });
})();
</script>

<form method="get" action="/tickets" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op taak...">
  <?= filterSelect('status', 'Actieve statussen', $filterOptions['status']) ?>
  <?= filterSelect('prioriteit', 'Alle prioriteiten', $filterOptions['prioriteit']) ?>
  <?= filterSelect('afdeling_naam', 'Alle afdelingen', $filterOptions['afdeling_naam']) ?>
  <?= filterSelect('behandelaar_naam', 'Alle behandelaars', $filterOptions['behandelaar_naam']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
  <?php if ($sort): ?>
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
    <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
  <?php endif; ?>
</form>

<?= activeFilterChip('tickets') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen tickets gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $t) => "/tickets/{$t['id']}")
      ->column('id', '#', fn (array $t) => '#' . $t['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('titel', 'Taak', fn (array $t) => '<span class="text-truncate d-block" title="' . htmlspecialchars($t['titel']) . '">' . htmlspecialchars($t['titel']) . '</span>')
      ->column('opdrachtgever_naam', 'Opdrachtgever', fn (array $t) => htmlspecialchars($t['opdrachtgever_naam']), ['class' => 'col-2'])
      ->column('afdeling_naam', 'Afdeling', fn (array $t) => htmlspecialchars($t['afdeling_naam'] ?? '—'), ['class' => 'col-1'])
      ->column('prioriteit', 'Prioriteit', fn (array $t) => prioBadge($t['prioriteit']), ['class' => 'col-1'])
      ->column('status', 'Status', fn (array $t) => statusBadge($t['status']), ['class' => 'col-2'])
      ->column('behandelaar_naam', 'Behandelaar', fn (array $t) => htmlspecialchars($t['behandelaar_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('deadline', 'Deadline', fn (array $t) => formatDatum($t['deadline']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
