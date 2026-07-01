<?php
/** @var array $items */
/** @var array $pagination */
/** @var array $filterOptions */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

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
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen risico's gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th><?= sortLink('titel', 'Titel', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('categorie', 'Type', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('prioriteit', 'Prioriteit', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('status', 'Status', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('locatie', 'Locatie', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('datum_gemeld', 'Gemeld op', $sort, $dir) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $r): ?>
      <tr onclick="window.location='/cyberrisicos/<?= $r['id'] ?>'">
        <td>
          <span class="text-truncate d-block" title="<?= htmlspecialchars($r['titel']) ?>">
            <?= htmlspecialchars($r['titel']) ?>
            <?php if (!empty($r['is_gevoelig'])): ?>
              <span class="badge" style="background:#FBEAEA;color:#b3261e" title="Bevat gevoelige informatie">Gevoelig</span>
            <?php endif; ?>
          </span>
        </td>
        <td><?= htmlspecialchars($filterOptions['categorie'][$r['categorie']] ?? $r['categorie']) ?></td>
        <td><?= prioBadge($r['prioriteit']) ?></td>
        <td><?= statusBadge($r['status']) ?></td>
        <td><?= htmlspecialchars($r['locatie'] ?? '—') ?></td>
        <td><?= formatDatum($r['datum_gemeld']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
