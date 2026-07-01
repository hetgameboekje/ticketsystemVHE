<?php
/** @var array $gebruikers */
?>
<div class="page-header">
  <div class="page-title">Rechten</div>
</div>

<div class="card">
  <?php if (empty($gebruikers)): ?>
    <div class="empty-state">Geen gebruikers gevonden.</div>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Naam</th>
        <th>E-mailadres</th>
        <th class="col-2">Rol</th>
        <th class="col-2"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($gebruikers as $g): ?>
        <tr>
          <td><?= htmlspecialchars($g['naam']) ?></td>
          <td><?= htmlspecialchars($g['email']) ?></td>
          <td><?= htmlspecialchars(ucfirst($g['rol'])) ?></td>
          <td>
            <?php if ($g['rol'] === 'admin'): ?>
              <span style="font-size:12px;color:var(--color-text-secondary)">Admin heeft altijd volledige toegang</span>
            <?php else: ?>
              <a class="btn" href="/beheer/rechten/<?= $g['id'] ?>">Rechten bewerken</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>
