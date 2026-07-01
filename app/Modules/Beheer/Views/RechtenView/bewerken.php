<?php
/** @var array $gebruiker */
/** @var array $modules */
/** @var array $rechten */

use App\Core\Table;

$rows = [];
foreach ($modules as $module => $label) {
    $r = $rechten[$module] ?? ['mag_lezen' => 0, 'mag_schrijven' => 0, 'mag_verwijderen' => 0];
    $rows[] = [
        'module' => $module,
        'label' => $label,
        'mag_lezen' => $r['mag_lezen'],
        'mag_schrijven' => $r['mag_schrijven'],
        'mag_verwijderen' => $r['mag_verwijderen'],
    ];
}

$checkbox = fn (string $module, string $veld, mixed $checked): string => '<input type="checkbox" style="width:auto" name="rechten[' . htmlspecialchars($module) . '][' . $veld . ']" value="1" ' . ($checked ? 'checked' : '') . '>';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/beheer/rechten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Rechten — <?= htmlspecialchars($gebruiker['naam']) ?></div>
  </div>
</div>

<div class="card">
  <form method="post" action="/beheer/rechten/<?= $gebruiker['id'] ?>" style="padding:16px">
    <?php
    $table = (new Table())
        ->column('label', 'Module', fn (array $r) => htmlspecialchars($r['label']), ['sortable' => false])
        ->column('lezen', 'Lezen', fn (array $r) => $checkbox($r['module'], 'lezen', $r['mag_lezen']), ['class' => 'col-2', 'sortable' => false])
        ->column('schrijven', 'Schrijven', fn (array $r) => $checkbox($r['module'], 'schrijven', $r['mag_schrijven']), ['class' => 'col-2', 'sortable' => false])
        ->column('verwijderen', 'Verwijderen', fn (array $r) => $checkbox($r['module'], 'verwijderen', $r['mag_verwijderen']), ['class' => 'col-2', 'sortable' => false])
        ->rows($rows);
    echo $table->render();
    ?>
    <div style="display:flex;gap:8px;margin-top:16px">
      <button class="btn btn-primary" type="submit">Rechten opslaan</button>
      <a class="btn" href="/beheer/rechten">Annuleren</a>
    </div>
  </form>
</div>
