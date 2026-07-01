<?php
/** @var array $stats */
/** @var array $recenteTickets */
/** @var array $voorraadOverview */
/** @var int $cyberrisicosOpen */
/** @var array $cyberrisicosPerDag */
/** @var array $cyberrisicosByDate */

require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$chartDates  = array_map(fn(array $d) => $d['datum'], $cyberrisicosPerDag);
$chartLabels = array_map(fn(array $d) => date('d-m', strtotime($d['datum'])), $cyberrisicosPerDag);
$chartData   = array_map(fn(array $d) => $d['aantal'], $cyberrisicosPerDag);
?>

<style>
    .stat-card {
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-card .card-body {
        min-height: 110px;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .agenda-item:hover {
        background-color: rgba(var(--bs-secondary-rgb), .08);
    }

    .chart-wrap {
        position: relative;
        height: 220px;
    }

    .text-truncate-1 {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 575.98px) {
        .chart-wrap {
            height: 200px;
        }
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Dashboard</h1>

    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary" href="/tickets/create">+ Nieuw ticket</a>

        <a class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" href="/cyberrisicos/create" title="Cyberrisico melden">
            <i class="bi bi-shield-exclamation"></i>
            <span>Risico melden</span>
            <?php if ($cyberrisicosOpen > 0): ?>
                <span class="badge rounded-pill text-bg-danger">
                    <?= (int) $cyberrisicosOpen ?>
                </span>
            <?php endif; ?>
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <a class="card shadow-sm h-100 text-decoration-none stat-card" href="/tickets?status=open">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="text-body-secondary small mb-1">Open tickets</div>
                <div class="fs-2 fw-bold text-info">
                    <?= (int) $stats['tickets_open'] ?>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a class="card shadow-sm h-100 text-decoration-none stat-card" href="/tickets?status=in_behandeling">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="text-body-secondary small mb-1">In behandeling</div>
                <div class="fs-2 fw-bold text-warning">
                    <?= (int) $stats['tickets_in_behandeling'] ?>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a class="card shadow-sm h-100 text-decoration-none stat-card" href="/verbeterpunten">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="text-body-secondary small mb-1">Verbeterpunten</div>
                <div class="fs-2 fw-bold">
                    <?= (int) $stats['verbeterpunten'] ?>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a class="card shadow-sm h-100 text-decoration-none stat-card" href="/medewerkers">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="text-body-secondary small mb-1">Medewerkers</div>
                <div class="fs-2 fw-bold">
                    <?= (int) $stats['medewerkers'] ?>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6 d-flex">
        <div class="card shadow-sm w-100 h-100">
            <div class="card-header bg-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-semibold">Gemelde cyberrisico's — laatste 30 dagen</span>
                <a class="btn btn-sm btn-outline-secondary" href="/cyberrisicos">Alle risico's &rarr;</a>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="cyberrisicoChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6 d-flex">
        <div class="card shadow-sm w-100 h-100">
            <div class="card-header bg-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-semibold">Mijn agenda</span>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="date" id="dashAgendaDatum" class="form-control form-control-sm" style="width:auto;">
                    <button class="btn btn-sm btn-primary" type="button" id="dashAgendaNieuwBtn">+ Toevoegen</button>
                </div>
            </div>

            <div class="card-body py-2" id="dashAgendaLijst">
                <div class="text-body-secondary">Laden...</div>
            </div>

            <div class="card-footer bg-body border-top-0 pt-0">
                <a class="btn btn-sm btn-outline-secondary" href="/agenda">Volledige agenda &rarr;</a>
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

                <div class="mb-3">
                    <label class="form-label" for="dashAgendaTitel">Titel</label>
                    <input type="text" id="dashAgendaTitel" class="form-control" required>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label" for="dashAgendaStart">Start</label>
                        <input type="time" id="dashAgendaStart" class="form-control" value="09:00">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="dashAgendaEind">Einde</label>
                        <input type="time" id="dashAgendaEind" class="form-control" value="10:00">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label" for="dashAgendaLocatie">Locatie</label>
                    <input type="text" id="dashAgendaLocatie" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger d-none" id="dashAgendaVerwijderBtn">Verwijderen</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuleren</button>
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

<div class="card shadow-sm mb-3">
    <div class="card-header bg-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold">Recente tickets</span>
        <a class="btn btn-sm btn-outline-secondary" href="/tickets">Alle tickets &rarr;</a>
    </div>

    <?php if (empty($recenteTickets)): ?>
        <div class="card-body text-body-secondary">Nog geen tickets aangemaakt.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 90px;">#</th>
                        <th>Taak</th>
                        <th style="width: 140px;">Afdeling</th>
                        <th style="width: 120px;">Prioriteit</th>
                        <th style="width: 140px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recenteTickets as $t): ?>
                        <tr class="cursor-pointer" onclick="window.location='/tickets/<?= (int) $t['id'] ?>'">
                            <td class="text-body-secondary text-nowrap">#<?= (int) $t['id'] ?></td>
                            <td>
                                <span class="text-truncate-1" title="<?= htmlspecialchars($t['titel']) ?>">
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

<div class="card shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold">Beschikbare hardware</span>
        <a class="btn btn-sm btn-outline-secondary" href="/voorraad">Voorraad beheren &rarr;</a>
    </div>

    <?php if (empty($voorraadOverview)): ?>
        <div class="card-body text-body-secondary">Nog geen voorraadtypen aangemaakt.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th style="width: 100px;">Code</th>
                        <th style="width: 130px;">Beschikbaar</th>
                        <th style="width: 100px;">Totaal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($voorraadOverview as $t): ?>
                        <tr class="cursor-pointer" onclick="window.location='/voorraad?type_naam=<?= urlencode($t['naam']) ?>'">
                            <td><?= htmlspecialchars($t['naam']) ?></td>
                            <td class="text-body-secondary"><?= htmlspecialchars($t['code']) ?></td>
                            <td><?= (int) $t['beschikbaar'] ?></td>
                            <td class="text-body-secondary"><?= (int) $t['totaal'] ?></td>
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

    var PRIORITEIT_LABELS = {
        laag: 'Laag',
        middel: 'Middel',
        hoog: 'Hoog',
        kritiek: 'Kritiek'
    };

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDatumNl(iso) {
        var parts = iso.split('-');
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }

    function badgeClassForStatus(status) {
        switch (status) {
            case 'nieuw': return 'text-bg-primary';
            case 'in_onderzoek': return 'text-bg-warning';
            case 'bevestigd': return 'text-bg-danger';
            case 'opgelost': return 'text-bg-success';
            case 'geaccepteerd': return 'text-bg-secondary';
            default: return 'text-bg-light';
        }
    }

    function badgeClassForPrio(prio) {
        switch (prio) {
            case 'laag': return 'text-bg-secondary';
            case 'middel': return 'text-bg-warning';
            case 'hoog': return 'text-bg-danger';
            case 'kritiek': return 'text-bg-dark';
            default: return 'text-bg-light';
        }
    }

    function showIncidentsForDate(date) {
        var incidents = incidentsByDate[date] || [];

        document.getElementById('incidentDayModalLabel').textContent = 'Incidenten op ' + formatDatumNl(date);

        var list = document.getElementById('incidentDayModalList');
        list.innerHTML = '';

        if (incidents.length === 0) {
            list.innerHTML = '<div class="text-body-secondary">Geen incidenten gemeld op deze dag.</div>';
        } else {
            var wrapper = document.createElement('div');
            wrapper.className = 'list-group list-group-flush';

            incidents.forEach(function (inc) {
                var a = document.createElement('a');
                a.href = '/cyberrisicos/' + inc.id;
                a.className = 'list-group-item list-group-item-action';

                a.innerHTML =
                    '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">' +
                        '<div class="fw-medium">' + escapeHtml(inc.titel) + '</div>' +
                        '<div class="d-flex flex-wrap gap-2">' +
                            '<span class="badge rounded-pill ' + badgeClassForStatus(inc.status) + '">' + (STATUS_LABELS[inc.status] || inc.status) + '</span>' +
                            '<span class="badge rounded-pill ' + badgeClassForPrio(inc.prioriteit) + '">' + (PRIORITEIT_LABELS[inc.prioriteit] || inc.prioriteit) + '</span>' +
                        '</div>' +
                    '</div>';

                wrapper.appendChild(a);
            });

            list.appendChild(wrapper);
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
                borderRadius: 4,
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
        lijst.innerHTML = '<div class="text-body-secondary">Laden...</div>';

        fetch('/agenda/events?start=' + datum + 'T00:00:00&end=' + volgendeDagStr(datum) + 'T00:00:00')
            .then(function (r) { return r.json(); })
            .then(function (events) {
                if (!events.length) {
                    lijst.innerHTML = '<div class="text-body-secondary">Geen afspraken op deze dag.</div>';
                    return;
                }

                lijst.innerHTML = '';
                var wrapper = document.createElement('div');
                wrapper.className = 'list-group list-group-flush';

                events.forEach(function (ev) {
                    var row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'list-group-item list-group-item-action agenda-item';

                    row.innerHTML =
                        '<div class="d-flex align-items-center gap-3">' +
                            '<span class="rounded-circle flex-shrink-0" style="width:10px;height:10px;background:' + ev.color + ';"></span>' +
                            '<span class="small text-body-secondary flex-shrink-0">' + tijd(ev.start) + '–' + tijd(ev.end) + '</span>' +
                            '<span class="text-truncate">' + ev.title.replace(/</g, '&lt;') + '</span>' +
                        '</div>';

                    row.addEventListener('click', function () {
                        openBewerken(ev);
                    });

                    wrapper.appendChild(row);
                });

                lijst.appendChild(wrapper);
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
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
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