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
  <div class="page-title">Voorraad</div>
  <a class="btn btn-primary" href="/voorraad/create">+ Item toevoegen</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/voorraad" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op barcode...">
  <?= filterSelect('type_naam', 'Alle typen', $filterOptions['type_naam']) ?>
  <?= filterSelect('status', 'Alle statussen', $filterOptions['status']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('voorraad') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen voorraaditems gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('barcode', 'Barcode', $sort, $dir) ?></th>
      <th><?= sortLink('type_naam', 'Type', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('variant', 'Variant', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('serienummer', 'Serienummer', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('status', 'Status', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('locatie', 'Locatie', $sort, $dir) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $i): ?>
      <tr onclick="window.location='/voorraad/<?= $i['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $i['id'] ?></td>
        <td><?= htmlspecialchars($i['barcode']) ?></td>
        <td><?= htmlspecialchars($i['type_naam'] ?? '—') ?></td>
        <td><?= htmlspecialchars($i['variant'] ?? '—') ?></td>
        <td><?= htmlspecialchars($i['serienummer'] ?? '—') ?></td>
        <td><span class="badge badge-<?= $i['status'] === 'op_voorraad' ? 'open' : 'gesloten' ?>"><?= $i['status'] === 'op_voorraad' ? 'Op voorraad' : 'Uitgegeven' ?></span></td>
        <td><?= htmlspecialchars($i['locatie'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
