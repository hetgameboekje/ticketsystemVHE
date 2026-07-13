<?php
/** @var string $titel */
/** @var array $tiles */
/** @var array|null $groups */

$renderTileGrid = function (array $tiles): void {
    ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:16px">
        <?php foreach ($tiles as $tile): ?>
        <div class="card">
            <div class="card-header"><span class="card-title"><?= htmlspecialchars($tile['titel']) ?></span></div>
            <div style="padding:16px">
                <p style="font-size:13px;color:var(--color-text-secondary);margin-top:0"><?= htmlspecialchars($tile['omschrijving']) ?></p>
                <a class="btn btn-primary" href="<?= htmlspecialchars($tile['link']) ?>">Openen</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
};

$groups = $groups ?? null;
$tiles = $tiles ?? [];
$isLeeg = $groups !== null ? empty($groups) : empty($tiles);
?>
<div class="page-header">
  <div class="page-title"><?= htmlspecialchars($titel) ?></div>
</div>

<?php if ($isLeeg): ?>
  <div class="empty-state">Je hebt nog geen toegang tot een van de <?= htmlspecialchars($titel) ?>-modules.</div>
<?php elseif ($groups !== null): ?>
  <?php foreach ($groups as $groupLabel => $groupTiles): ?>
    <h2 style="font-size:16px;font-weight:600;margin:24px 0 12px;<?= $groupLabel === array_key_first($groups) ? 'margin-top:0' : '' ?>"><?= htmlspecialchars($groupLabel) ?></h2>
    <?php $renderTileGrid($groupTiles); ?>
  <?php endforeach; ?>
<?php else: ?>
  <?php $renderTileGrid($tiles); ?>
<?php endif; ?>
