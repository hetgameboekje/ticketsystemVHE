<?php
/** @var array $items */
/** @var array $pagination */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;
?>
<div class="page-header">
  <div class="page-title">Inbox verwerking</div>
  <a class="btn" href="/email-verwerking">&larr; Dashboard</a>
</div>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen nieuwe e-mails — alles is al geanalyseerd of verwerkt.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $r) => "/email-verwerking/{$r['id']}")
      ->column('created_at', 'Datum', fn (array $r) => formatDatumTijd($r['created_at']), ['class' => 'col-2'])
      ->column('afzender_naam', 'Afzender', fn (array $r) => htmlspecialchars($r['afzender_naam'] ?: $r['afzender_email']))
      ->column('onderwerp', 'Onderwerp', fn (array $r) => htmlspecialchars($r['onderwerp']))
      ->column('status', 'Status', fn (array $r) => statusBadge($r['status']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
