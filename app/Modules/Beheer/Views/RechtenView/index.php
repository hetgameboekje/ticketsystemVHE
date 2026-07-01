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
        ->column('naam', 'Naam', fn (array $g) => htmlspecialchars($g['naam']), [
            'sortable' => false,
            'class' => 'align-middle'
        ])
        ->column('email', 'E-mailadres', fn (array $g) => htmlspecialchars($g['email']), [
            'sortable' => false,
            'class' => 'align-middle'
        ])
        ->column('rol', 'Rol', fn (array $g) => htmlspecialchars(ucfirst($g['rol'])), [
            'class' => 'col-2 align-middle',
            'sortable' => false
        ])
        ->column('acties', '', function (array $g): string {
            if ($g['rol'] === 'admin') {
                return '
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <i class="bi bi-unlock" title="Admin heeft altijd volledige toegang"></i>
                    </div>
                ';
            }

            return '
                <div class="d-flex justify-content-center align-items-center h-100">
                    <a class="btn btn-sm btn-primary" href="/beheer/rechten/' . (int) $g['id'] . '">
                        Rechten bewerken
                    </a>
                </div>
            ';
        }, [
            'class' => 'col-2 align-middle text-center',
            'sortable' => false
        ])
        ->rows($gebruikers);

    echo $table->render();
    ?>
</div>