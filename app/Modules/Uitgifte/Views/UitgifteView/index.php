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
  <div class="page-title">Uitgifte</div>
  <a class="btn btn-primary" href="/uitgiften/create">+ Item toewijzen</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/uitgiften" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op medewerker...">
  <?= filterSelect('status', 'Alle statussen', $filterOptions['status']) ?>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('uitgiften') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen uitgiften gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th><?= sortLink('medewerker_naam', 'Medewerker', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('type_naam', 'Item', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('barcode', 'Barcode', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('uitgegeven_op', 'Uitgegeven op', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('status', 'Status', $sort, $dir) ?></th>
      <th class="col-1"></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $u): ?>
      <tr onclick="window.location='/uitgiften/<?= $u['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['medewerker_naam']) ?></td>
        <td><?= htmlspecialchars($u['type_naam'] ?? '—') ?><?= $u['variant'] ? ' (' . htmlspecialchars($u['variant']) . ')' : '' ?></td>
        <td><?= htmlspecialchars($u['barcode'] ?? '—') ?></td>
        <td><?= formatDatum($u['uitgegeven_op']) ?></td>
        <td><span class="badge badge-<?= $u['status'] === 'uitgegeven' ? 'in_behandeling' : 'opgelost' ?>"><?= $u['status'] === 'uitgegeven' ? 'Uitgegeven' : 'Geretourneerd' ?></span></td>
        <td onclick="event.stopPropagation()">
          <?php if ($u['status'] === 'uitgegeven'): ?>
            <form method="post" action="/uitgiften/<?= $u['id'] ?>/retour" onsubmit="return retourPrompt(this)">
              <input type="hidden" name="opmerking" value="">
              <button class="btn" type="submit" style="font-size:12px">Retour</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>

<script>
function retourPrompt(form) {
    var opmerking = window.prompt('Opmerking over de staat van het item (optioneel, leeg = geen opmerking):', '');
    if (opmerking === null) {
        return false;
    }
    form.querySelector('input[name=opmerking]').value = opmerking;
    return confirm('Retour nemen?');
}
</script>
