<?php
/** @var array $emails */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$statusLabels = [
    'pending' => 'In wachtrij',
    'sent' => 'Verzonden',
    'error' => 'Fout',
    'test' => 'Test',
];
?>
<div class="page-header">
  <div class="page-title">E-mails</div>
  <a class="btn" href="/beheer">&larr; Beheer</a>
</div>

<div class="card">
  <?php if (empty($emails)): ?>
    <div class="empty-state">Nog geen e-mails in de wachtrij geweest.</div>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Aangemaakt</th>
          <th>Verzonden</th>
          <th>Ontvanger</th>
          <th>Onderwerp</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($emails as $mail): ?>
        <tr>
          <td><?= formatDatumTijd($mail['created_at']) ?></td>
          <td><?= $mail['sent_at'] ? formatDatumTijd($mail['sent_at']) : '—' ?></td>
          <td><?= htmlspecialchars($mail['ontvanger']) ?></td>
          <td>
            <?= htmlspecialchars($mail['onderwerp']) ?>
            <?php if ($mail['status'] === 'error' && !empty($mail['foutmelding'])): ?>
              <div style="font-size:11px;color:#b3261e"><?= htmlspecialchars($mail['foutmelding']) ?></div>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-<?= htmlspecialchars($mail['status']) ?>"><?= htmlspecialchars($statusLabels[$mail['status']] ?? $mail['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
