<?php
/** @var array $stats */
/** @var array $recenteTickets */
/** @var array $voorraadOverview */
/** @var int $cyberrisicosOpen */
/** @var array $cyberrisicosPerDag */
/** @var array $cyberrisicosByDate */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$chartDates = array_map(fn (array $d) => $d['datum'], $cyberrisicosPerDag);
$chartLabels = array_map(fn (array $d) => date('d-m', strtotime($d['datum'])), $cyberrisicosPerDag);
$chartData = array_map(fn (array $d) => $d['aantal'], $cyberrisicosPerDag);
?>

<style>
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }

  .page-title {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
  }

  .dashboard-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .row.g-2 > [class*="col-"] {
    display: flex;
  }

  .stat {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    width: 100%;
    height: 100%;
    min-height: 96px;
    padding: 16px;
    background: var(--color-surface, #fff);
    border: 1px solid var(--color-border, #e5e7eb);
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
  }

  .stat:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
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
    margin-bottom: 16px;
  }

  .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-border, #e5e7eb);
    flex-wrap: wrap;
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

  th,
  td {
    padding: 14px 20px;
    text-align: left;
    vertical-align: middle;
    border-bottom: 1px solid var(--color-border, #e5e7eb);
  }

  thead th {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-secondary, #6b7280);
    white-space: nowrap;
  }

  tbody tr {
    cursor: pointer;
    transition: background-color .15s ease;
  }

  tbody tr:hover {
    background: rgba(0, 0, 0, 0.02);
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

  .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
  }

  @media (max-width: 576px) {
    .page-title {
      font-size: 24px;
    }

    .stat {
      min-height: 88px;
    }

    .stat-val {
      font-size: 24px;
    }

    th,
    td {
      padding: 12px 14px;
    }
  }
</style>

<div class="page-header">
  <div class="page-title">Dashboard</div>

  <div class="dashboard-actions">
    <a class="btn btn-primary" href="/tickets/create">+ Nieuw ticket</a>

    <a class="btn" href="/cyberrisicos/create" title="Cyberrisico melden">
      <i class="bi bi-shield-exclamation"></i> Risico melden
      <?php if ($cyberrisicosOpen > 0): ?>
        <span class="badge" style="background:#FBEAEA;color:#b3261e">
          <?= (int) $cyberrisicosOpen ?>
        </span>
      <?php endif; ?>
    </a>
  </div>
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

<div class="row g-2 mb-3 align-items-stretch">
  <div class="col-12 col-md-6 d-flex">
    <div class="card" style="margin-bottom:0;height:100%;width:100%;display:flex;flex-direction:column">
      <div class="card-header">
        <span class="card-title">Gemelde cyberrisico's — laatste 30 dagen</span>
        <a class="btn" href="/cyberrisicos" style="font-size:12px">Alle risico's &rarr;</a>
      </div>
      <div style="padding:16px;flex:1;display:flex;flex-direction:column;justify-content:center">
        <div style="position:relative;height:220px">
          <canvas id="cyberrisicoChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 d-flex">
    <div class="card" style="margin-bottom:0;height:100%;width:100%;display:flex;flex-direction:column">
      <div class="card-header">
        <span class="card-title">Mijn agenda</span>
        <div style="display:flex;align-items:center;gap:8px">
          <input type="date" id="dashAgendaDatum" style="width:auto;padding:5px 8px;font-size:13px">
          <button class="btn btn-primary" type="button" id="dashAgendaNieuwBtn" style="font-size:12px;padding:5px 10px">+ Toevoegen</button>
        </div>
      </div>
      <div style="padding:8px 16px;flex:1;overflow:auto" id="dashAgendaLijst">
        <div class="empty-state">Laden...</div>
      </div>
      <div style="padding:0 16px 16px">
        <a class="btn" href="/agenda" style="font-size:12px">Volledige agenda &rarr;</a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="dashAgendaModal" tabindex="-1" aria-labelledby="dashAgendaModalTitel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dashAgendaModalTitel">Nieuwe afspraak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="dashAgendaId" value="">
        <div class="form-group">
          <label class="form-label">Titel</label>
          <input type="text" id="dashAgendaTitel" required>
        </div>
        <div class="form-grid" style="grid-template-columns:1fr 1fr">
          <div class="form-group">
            <label class="form-label">Start</label>
            <input type="time" id="dashAgendaStart" value="09:00">
          </div>
          <div class="form-group">
            <label class="form-label">Einde</label>
            <input type="time" id="dashAgendaEind" value="10:00">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Locatie</label>
          <input type="text" id="dashAgendaLocatie">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger d-none" id="dashAgendaVerwijderBtn">Verwijderen</button>
        <button type="button" class="btn" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-primary" id="dashAgendaOpslaanBtn">Opslaan</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="incidentDayModal" tabindex="-1" aria-labelledby="incidentDayModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="incidentDayModalLabel">Incidenten</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
      </div>
      <div class="modal-body">
        <div id="incidentDayModalList"></div>
      </div>
    </div>
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
            <th style="width:90px">#</th>
            <th>Taak</th>
            <th style="width:100px">Afdeling</th>
            <th style="width:100px">Prioriteit</th>
            <th style="width:130px">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recenteTickets as $t): ?>
            <tr onclick="window.location='/tickets/<?= (int) $t['id'] ?>'">
              <td style="color:var(--color-text-tertiary);white-space:nowrap">#<?= (int) $t['id'] ?></td>
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

<div class="card">
  <div class="card-header">
    <span class="card-title">Beschikbare hardware</span>
    <a class="btn" href="/voorraad" style="font-size:12px">Voorraad beheren &rarr;</a>
  </div>

  <?php if (empty($voorraadOverview)): ?>
    <div class="empty-state">Nog geen voorraadtypen aangemaakt.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Type</th>
            <th style="width:80px">Code</th>
            <th style="width:110px">Beschikbaar</th>
            <th style="width:90px">Totaal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($voorraadOverview as $t): ?>
            <tr onclick="window.location='/voorraad?type_naam=<?= urlencode($t['naam']) ?>'">
              <td><?= htmlspecialchars($t['naam']) ?></td>
              <td style="color:var(--color-text-secondary)"><?= htmlspecialchars($t['code']) ?></td>
              <td><?= (int) $t['beschikbaar'] ?></td>
              <td style="color:var(--color-text-secondary)"><?= (int) $t['totaal'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var canvas = document.getElementById('cyberrisicoChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    var chartDates = <?= json_encode($chartDates) ?>;
    var incidentsByDate = <?= json_encode($cyberrisicosByDate) ?>;

    var STATUS_LABELS = {
        nieuw: 'Nieuw',
        in_onderzoek: 'In onderzoek',
        bevestigd: 'Bevestigd',
        opgelost: 'Opgelost',
        geaccepteerd: 'Geaccepteerd risico'
    };
    var PRIORITEIT_LABELS = { laag: 'Laag', middel: 'Middel', hoog: 'Hoog', kritiek: 'Kritiek' };

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDatumNl(iso) {
        var parts = iso.split('-');
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }

    function showIncidentsForDate(date) {
        var incidents = incidentsByDate[date] || [];

        document.getElementById('incidentDayModalLabel').textContent = 'Incidenten op ' + formatDatumNl(date);

        var list = document.getElementById('incidentDayModalList');
        list.innerHTML = '';

        if (incidents.length === 0) {
            list.innerHTML = '<div class="empty-state">Geen incidenten gemeld op deze dag.</div>';
        } else {
            incidents.forEach(function (inc) {
                var a = document.createElement('a');
                a.href = '/cyberrisicos/' + inc.id;
                a.className = 'log-item';
                a.style.display = 'block';
                a.innerHTML =
                    '<div class="log-meta" style="flex-wrap:wrap">' +
                    '<span class="log-user">' + escapeHtml(inc.titel) + '</span>' +
                    '<span class="badge badge-' + inc.status + '">' + (STATUS_LABELS[inc.status] || inc.status) + '</span>' +
                    '<span class="prio prio-' + inc.prioriteit + '"><span class="prio-dot"></span>' + (PRIORITEIT_LABELS[inc.prioriteit] || inc.prioriteit) + '</span>' +
                    '</div>';
                list.appendChild(a);
            });
        }

        var modalEl = document.getElementById('incidentDayModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    canvas.style.cursor = 'pointer';

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Gemelde incidenten',
                data: <?= json_encode($chartData) ?>,
                backgroundColor: '#9cc3e8',
                borderRadius: 3,
                maxBarThickness: 18
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            onClick: function (evt, elements) {
                if (!elements.length) {
                    return;
                }
                var index = elements[0].index;
                showIncidentsForDate(chartDates[index]);
            }
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var datumInput = document.getElementById('dashAgendaDatum');
    var lijst = document.getElementById('dashAgendaLijst');
    var modalEl = document.getElementById('dashAgendaModal');
    if (!datumInput || !modalEl) {
        return;
    }
    var modal = new bootstrap.Modal(modalEl);

    function vandaagStr() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function volgendeDagStr(dateStr) {
        var d = new Date(dateStr + 'T00:00:00');
        d.setDate(d.getDate() + 1);
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function tijd(iso) {
        return iso.slice(11, 16);
    }

    function laadDag() {
        var datum = datumInput.value || vandaagStr();
        lijst.innerHTML = '<div class="empty-state">Laden...</div>';

        fetch('/agenda/events?start=' + datum + 'T00:00:00&end=' + volgendeDagStr(datum) + 'T00:00:00')
            .then(function (r) { return r.json(); })
            .then(function (events) {
                if (!events.length) {
                    lijst.innerHTML = '<div class="empty-state">Geen afspraken op deze dag.</div>';
                    return;
                }
                lijst.innerHTML = '';
                events.forEach(function (ev) {
                    var row = document.createElement('div');
                    row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--color-border-tertiary);cursor:pointer';
                    row.innerHTML =
                        '<span style="width:8px;height:8px;border-radius:50%;background:' + ev.color + ';flex-shrink:0"></span>' +
                        '<span style="font-size:12px;color:var(--color-text-secondary);white-space:nowrap">' + tijd(ev.start) + '&ndash;' + tijd(ev.end) + '</span>' +
                        '<span class="text-truncate d-block" style="font-size:13px">' + ev.title.replace(/</g, '&lt;') + '</span>';
                    row.addEventListener('click', function () {
                        openBewerken(ev);
                    });
                    lijst.appendChild(row);
                });
            });
    }

    function openNieuw() {
        document.getElementById('dashAgendaModalTitel').textContent = 'Nieuwe afspraak';
        document.getElementById('dashAgendaId').value = '';
        document.getElementById('dashAgendaTitel').value = '';
        document.getElementById('dashAgendaLocatie').value = '';
        document.getElementById('dashAgendaStart').value = '09:00';
        document.getElementById('dashAgendaEind').value = '10:00';
        document.getElementById('dashAgendaVerwijderBtn').classList.add('d-none');
        modal.show();
    }

    function openBewerken(ev) {
        document.getElementById('dashAgendaModalTitel').textContent = 'Afspraak bewerken';
        document.getElementById('dashAgendaId').value = ev.id;
        document.getElementById('dashAgendaTitel').value = ev.title;
        document.getElementById('dashAgendaLocatie').value = ev.extendedProps.locatie || '';
        document.getElementById('dashAgendaStart').value = tijd(ev.start);
        document.getElementById('dashAgendaEind').value = tijd(ev.end);
        document.getElementById('dashAgendaVerwijderBtn').classList.remove('d-none');
        modal.show();
    }

    document.getElementById('dashAgendaNieuwBtn').addEventListener('click', openNieuw);
    datumInput.addEventListener('change', laadDag);

    document.getElementById('dashAgendaOpslaanBtn').addEventListener('click', function () {
        var datum = datumInput.value || vandaagStr();
        var id = document.getElementById('dashAgendaId').value;
        var titel = document.getElementById('dashAgendaTitel').value.trim();
        var start = document.getElementById('dashAgendaStart').value;
        var eind = document.getElementById('dashAgendaEind').value;

        if (!titel || !start || !eind) {
            window.alert('Titel, start en einde zijn verplicht.');
            return;
        }

        var payload = {
            titel: titel,
            start_op: datum + 'T' + start,
            eind_op: datum + 'T' + eind,
            locatie: document.getElementById('dashAgendaLocatie').value
        };
        if (!id) {
            payload.type = 'afspraak';
        }

        var url = id ? '/agenda/' + id : '/agenda';
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (res.success) {
                modal.hide();
                laadDag();
            } else {
                window.alert(res.error || 'Opslaan is mislukt.');
            }
        });
    });

    document.getElementById('dashAgendaVerwijderBtn').addEventListener('click', function () {
        var id = document.getElementById('dashAgendaId').value;
        if (!id || !window.confirm('Deze afspraak verwijderen?')) {
            return;
        }
        fetch('/agenda/' + id + '/verwijderen', { method: 'POST' })
            .then(function (r) { return r.json(); })
            .then(function () {
                modal.hide();
                laadDag();
            });
    });

    datumInput.value = vandaagStr();
    laadDag();
});
</script>