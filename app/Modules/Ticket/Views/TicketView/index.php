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
  <?= filterSelect('status', 'Alle statussen (zonder opgelost)', $filterOptions['status']) ?>
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
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen tickets gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th><?= sortLink('titel', 'Taak', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('opdrachtgever_naam', 'Opdrachtgever', $sort, $dir) ?></th>
      <th class="col-1"><?= sortLink('afdeling_naam', 'Afdeling', $sort, $dir) ?></th>
      <th class="col-1"><?= sortLink('prioriteit', 'Prioriteit', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('status', 'Status', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('behandelaar_naam', 'Behandelaar', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('deadline', 'Deadline', $sort, $dir) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $t): ?>
      <tr onclick="window.location='/tickets/<?= $t['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $t['id'] ?></td>
        <td><span class="text-truncate d-block" title="<?= htmlspecialchars($t['titel']) ?>"><?= htmlspecialchars($t['titel']) ?></span></td>
        <td><?= htmlspecialchars($t['opdrachtgever_naam']) ?></td>
        <td><?= htmlspecialchars($t['afdeling_naam'] ?? '—') ?></td>
        <td><?= prioBadge($t['prioriteit']) ?></td>
        <td><?= statusBadge($t['status']) ?></td>
        <td><?= htmlspecialchars($t['behandelaar_naam'] ?? '—') ?></td>
        <td><?= formatDatum($t['deadline']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
