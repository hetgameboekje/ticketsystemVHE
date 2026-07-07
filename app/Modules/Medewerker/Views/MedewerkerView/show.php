<?php
/** @var array $item */
/** @var array $uitgiften */
/** @var array $apparaten */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/medewerkers" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['voornaam'] . ' ' . $item['achternaam']) ?></div>
    <?= statusBadge($item['status']) ?>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/medewerkers/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('medewerkers', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:10px">
      <div class="card-header"><span class="card-title">Uitgiften</span></div>
      <?php if (empty($uitgiften)): ?>
        <div class="empty-state">Nog geen uitgiften voor deze medewerker.</div>
      <?php else: ?>
        <div class="log-list">
          <?php foreach ($uitgiften as $u): ?>
          <a class="log-item" href="/uitgiften/<?= $u['id'] ?>" style="display:block;color:inherit;text-decoration:none">
            <div class="log-meta">
              <span class="log-user"><?= htmlspecialchars($u['type_naam'] ?? 'Item') ?><?= $u['variant'] ? ' (' . htmlspecialchars($u['variant']) . ')' : '' ?></span>
              <span class="log-time"><?= formatDatum($u['uitgegeven_op']) ?></span>
              <span class="badge badge-<?= $u['status'] === 'uitgegeven' ? 'in_behandeling' : 'opgelost' ?>"><?= $u['status'] === 'uitgegeven' ? 'Uitgegeven' : 'Geretourneerd' ?></span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Apparaten &amp; software</span></div>
      <?php if (empty($apparaten)): ?>
        <div class="empty-state">Nog geen apparaten gekoppeld.</div>
      <?php else: ?>
        <div class="log-list">
          <?php foreach ($apparaten as $d): ?>
          <a class="log-item" href="/apparaten/<?= $d['id'] ?>" style="display:block;color:inherit;text-decoration:none">
            <div class="log-meta">
              <span class="log-user"><?= htmlspecialchars($d['naam']) ?></span>
              <span class="log-time"><?= (int) $d['software_aantal'] ?> software-item(s)</span>
            </div>
            <div class="log-text">Laatst geïmporteerd: <?= formatDatumTijd($d['laatst_geimporteerd_op']) ?></div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">Details</span></div>
    <div style="padding:0 16px">
      <div class="meta-row"><span class="meta-key">Functie</span><span><?= htmlspecialchars($item['functie'] ?? '—') ?></span></div>
      <div class="meta-row"><span class="meta-key">Afdeling</span><span><?= htmlspecialchars($item['afdeling_naam'] ?? '—') ?></span></div>
      <div class="meta-row"><span class="meta-key">E-mail</span><span><?= htmlspecialchars($item['email'] ?? '—') ?></span></div>
      <div class="meta-row"><span class="meta-key">Telefoon</span><span><?= htmlspecialchars($item['telefoon'] ?? '—') ?></span></div>
      <div class="meta-row"><span class="meta-key">Startdatum</span><span><?= formatDatum($item['startdatum']) ?></span></div>
      <div class="meta-row">
        <span class="meta-key">Login</span>
        <span>
          <?php if (empty($item['user_id'])): ?>
            <span class="text-body-secondary">Geen login gekoppeld</span>
          <?php elseif (!empty($item['login_deleted_at'])): ?>
            <?= htmlspecialchars($item['login_email']) ?> <span class="text-body-secondary">(gedeactiveerd)</span>
          <?php else: ?>
            <?= htmlspecialchars($item['login_email']) ?>
          <?php endif; ?>
        </span>
      </div>
    </div>
  </div>
</div>
