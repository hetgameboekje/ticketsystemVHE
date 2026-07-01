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
  <div class="page-title">Cyberrisico's</div>
  <a class="btn btn-primary" href="/cyberrisicos/create">+ Risico melden</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/cyberrisicos" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op titel...">
  <?= filterSelect('categorie', 'Alle typen', $filterOptions['categorie']) ?>
  <?= filterSelect('prioriteit', 'Alle prioriteiten', $filterOptions['prioriteit']) ?>
  <?= filterSelect('status', 'Alle statussen (excl. opgelost/geaccepteerd)', $filterOptions['status']) ?>
  <?= filterSelect('locatie', 'Alle locaties', $filterOptions['locatie']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('cyberrisicos') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText("Geen risico's gevonden.")
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $r) => "/cyberrisicos/{$r['id']}")
      ->column('titel', 'Titel', function (array $r): string {
          $html = '<span class="text-truncate d-block" title="' . htmlspecialchars($r['titel']) . '">' . htmlspecialchars($r['titel']);
          if (!empty($r['is_gevoelig'])) {
              $html .= ' <span class="badge" style="background:#FBEAEA;color:#b3261e" title="Bevat gevoelige informatie">Gevoelig</span>';
          }
          return $html . '</span>';
      })
      ->column('categorie', 'Type', fn (array $r) => htmlspecialchars($filterOptions['categorie'][$r['categorie']] ?? $r['categorie']), ['class' => 'col-2'])
      ->column('prioriteit', 'Prioriteit', fn (array $r) => prioBadge($r['prioriteit']), ['class' => 'col-2'])
      ->column('status', 'Status', fn (array $r) => statusBadge($r['status']), ['class' => 'col-2'])
      ->column('locatie', 'Locatie', fn (array $r) => htmlspecialchars($r['locatie'] ?? '—'), ['class' => 'col-2'])
      ->column('datum_gemeld', 'Gemeld op', fn (array $r) => formatDatum($r['datum_gemeld']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
