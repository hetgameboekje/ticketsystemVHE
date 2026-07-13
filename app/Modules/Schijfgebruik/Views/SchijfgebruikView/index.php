<?php
/** @var array $items */
/** @var array $pagination */
/** @var array $filterOptions */
/** @var string $search */
/** @var string $minGebruik */
/** @var bool $alleenWaarschuwingen */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="page-header">
  <div class="page-title">Schijfgebruik</div>
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
    <form method="post" action="/schijfgebruik/import" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <input type="file" name="bestand" accept=".csv" required>
      <button class="btn btn-primary" type="submit">Importeren</button>
      <span style="font-size:12px;color:var(--color-text-secondary)">
        Verwacht een NinjaRMM "Devices"-export (.csv). Vervangt bij elke import de volledige lijst met apparaten en schijven.
      </span>
    </form>
  </div>
</div>

<form method="get" action="/schijfgebruik" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op apparaat of gebruiker...">
  <?= filterSelect('organisatie', 'Alle organisaties', $filterOptions['organisatie']) ?>
  <?= filterSelect('locatie', 'Alle locaties', $filterOptions['locatie']) ?>
  <?= filterSelect('letter', 'Alle schijven', $filterOptions['letter']) ?>
  <input type="number" name="min_gebruik" min="0" max="100" value="<?= htmlspecialchars($minGebruik) ?>" placeholder="Min. gebruik %" style="width:140px">
  <label style="display:flex;align-items:center;gap:6px;font-weight:normal;font-size:13px">
    <input type="checkbox" name="alleen_waarschuwingen" value="1" <?= $alleenWaarschuwingen ? 'checked' : '' ?>>
    Alleen apparaten met waarschuwingen
  </label>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('schijfgebruik') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen apparaten/schijven gevonden. Importeer hierboven een CSV-export.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $r) => "/schijfgebruik/{$r['device_id']}")
      ->column('naam', 'Apparaat', fn (array $r) => htmlspecialchars($r['naam']))
      ->column('laatste_login', 'Gebruiker', fn (array $r) => htmlspecialchars($r['laatste_login'] ?? '—'))
      ->column('organisatie', 'Organisatie', fn (array $r) => htmlspecialchars($r['organisatie'] ?? '—'), ['class' => 'col-2'])
      ->column('locatie', 'Locatie', fn (array $r) => htmlspecialchars($r['locatie'] ?? '—'), ['class' => 'col-2'])
      ->column('letter', 'Schijf', fn (array $r) => htmlspecialchars($r['letter']), ['class' => 'col-1'])
      ->column('capaciteit_label', 'Capaciteit', fn (array $r) => htmlspecialchars($r['capaciteit_label'] ?? '—'), ['class' => 'col-1'])
      ->column('gebruik_percentage', 'Gebruik', function (array $r) {
          $pct = (int) $r['gebruik_percentage'];
          $kleur = $pct >= 90 ? '#dc3545' : ($pct >= 75 ? '#fd7e14' : '#198754');
          return '<span style="font-weight:600;color:' . $kleur . '">' . $pct . '%</span>';
      }, ['class' => 'col-1'])
      ->column('laatst_online', 'Laatst online', fn (array $r) => formatDatumTijd($r['laatst_online']), ['class' => 'col-2'])
      ->column('laatste_boot', 'Laatste boot', fn (array $r) => formatDatumTijd($r['laatste_boot']), ['class' => 'col-2'])
      ->column('is_online', 'Status', function (array $r) {
          $badge = $r['is_online']
              ? '<span class="badge" style="background:#198754;color:#fff">Online</span>'
              : '<span class="badge" style="background:#dc3545;color:#fff">Offline' . ($r['dagen_offline'] !== null ? ' (' . $r['dagen_offline'] . 'd)' : '') . '</span>';

          $iconen = '';
          if ($r['herstart_nodig']) {
              $iconen .= ' <i class="bi bi-arrow-clockwise" style="color:#fd7e14" title="Herstart aanbevolen"></i>';
          }
          if (!empty($r['waarschuwingen'])) {
              $iconen .= ' <i class="bi bi-exclamation-triangle-fill" style="color:#dc3545" title="' . htmlspecialchars(implode(' — ', $r['waarschuwingen'])) . '"></i>';
          }

          return $badge . $iconen;
      }, ['class' => 'col-2', 'sortable' => false])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
