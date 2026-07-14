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
  <div style="display:flex;gap:8px;align-items:center">
    <a class="btn" href="/apparaten/software">Software-inventaris</a>
    <a href="/apparaten/create" style="font-size:12px;color:var(--color-text-tertiary)">Nieuw apparaat handmatig toevoegen</a>
    <button type="button" class="btn" id="lokaal-import-knop">+ Lokaal</button>
    <button type="button" class="btn btn-primary" id="globaal-import-knop">+ Globaal</button>
  </div>
</div>

<!-- Verborgen forms voor de "+ Globaal"/"+ Lokaal"-knoppen: klikken opent direct de bestandsverkenner
     (input.click()) en zodra er een bestand gekozen is, wordt automatisch geüpload — geen tussenstap
     met een zichtbaar uploadformulier meer nodig. -->
<form method="post" action="/apparaten/software-import" enctype="multipart/form-data" id="globaal-import-form" style="display:none">
  <input type="file" name="bestand" accept=".csv" id="globaal-import-bestand">
</form>
<form method="post" action="/apparaten" enctype="multipart/form-data" id="lokaal-import-form" style="display:none">
  <input type="file" name="bestand" accept=".csv" id="lokaal-import-bestand">
</form>

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

<script>
(function () {
    function koppel(knopId, bestandId, formId) {
        var knop = document.getElementById(knopId);
        var bestand = document.getElementById(bestandId);
        var form = document.getElementById(formId);
        if (!knop || !bestand || !form) return;

        knop.addEventListener('click', function () {
            bestand.click();
        });
        bestand.addEventListener('change', function () {
            if (bestand.files.length > 0) {
                form.submit();
            }
        });
    }

    koppel('globaal-import-knop', 'globaal-import-bestand', 'globaal-import-form');
    koppel('lokaal-import-knop', 'lokaal-import-bestand', 'lokaal-import-form');
})();
</script>
