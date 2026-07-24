<?php
/** @var array $items */
/** @var array $pagination */
/** @var string $search */
/** @var string|null $sort */
/** @var string $dir */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;
?>
<div class="page-header">
  <div class="page-title">E-mail logboek</div>
  <a class="btn" href="/email-verwerking">&larr; Dashboard</a>
</div>

<form method="get" action="/email-verwerking/logboek" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Zoeken op onderwerp...">
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen e-mails gevonden.')
      ->sortState($sort, $dir)
      ->rowUrl(fn (array $r) => "/email-verwerking/{$r['id']}")
      ->column('created_at', 'Datum', fn (array $r) => formatDatumTijd($r['created_at']), ['class' => 'col-2'])
      ->column('afzender_naam', 'Afzender', fn (array $r) => htmlspecialchars($r['afzender_naam'] ?: $r['afzender_email']))
      ->column('onderwerp', 'Onderwerp', fn (array $r) => htmlspecialchars($r['onderwerp']))
      ->column('ai_categorie', 'AI-categorie', fn (array $r) => htmlspecialchars($r['ai_categorie'] ?? '—'), ['class' => 'col-2'])
      ->column('status', 'Status', fn (array $r) => statusBadge($r['status']), ['class' => 'col-2'])
      ->rows($items);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
