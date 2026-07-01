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
  <div class="page-title">Printers</div>
  <a class="btn btn-primary" href="/printers/create">+ Nieuwe printer</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/printers" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op naam...">
  <?= filterSelect('computer_naam', 'Alle servers', $filterOptions['computer_naam']) ?>
  <?= filterSelect('type', 'Alle typen', $filterOptions['type']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('printers') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen printers gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th><?= sortLink('naam', 'Naam', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('computer_naam', 'Server', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('driver_naam', 'Driver', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('ip_adres', 'IP / poort', $sort, $dir) ?></th>
      <th class="col-2"></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $p): ?>
      <tr onclick="window.location='/printers/<?= $p['id'] ?>'">
        <td><span class="text-truncate d-block" title="<?= htmlspecialchars($p['naam']) ?>"><?= htmlspecialchars($p['naam']) ?></span></td>
        <td><?= htmlspecialchars($p['computer_naam'] ?? '—') ?></td>
        <td><span class="text-truncate d-block" title="<?= htmlspecialchars($p['driver_naam'] ?? '') ?>"><?= htmlspecialchars($p['driver_naam'] ?? '—') ?></span></td>
        <td><?= htmlspecialchars($p['ip_adres'] ?? '—') ?></td>
        <td onclick="event.stopPropagation()" title="Kopieer shell commando">
          <button
            type="button"
            class="btn js-copy-btn"
            style="font-size:12px"
            data-command="<?= htmlspecialchars(\App\Modules\Printer\Models\PrinterModel::buildInstallCommand($p)) ?>"
            title="Kopieer shell commando"
          >
            <i class="bi bi-copy"></i>
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
