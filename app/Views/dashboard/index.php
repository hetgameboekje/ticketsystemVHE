<?php
/** @var array $stats */
/** @var array $recenteTickets */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>

<style>
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
  }

  .page-title {
    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
  }

  .stat {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    height: 100%;
    min-height: 96px;
    padding: 16px;
    background: var(--color-surface, #fff);
    border: 1px solid var(--color-border, #e5e7eb);
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
  }

  .stat:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
    text-decoration: none;
    color: inherit;
  }

  .stat-label {
    display: block;
    margin: 0;
    font-size: 13px;
    line-height: 1.2;
    color: var(--color-text-secondary, #6b7280);
  }

  .stat-val {
    display: block;
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
    color: var(--color-text, #111827);
  }

  .card {
    background: var(--color-surface, #fff);
    border: 1px solid var(--color-border, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
  }

  .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-border, #e5e7eb);
  }

  .card-title {
    font-size: 16px;
    font-weight: 600;
    line-height: 1.2;
  }

  .table-wrap {
    overflow-x: auto;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th, td {
    padding: 14px 20px;
    text-align: left;
    vertical-align: middle;
    border-bottom: 1px solid var(--color-border, #e5e7eb);
  }

  tbody tr {
    cursor: pointer;
    transition: background-color .15s ease;
  }

  tbody tr:hover {
    background: rgba(0,0,0,.02);
  }

  .text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .empty-state {
    padding: 24px 20px;
    color: var(--color-text-secondary, #6b7280);
  }
</style>

<div class="page-header">
  <div class="page-title">Dashboard</div>
  <a class="btn btn-primary" href="/tickets/create">+ Nieuw ticket</a>
</div>

<div class="row g-2 mb-3">
  <div class="col-12 col-sm-6 col-md-3">
    <a class="stat" href="/tickets?status=open" style="border-top:2px solid var(--color-text-info)">
      <div class="stat-label">Open tickets</div>
      <div class="stat-val" style="color:var(--color-text-info)">
        <?= (int) $stats['tickets_open'] ?>
      </div>
    </a>
  </div>

  <div class="col-12 col-sm-6 col-md-3">
    <a class="stat" href="/tickets?status=in_behandeling" style="border-top:2px solid var(--color-text-warning)">
      <div class="stat-label">In behandeling</div>
      <div class="stat-val" style="color:var(--color-text-warning)">
        <?= (int) $stats['tickets_in_behandeling'] ?>
      </div>
    </a>
  </div>

  <div class="col-12 col-sm-6 col-md-3">
    <a class="stat" href="/verbeterpunten" style="border-top:2px solid var(--color-text-success)">
      <div class="stat-label">Verbeterpunten</div>
      <div class="stat-val">
        <?= (int) $stats['verbeterpunten'] ?>
      </div>
    </a>
  </div>

  <div class="col-12 col-sm-6 col-md-3">
    <a class="stat" href="/medewerkers" style="border-top:2px solid var(--color-border-secondary)">
      <div class="stat-label">Medewerkers</div>
      <div class="stat-val">
        <?= (int) $stats['medewerkers'] ?>
      </div>
    </a>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">Recente tickets</span>
    <a class="btn" href="/tickets" style="font-size:12px">Alle tickets &rarr;</a>
  </div>

  <?php if (empty($recenteTickets)): ?>
    <div class="empty-state">Nog geen tickets aangemaakt.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th>Taak</th>
            <th style="width:100px">Afdeling</th>
            <th style="width:100px">Prioriteit</th>
            <th style="width:130px">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recenteTickets as $t): ?>
            <tr onclick="window.location='/tickets/<?= (int) $t['id'] ?>'">
              <td style="color:var(--color-text-tertiary)">#<?= (int) $t['id'] ?></td>
              <td>
                <span class="text-truncate d-block" title="<?= htmlspecialchars($t['titel']) ?>">
                  <?= htmlspecialchars($t['titel']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($t['afdeling_naam'] ?? '—') ?></td>
              <td><?= prioBadge($t['prioriteit']) ?></td>
              <td><?= statusBadge($t['status']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>