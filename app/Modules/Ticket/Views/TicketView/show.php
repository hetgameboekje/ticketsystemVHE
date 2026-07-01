<?php
/** @var array $item */
/** @var array $logs */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
$statussen = ['open' => 'Open', 'in_behandeling' => 'In behandeling', 'wacht_op_info' => 'Wacht op info', 'opgelost' => 'Opgelost', 'gesloten' => 'Gesloten'];
$statusLogs = array_values(array_filter($logs, fn ($log) => $log['status_naar'] !== null));
$opmerkingen = array_values(array_filter($logs, fn ($log) => $log['status_naar'] === null));
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/tickets" style="padding:6px 10px">&larr;</a>
    <div class="page-title">#<?= $item['id'] ?> — <?= htmlspecialchars($item['titel']) ?></div>
    <?= statusBadge($item['status']) ?>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/tickets/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('tickets', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Omschrijving</span></div>
      <div style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary)">
        <?= $item['omschrijving'] !== '' ? nl2br(htmlspecialchars($item['omschrijving'])) : '<span style="color:var(--color-text-tertiary)">Geen omschrijving</span>' ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Opmerkingen</span></div>

      <?php if (empty($opmerkingen)): ?>
        <div class="empty-state">Nog geen opmerkingen.</div>
      <?php else: ?>
        <?php foreach ($opmerkingen as $log): ?>
        <div class="log-item">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars($log['user_naam'] ?? 'Onbekend') ?></span>
            <span class="log-time"><?= formatDatumTijd($log['created_at']) ?></span>
          </div>
          <div class="log-text"><?= nl2br(htmlspecialchars($log['opmerking'])) ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <div style="padding:16px;border-top:0.5px solid var(--color-border-tertiary)">
        <form method="post" action="/tickets/<?= $item['id'] ?>/log">
          <div class="form-group">
            <label class="form-label">Opmerking toevoegen</label>
            <textarea name="opmerking" placeholder="Beschrijf wat je gedaan hebt of vraag om meer informatie..."></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Opslaan</button>
        </form>
      </div>
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Opdrachtgever</span><span><?= htmlspecialchars($item['opdrachtgever_naam']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Afdeling</span><span><?= htmlspecialchars($item['afdeling_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Prioriteit</span><span><?= prioBadge($item['prioriteit']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Impact</span><span><?= htmlspecialchars($item['impact']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Schatting</span><span><?= ($item['schatting_minuten'] ?? null) !== null ? $item['schatting_minuten'] . ' min' : '—' ?></span></div>
        <div class="meta-row"><span class="meta-key">Behandelaar</span><span><?= htmlspecialchars($item['behandelaar_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Datum aangemaakt</span><span><?= formatDatum($item['created_at']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Deadline</span><span><?= formatDatum($item['deadline']) ?></span></div>
      </div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Status wijzigen</span></div>
      <div style="padding:16px">
        <form method="post" action="/tickets/<?= $item['id'] ?>/log">
          <div class="form-group">
            <select name="status" style="width:100%">
              <?php foreach ($statussen as $val => $label): ?>
                <option value="<?= $val ?>" <?= $item['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Status bijwerken</button>
        </form>
      </div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Statuslogboek</span></div>

      <?php if (empty($statusLogs)): ?>
        <div class="empty-state">Nog geen statuswijzigingen.</div>
      <?php else: ?>
        <?php foreach ($statusLogs as $log): ?>
        <div class="log-item">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars($log['user_naam'] ?? 'Onbekend') ?></span>
            <span class="log-time"><?= formatDatumTijd($log['created_at']) ?></span>
            <span class="status-change">
              <span class="badge badge-<?= htmlspecialchars($log['status_van']) ?>" style="padding:2px 6px;font-size:10px"><?= statusLabel($log['status_van']) ?></span>
              &rarr;
              <span class="badge badge-<?= htmlspecialchars($log['status_naar']) ?>" style="padding:2px 6px;font-size:10px"><?= statusLabel($log['status_naar']) ?></span>
            </span>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
