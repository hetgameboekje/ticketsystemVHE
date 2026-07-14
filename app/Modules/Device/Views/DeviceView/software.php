<?php
/** @var array $items */
/** @var array $pagination */
/** @var array $filterOptions */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
/** @var int $softwareTotaalAantal */
/** @var string|null $softwareLaatstGeimporteerd */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/apparaten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Software-inventaris (globaal)</div>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <span style="font-size:12px;color:var(--color-text-tertiary)">Laatst geïmporteerd: <?= $softwareLaatstGeimporteerd ? formatDatumTijd($softwareLaatstGeimporteerd) : '—' ?></span>
    <button type="button" class="btn" id="globaal-import-knop">+ Nieuwe import</button>
    <form method="post" action="/apparaten/software/leegmaken"
          onsubmit="return confirm('Weet je zeker dat je de volledige software-inventaris wilt legen? Dit kan alleen ongedaan gemaakt worden door opnieuw te importeren.')">
      <button class="btn btn-danger" type="submit">Tabel legen</button>
    </form>
  </div>
</div>

<!-- Deze pagina toont uitsluitend gegevens (Read) — de inhoud wordt volledig beheerd via import
     (vervangt alles) of "Tabel legen"; er is bewust geen aanmaak/bewerk/verwijder-actie per rij. -->
<form method="post" action="/apparaten/software-import" enctype="multipart/form-data" id="globaal-import-form" style="display:none">
  <input type="file" name="bestand" accept=".csv" id="globaal-import-bestand">
</form>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/apparaten/software" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op naam of uitgever...">
  <?= filterSelect('platform', 'Alle platforms', $filterOptions['platform']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('apparaten/software') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen software gevonden. Importeer hierboven een CSV-export.')
      ->sortState($sort, $dir)
      ->column('naam', 'Naam', fn (array $s) => htmlspecialchars($s['naam']))
      ->column('publisher', 'Uitgever', fn (array $s) => htmlspecialchars($s['publisher'] ?? '—'))
      ->column('versie', 'Versie', fn (array $s) => htmlspecialchars($s['versie'] ?? '—'), ['class' => 'col-1'])
      ->column('platform', 'Platform', fn (array $s) => htmlspecialchars($s['platform'] ?? '—'), ['class' => 'col-1'])
      ->column('system_component', 'Systeemonderdeel', fn (array $s) => !empty($s['system_component']) ? 'Ja' : 'Nee', ['class' => 'col-1'])
      ->column('aantal_apparaten', 'Apparaten', fn (array $s) => (int) $s['aantal_apparaten'], ['class' => 'col-1'])
      ->column('eerst_gezien', 'Eerst gezien', fn (array $s) => formatDatumTijd($s['eerst_gezien']), ['class' => 'col-2'])
      ->column('laatst_gezien', 'Laatst gezien', fn (array $s) => formatDatumTijd($s['laatst_gezien']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>

<script>
(function () {
    var knop = document.getElementById('globaal-import-knop');
    var bestand = document.getElementById('globaal-import-bestand');
    var form = document.getElementById('globaal-import-form');
    if (!knop || !bestand || !form) return;

    knop.addEventListener('click', function () {
        bestand.click();
    });
    bestand.addEventListener('change', function () {
        if (bestand.files.length > 0) {
            form.submit();
        }
    });
})();
</script>
