<?php
/** @var array $gebruiker */
/** @var array $modules */
/** @var array $rechten */
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/beheer/rechten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Rechten — <?= htmlspecialchars($gebruiker['naam']) ?></div>
  </div>
</div>

<div class="card">
  <form method="post" action="/beheer/rechten/<?= $gebruiker['id'] ?>" style="padding:16px">
    <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Module</th>
          <th class="col-2">Lezen</th>
          <th class="col-2">Schrijven</th>
          <th class="col-2">Verwijderen</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $module => $label): ?>
          <?php $r = $rechten[$module] ?? ['mag_lezen' => 0, 'mag_schrijven' => 0, 'mag_verwijderen' => 0]; ?>
          <tr>
            <td><?= htmlspecialchars($label) ?></td>
            <td><input type="checkbox" style="width:auto" name="rechten[<?= htmlspecialchars($module) ?>][lezen]" value="1" <?= $r['mag_lezen'] ? 'checked' : '' ?>></td>
            <td><input type="checkbox" style="width:auto" name="rechten[<?= htmlspecialchars($module) ?>][schrijven]" value="1" <?= $r['mag_schrijven'] ? 'checked' : '' ?>></td>
            <td><input type="checkbox" style="width:auto" name="rechten[<?= htmlspecialchars($module) ?>][verwijderen]" value="1" <?= $r['mag_verwijderen'] ? 'checked' : '' ?>></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <div style="display:flex;gap:8px;margin-top:16px">
      <button class="btn btn-primary" type="submit">Rechten opslaan</button>
      <a class="btn" href="/beheer/rechten">Annuleren</a>
    </div>
  </form>
</div>
