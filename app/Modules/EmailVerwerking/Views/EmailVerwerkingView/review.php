<?php
/** @var array $items */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>
<div class="page-header">
  <div class="page-title">Artikelen reviewen</div>
  <a class="btn" href="/email-verwerking">&larr; Dashboard</a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen conceptartikelen die op review wachten.')
      ->rowUrl(fn (array $r) => "/email-verwerking/concepten/{$r['id']}")
      ->column('titel', 'Artikel', fn (array $r) => htmlspecialchars($r['titel']))
      ->column('categorie', 'Categorie', fn (array $r) => htmlspecialchars($r['categorie']), ['class' => 'col-2'])
      ->column('aantal_bronnen', 'Bronnen', fn (array $r) => (int) $r['aantal_bronnen'] . ' e-mail(s)', ['class' => 'col-2'])
      ->column('confidence', 'Confidence', fn (array $r) => $r['confidence'] !== null ? number_format(((float) $r['confidence']) * 100, 0) . '%' : '—', ['class' => 'col-2'])
      ->column('status', 'Status', fn (array $r) => statusBadge($r['status']), ['class' => 'col-2'])
      ->column('acties', 'Acties', fn (array $r) => '
          <form method="post" action="/email-verwerking/concepten/' . $r['id'] . '/publiceren" style="display:inline" onsubmit="return confirm(\'Dit artikel publiceren naar de kennisbank?\')">
            <button class="btn btn-primary" type="submit" style="padding:5px 9px;font-size:11.5px">Goedkeuren</button>
          </form>
          <a class="btn" href="/email-verwerking/concepten/' . $r['id'] . '" style="padding:5px 9px;font-size:11.5px">Wijzig</a>
      ', ['class' => 'col-3', 'sortable' => false])
      ->rows($items);
  echo $table->render();
  ?>
</div>
