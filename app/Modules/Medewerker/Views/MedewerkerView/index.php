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
  <div class="page-title">Medewerkers</div>
  <a class="btn btn-primary" href="/medewerkers/create">+ Nieuwe medewerker</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:14px">
  <div class="card-header"><span class="card-title">CSV importeren</span></div>
  <div style="padding:16px">
    <form method="post" action="/medewerkers/import" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <input type="file" name="bestand" accept=".csv" required>
      <button class="btn btn-primary" type="submit">Importeren</button>
      <span style="font-size:12px;color:var(--color-text-secondary)">
        Verwacht een gebruikers-export (.csv) met kolommen "User name", "Email", "User access", "Phone number"
        en "Assigned devices". Werkt bestaande medewerkers bij op e-mailadres en koppelt apparaten waar mogelijk.
      </span>
    </form>
  </div>
</div>

<form method="get" action="/medewerkers" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op achternaam...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('medewerkers') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen medewerkers gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $m) => "/medewerkers/{$m['id']}")
      ->column('id', '#', fn (array $m) => '#' . $m['id'], ['class' => 'col-1', 'cellStyle' => 'color:var(--color-text-tertiary)'])
      ->column('achternaam', 'Naam', fn (array $m) => htmlspecialchars($m['achternaam'] . ', ' . $m['voornaam']))
      ->column('functie', 'Functie', fn (array $m) => htmlspecialchars($m['functie'] ?? '—'), ['class' => 'col-3'])
      ->column('afdeling_naam', 'Afdeling', fn (array $m) => htmlspecialchars($m['afdeling_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('email', 'E-mail', fn (array $m) => htmlspecialchars($m['email'] ?? '—'), ['class' => 'col-3'])
      ->column('status', 'Status', fn (array $m) => statusBadge($m['status']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
