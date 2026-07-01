<?php
/** @var array $items */
/** @var array $pagination */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="page-header">
  <div class="page-title">Uitgaven hardware</div>
  <a class="btn btn-primary" href="/hardware-uitgaven/create">+ Nieuwe uitgave</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/hardware-uitgaven" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op omschrijving...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('hardware-uitgaven') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen hardware-uitgaven gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th><?= sortLink('omschrijving', 'Omschrijving', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('leverancier', 'Leverancier', $sort, $dir) ?></th>
      <th class="col-1"><?= sortLink('bedrag', 'Bedrag', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('afdeling_naam', 'Afdeling', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('status', 'Status', $sort, $dir) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $h): ?>
      <tr onclick="window.location='/hardware-uitgaven/<?= $h['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $h['id'] ?></td>
        <td><span class="text-truncate d-block" title="<?= htmlspecialchars($h['omschrijving']) ?>"><?= htmlspecialchars($h['omschrijving']) ?></span></td>
        <td><?= htmlspecialchars($h['leverancier'] ?? '—') ?></td>
        <td>&euro; <?= number_format((float) $h['bedrag'], 2, ',', '.') ?></td>
        <td><?= htmlspecialchars($h['afdeling_naam'] ?? '—') ?></td>
        <td><?= statusBadge($h['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
