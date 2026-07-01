<?php
/** @var array $bezoeken */
/** @var array $pagination */
/** @var array $gebruikers */
/** @var array $ipAdressen */
/** @var array $filters */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">Paginabezoeken</div>
</div>

<form method="get" action="/beheer/log" class="filters" style="margin-bottom:14px">
  <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Zoeken op URL...">
  <select name="user_id" onchange="this.form.submit()">
    <option value="">Alle gebruikers</option>
    <?php foreach ($gebruikers as $g): ?>
      <option value="<?= $g['id'] ?>" <?= (string) $filters['user_id'] === (string) $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['naam']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="ip_adres" onchange="this.form.submit()">
    <option value="">Alle IP-adressen</option>
    <?php foreach ($ipAdressen as $ip): ?>
      <option value="<?= htmlspecialchars($ip) ?>" <?= $filters['ip_adres'] === $ip ? 'selected' : '' ?>><?= htmlspecialchars($ip) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-primary" type="submit">Zoeken</button>
</form>

<div class="card">
  <?php if (empty($bezoeken)): ?>
    <div class="empty-state">Geen paginabezoeken gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th class="col-2">Datum/tijd</th>
        <th>Gebruiker</th>
        <th class="col-2">IP-adres</th>
        <th class="col-2">Methode</th>
        <th>URL</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bezoeken as $b): ?>
        <tr>
          <td><?= formatDatumTijd($b['created_at']) ?></td>
          <td><?= htmlspecialchars($b['gebruiker_naam'] ?? '— (niet ingelogd)') ?></td>
          <td><?= htmlspecialchars($b['ip_adres']) ?></td>
          <td><?= htmlspecialchars($b['methode']) ?></td>
          <td><span class="text-truncate d-block" title="<?= htmlspecialchars($b['url']) ?>"><?= htmlspecialchars($b['url']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?= paginationLinks($pagination) ?>
  <?php endif; ?>
</div>
