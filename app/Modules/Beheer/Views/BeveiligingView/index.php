<?php
/** @var array $pogingen */
/** @var array $pagination */
/** @var array $gebruikers */
/** @var array $filters */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

use App\Core\Table;
?>
<div class="page-header">
  <div class="page-title">Beveiliging — inlogpogingen</div>
</div>

<form method="get" action="/beheer/beveiliging" class="filters" style="margin-bottom:14px">
  <select name="user_id" onchange="this.form.submit()">
    <option value="">Alle gebruikers</option>
    <?php foreach ($gebruikers as $g): ?>
      <option value="<?= $g['id'] ?>" <?= (string) $filters['user_id'] === (string) $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['naam']) ?></option>
    <?php endforeach; ?>
  </select>
  <label style="display:flex;align-items:center;gap:6px;font-size:13px">
    <input type="checkbox" name="alleen_verdacht" value="1" onchange="this.form.submit()" <?= !empty($filters['alleen_verdacht']) ? 'checked' : '' ?>>
    Alleen mislukt / nieuw IP
  </label>
  <button class="btn btn-primary" type="submit">Filteren</button>
</form>

<div class="card">
  <?php
  $table = (new Table())
      ->emptyText('Geen inlogpogingen gevonden.')
      ->column('created_at', 'Datum/tijd', fn (array $p) => formatDatumTijd($p['created_at']), ['class' => 'col-2', 'sortable' => false])
      ->column('email', 'E-mailadres', fn (array $p) => htmlspecialchars($p['gebruiker_naam'] ?? $p['email']), ['sortable' => false])
      ->column('ip_address', 'IP-adres', fn (array $p) => htmlspecialchars($p['ip_address'] ?? ''), ['class' => 'col-2', 'sortable' => false])
      ->column('success', 'Resultaat', fn (array $p) => $p['success']
          ? '<span class="badge" style="background:#E6F4EA;color:#1e7e34">Geslaagd</span>'
          : '<span class="badge" style="background:#FBEAEA;color:#b3261e">Mislukt</span>', ['class' => 'col-2', 'sortable' => false])
      ->column('is_new_ip', 'Signaal', fn (array $p) => !empty($p['is_new_ip'])
          ? '<span class="badge" style="background:#FFF4E5;color:#b26a00">Nieuw IP voor deze gebruiker</span>'
          : '', ['sortable' => false])
      ->rows($pogingen);
  echo $table->render();
  ?>
  <?= paginationLinks($pagination) ?>
</div>
