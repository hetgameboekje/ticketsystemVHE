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
  <div class="page-title">Kennisbank</div>
  <a class="btn btn-primary" href="/kennisbank/create">+ Nieuw artikel</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<form method="get" action="/kennisbank" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op titel...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<?= activeFilterChip('kennisbank') ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="empty-state">Geen artikelen gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr>
      <th class="col-1"><?= sortLink('id', '#', $sort, $dir) ?></th>
      <th><?= sortLink('titel', 'Titel', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('categorie', 'Categorie', $sort, $dir) ?></th>
      <th class="col-2"><?= sortLink('auteur_naam', 'Auteur', $sort, $dir) ?></th>
    </tr></thead>
    <tbody>
      <?php foreach ($items as $k): ?>
      <tr onclick="window.location='/kennisbank/<?= $k['id'] ?>'">
        <td style="color:var(--color-text-tertiary)">#<?= $k['id'] ?></td>
        <td><span class="text-truncate d-block" title="<?= htmlspecialchars($k['titel']) ?>"><?= htmlspecialchars($k['titel']) ?></span></td>
        <td><?= htmlspecialchars($k['categorie']) ?></td>
        <td><?= htmlspecialchars($k['auteur_naam'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
