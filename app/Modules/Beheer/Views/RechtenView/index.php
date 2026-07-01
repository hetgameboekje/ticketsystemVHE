<?php
/** @var array $gebruikers */

use App\Core\Table;
?>
<div class="page-header">
  <div class="page-title">Rechten</div>
</div>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen gebruikers gevonden.')
      ->column('naam', 'Naam', fn (array $g) => htmlspecialchars($g['naam']), ['sortable' => false])
      ->column('email', 'E-mailadres', fn (array $g) => htmlspecialchars($g['email']), ['sortable' => false])
      ->column('rol', 'Rol', fn (array $g) => htmlspecialchars(ucfirst($g['rol'])), ['class' => 'col-2', 'sortable' => false])
      ->column('acties', '', function (array $g): string {
          if ($g['rol'] === 'admin') {
              return '<span style="font-size:12px;color:var(--color-text-secondary)">Admin heeft altijd volledige toegang</span>';
          }
          return '<a class="btn" href="/beheer/rechten/' . $g['id'] . '">Rechten bewerken</a>';
      }, ['class' => 'col-2', 'sortable' => false])
      ->rows($gebruikers);
  echo $table->render();
  ?>
</div>
