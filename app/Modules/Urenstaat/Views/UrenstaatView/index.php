<?php
/** @var array $items */
/** @var array $pagination */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
/** @var array|null $open */
/** @var array $openDagen */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$duur = function (array $r): string {
    $start = strtotime($r['start_tijd']);
    $eind = $r['eind_tijd'] !== null ? strtotime($r['eind_tijd']) : false;
    if ($start === false || $eind === false || $eind <= $start) {
        return '—';
    }
    $minuten = (int) round(($eind - $start) / 60);
    return sprintf('%dh%02dm', intdiv($minuten, 60), $minuten % 60);
};
?>
<div class="page-header">
  <div class="page-title">Urenstaat</div>
  <div style="display:flex;gap:8px">
    <?php if ($open): ?>
      <form method="post" action="/urenstaat/stoppen">
        <button class="btn btn-primary" type="submit">Dag afsluiten (gestart om <?= substr($open['start_tijd'], 0, 5) ?>)</button>
      </form>
    <?php else: ?>
      <form method="post" action="/urenstaat/starten">
        <button class="btn btn-primary" type="submit">Nu starten</button>
      </form>
    <?php endif; ?>
    <a class="btn" href="/urenstaat/create">+ Tijd registreren</a>
  </div>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<?php if (count($openDagen) > 1): ?>
  <div id="open-dagen-banner" class="alert alert-error" style="cursor:pointer" onclick="document.getElementById('open-dagen-dialog').showModal()">
    Er staan <span id="open-dagen-aantal"><?= count($openDagen) ?></span> dagen open die niet zijn afgesloten. Klik om te bekijken en af te sluiten.
  </div>
<?php endif; ?>

<dialog id="open-dagen-dialog" style="border:none;border-radius:8px;padding:0;max-width:480px;width:90%">
  <div class="card" style="margin:0">
    <div class="card-header">
      <span class="card-title">Openstaande dagen</span>
      <button type="button" class="btn" style="padding:4px 10px" onclick="document.getElementById('open-dagen-dialog').close()">Sluiten</button>
    </div>
    <ul id="open-dagen-lijst" style="list-style:none;margin:0;padding:0">
      <?php foreach ($openDagen as $dag): ?>
        <li class="log-item" data-id="<?= (int) $dag['id'] ?>" style="display:flex;justify-content:space-between;align-items:center;gap:8px">
          <span>
            <?= formatDatum($dag['datum']) ?> — gestart om <?= substr($dag['start_tijd'], 0, 5) ?>
            <?php if (!empty($dag['locatie_naam'])): ?> (<?= htmlspecialchars($dag['locatie_naam']) ?>)<?php endif; ?>
          </span>
          <button type="button" class="btn btn-primary" style="padding:4px 10px" onclick="urenstaatSluitDagAf(<?= (int) $dag['id'] ?>, this)">Nu afsluiten</button>
        </li>
      <?php endforeach; ?>
    </ul>
    <div id="open-dagen-leeg" class="empty-state" style="display:none">Alle dagen zijn afgesloten.</div>
  </div>
</dialog>

<script>
function urenstaatSluitDagAf(id, knop) {
  knop.disabled = true;
  fetch('/urenstaat/' + id + '/stoppen', { method: 'POST' })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.success) {
        knop.disabled = false;
        return;
      }
      var item = document.querySelector('#open-dagen-lijst li[data-id="' + id + '"]');
      if (item) {
        item.remove();
      }
      var resterend = document.querySelectorAll('#open-dagen-lijst li').length;
      var banner = document.getElementById('open-dagen-banner');
      var aantal = document.getElementById('open-dagen-aantal');
      if (aantal) {
        aantal.textContent = resterend;
      }
      if (resterend <= 1 && banner) {
        banner.style.display = 'none';
      }
      if (resterend === 0) {
        document.getElementById('open-dagen-leeg').style.display = '';
      }
    })
    .catch(function () { knop.disabled = false; });
}
</script>

<form method="get" action="/urenstaat" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op omschrijving...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('urenstaat') ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Nog geen tijd geregistreerd.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $r) => "/urenstaat/{$r['id']}")
      ->column('datum', 'Datum', fn (array $r) => formatDatum($r['datum']), ['class' => 'col-2'])
      ->column('start_tijd', 'Tijd', fn (array $r) => substr($r['start_tijd'], 0, 5) . '–' . ($r['eind_tijd'] !== null ? substr($r['eind_tijd'], 0, 5) : 'loopt'), ['class' => 'col-2'])
      ->column('duur', 'Duur', $duur, ['class' => 'col-1', 'sortable' => false])
      ->column('locatie_naam', 'Locatie', fn (array $r) => htmlspecialchars($r['locatie_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('keyuser_naam', 'Keyuser/klant', fn (array $r) => htmlspecialchars($r['keyuser_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('gebruiker_naam', 'Gebruiker', fn (array $r) => htmlspecialchars($r['gebruiker_naam'] ?? '—'), ['class' => 'col-2'])
      ->column('omschrijving', 'Omschrijving', fn (array $r) => htmlspecialchars($r['omschrijving'] ?? '—'))
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
