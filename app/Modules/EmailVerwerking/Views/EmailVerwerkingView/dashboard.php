<?php
/** @var int $nieuweVandaag */
/** @var int $aantalGeanalyseerd */
/** @var int $conceptenKlaar */
/** @var int $wachtOpReview */
/** @var float $gemiddeldeConfidence */
/** @var int $aantalMislukt */
/** @var float $confidenceDrempel */
/** @var array $recenteEmails */
/** @var array $recenteFouten */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div class="page-title">E-mail &amp; kennisbank verwerking</div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/email-verwerking/inbox">Inbox verwerking</a>
    <a class="btn" href="/email-verwerking/review">Artikelen reviewen</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:16px">
  <div class="card" style="margin-bottom:0;padding:14px">
    <div style="font-size:22px;font-weight:700"><?= (int) $nieuweVandaag ?></div>
    <div style="font-size:12.5px;color:var(--color-text-secondary)">Nieuwe e-mails vandaag</div>
  </div>
  <div class="card" style="margin-bottom:0;padding:14px">
    <div style="font-size:22px;font-weight:700"><?= (int) $aantalGeanalyseerd ?></div>
    <div style="font-size:12.5px;color:var(--color-text-secondary)">Geanalyseerde vragen</div>
  </div>
  <div class="card" style="margin-bottom:0;padding:14px">
    <div style="font-size:22px;font-weight:700"><?= (int) $conceptenKlaar ?></div>
    <div style="font-size:12.5px;color:var(--color-text-secondary)">Conceptartikelen klaar</div>
  </div>
  <div class="card" style="margin-bottom:0;padding:14px">
    <div style="font-size:22px;font-weight:700"><?= (int) $wachtOpReview ?></div>
    <div style="font-size:12.5px;color:var(--color-text-secondary)">Wachten op review</div>
  </div>
  <div class="card" style="margin-bottom:0;padding:14px">
    <div style="font-size:22px;font-weight:700"><?= number_format($gemiddeldeConfidence * 100, 0) ?>%</div>
    <div style="font-size:12.5px;color:var(--color-text-secondary)">
      Gem. AI-confidence <span style="color:var(--color-text-tertiary)">(drempel <?= number_format($confidenceDrempel * 100, 0) ?>%)</span>
    </div>
  </div>
  <div class="card" style="margin-bottom:0;padding:14px">
    <div style="font-size:22px;font-weight:700"><?= (int) $aantalMislukt ?></div>
    <div style="font-size:12.5px;color:var(--color-text-secondary)">Mislukte verwerkingen</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">Recente e-mails</span>
    <a href="/email-verwerking/logboek" style="font-size:12.5px">Volledig logboek &rarr;</a>
  </div>
  <?php if (empty($recenteEmails)): ?>
    <div class="empty-state">Nog geen e-mails binnengehaald.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Datum</th><th>Afzender</th><th>Onderwerp</th><th>AI-categorie</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recenteEmails as $email): ?>
          <tr onclick="window.location='/email-verwerking/<?= $email['id'] ?>'" style="cursor:pointer">
            <td><?= formatDatumTijd($email['created_at']) ?></td>
            <td><?= htmlspecialchars($email['afzender_naam'] ?: $email['afzender_email']) ?></td>
            <td><?= htmlspecialchars(truncateWoorden($email['onderwerp'], 10)) ?></td>
            <td><?= htmlspecialchars($email['ai_categorie'] ?? '—') ?></td>
            <td><?= statusBadge($email['status']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Foutmeldingen</span></div>
  <?php if (empty($recenteFouten)): ?>
    <div class="empty-state">Geen recente fouten.</div>
  <?php else: ?>
    <?php foreach ($recenteFouten as $fout): ?>
      <div class="log-item">
        <div class="log-meta">
          <span class="log-user"><?= htmlspecialchars($fout['onderwerp'] ?? 'Onbekende e-mail') ?></span>
          <span class="log-time"><?= formatDatumTijd($fout['created_at']) ?></span>
        </div>
        <div style="font-size:13px;color:var(--color-text-secondary)"><?= htmlspecialchars($fout['bericht']) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
