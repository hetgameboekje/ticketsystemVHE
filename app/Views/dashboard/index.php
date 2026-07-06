<?php
/** @var array $mag */
/** @var array $stats */
/** @var array $actieveTickets */
/** @var array $voorraadOverview */
/** @var int $cyberrisicosOpen */
/** @var array $cyberrisicosPerDag */
/** @var array $cyberrisicosByDate */
/** @var array $afdelingen */
/** @var array $gebruikers */
/** @var array $cyberCategorieen */
/** @var array $cyberPrioriteiten */
/** @var array|null $laatsteTelefoonlijst */

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

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Dashboard</h1>

    <div class="ms-auto d-flex gap-2">
        <?php if ($mag['tickets']['schrijven']): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dashTicketModal">
            <i class="bi bi-plus-circle"></i> Nieuw ticket
        </button>
        <?php endif; ?>

        <?php if ($mag['cyberrisicos']['schrijven']): ?>
        <button type="button" class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#dashRisicoModal">
            <i class="bi bi-shield-exclamation"></i> Risico melden
            <?php if ($cyberrisicosOpen > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                    <?= (int) $cyberrisicosOpen ?>
                </span>
            <?php endif; ?>
        </button>
        <?php endif; ?>
    </div>
</div>




<div class="row g-3 mb-3">
    <?php if ($mag['tickets']['lezen']): ?>
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
    <?php endif; ?>

    <?php if ($mag['verbeterpunten']['lezen']): ?>
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
    <?php endif; ?>

    <?php if ($mag['medewerkers']['lezen']): ?>
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
    <?php endif; ?>

    <div class="col-12 col-sm-6 col-xl-3 d-flex">
        <div class="card shadow-sm w-100 h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="text-body-secondary small mb-1">Laatste telefoonlijst</div>
                <?php if ($laatsteTelefoonlijst === null): ?>
                    <div class="text-body-secondary small">Nog geen telefoonlijst verwerkt.</div>
                    <a class="btn btn-sm btn-outline-secondary mt-2" href="/tools/telefoonlijst">Openen</a>
                <?php else: ?>
                    <div class="small text-body-secondary mb-2">
                        <?= formatDatumTijd($laatsteTelefoonlijst['processed_at']) ?> &middot;
                        <?= (int) $laatsteTelefoonlijst['contact_count'] ?> contact(en)
                    </div>
                    <a class="btn btn-sm btn-primary" href="/tools/telefoonlijst/<?= (int) $laatsteTelefoonlijst['id'] ?>/download">Download .vcf</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php if ($mag['cyberrisicos']['lezen']): ?>
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
    <?php endif; ?>

    <?php if ($mag['agenda']['lezen']): ?>
    <div class="col-12 col-lg-6 d-flex">
        <div class="card shadow-sm w-100 h-100">
            <div class="card-header bg-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a class="fw-semibold text-decoration-none" href="/agenda">Mijn agenda</a>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="date" id="dashAgendaDatum" class="form-control form-control-sm" style="width:auto;">
                    <?php if ($mag['agenda']['schrijven']): ?>
                    <button class="btn btn-sm btn-primary" type="button" id="dashAgendaNieuwBtn">+ Toevoegen</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body py-2" id="dashAgendaLijst">
                <div class="text-body-secondary">Laden...</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($mag['tickets']['schrijven']): ?>
<div class="modal fade" id="dashTicketModal" tabindex="-1" aria-labelledby="dashTicketModalTitel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="/tickets">
                <div class="modal-header">
                    <h5 class="modal-title" id="dashTicketModalTitel">Nieuw ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Opdrachtgever</label>
                            <input type="text" class="form-control" name="opdrachtgever_naam" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Afdeling</label>
                            <select class="form-select" name="afdeling_id">
                                <option value="">— Selecteer afdeling —</option>
                                <?php foreach ($afdelingen as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['naam']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Taak (korte titel)</label>
                            <input type="text" class="form-control" name="titel" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Omschrijving</label>
                            <textarea class="form-control" name="omschrijving" style="min-height:100px"></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Prioriteit</label>
                            <select class="form-select" name="prioriteit">
                                <option value="laag">Laag</option>
                                <option value="normaal" selected>Normaal</option>
                                <option value="hoog">Hoog</option>
                                <option value="kritiek">Kritiek</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Impact</label>
                            <select class="form-select" name="impact">
                                <option>Laag</option>
                                <option selected>Normaal</option>
                                <option>Hoog — afdeling</option>
                                <option>Kritiek — productie</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Schatting (minuten)</label>
                            <input type="number" class="form-control" step="1" name="schatting_minuten">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Deadline</label>
                            <input type="date" class="form-control" name="deadline">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Behandelaar</label>
                            <select class="form-select" name="behandelaar_id">
                                <option value="">— Niet toegewezen —</option>
                                <?php foreach ($gebruikers as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['naam']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuleren</button>
                    <button type="submit" class="btn btn-primary">Ticket aanmaken</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($mag['cyberrisicos']['schrijven']): ?>
<div class="modal fade" id="dashRisicoModal" tabindex="-1" aria-labelledby="dashRisicoModalTitel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="/cyberrisicos">
                <div class="modal-header">
                    <h5 class="modal-title" id="dashRisicoModalTitel">Risico melden</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Titel</label>
                            <input type="text" class="form-control" name="titel" required placeholder="bv. Sticky note met wachtwoord onder toetsenbord">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Omschrijving</label>
                            <textarea class="form-control" name="omschrijving" style="min-height:100px" required></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Type risico</label>
                            <select class="form-select" name="categorie">
                                <?php foreach ($cyberCategorieen as $val => $label): ?>
                                    <option value="<?= $val ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Prioriteit</label>
                            <select class="form-select" name="prioriteit">
                                <?php foreach ($cyberPrioriteiten as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $val === 'middel' ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Locatie</label>
                            <input type="text" class="form-control" name="locatie" placeholder="bv. Serverroom, receptie, kantoor 2e verdieping">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Gemeld door</label>
                            <input type="text" class="form-control" name="gemeld_door" placeholder="Naam van de melder">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Eigenaar (verantwoordelijk voor opvolging)</label>
                            <select class="form-select" name="eigenaar_id">
                                <option value="">— Niet toegewezen —</option>
                                <?php foreach ($gebruikers as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['naam']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Datum geconstateerd</label>
                            <input type="date" class="form-control" name="datum_geconstateerd">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Oplossingsadvies</label>
                            <textarea class="form-control" name="oplossingsadvies" placeholder="Wat moet er gebeuren om dit risico weg te nemen?"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bewijs / notities</label>
                            <textarea class="form-control" name="bewijs_notities" placeholder="Waar/wanneer geconstateerd, foto-locatie, extra context..."></textarea>
                        </div>
                        <div class="col-12 form-check">
                            <input class="form-check-input" type="checkbox" id="dashRisicoGevoelig" name="is_gevoelig" value="1">
                            <label class="form-check-label" for="dashRisicoGevoelig">Bevat gevoelige informatie (bijv. echte wachtwoorden, credentials) — wees terughoudend met details hierboven</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuleren</button>
                    <button type="submit" class="btn btn-primary">Risico registreren</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($mag['agenda']['lezen']): ?>
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
                <?php if ($mag['agenda']['schrijven']): ?>
                <button type="button" class="btn btn-danger d-none" id="dashAgendaVerwijderBtn">Verwijderen</button>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuleren</button>
                <?php if ($mag['agenda']['schrijven']): ?>
                <button type="button" class="btn btn-primary" id="dashAgendaOpslaanBtn">Opslaan</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($mag['cyberrisicos']['lezen']): ?>
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
<?php endif; ?>

<?php if ($mag['tickets']['lezen']): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold">Actieve tickets</span>
        <a class="btn btn-sm btn-outline-secondary" href="/tickets">Alle tickets &rarr;</a>
    </div>

    <?php if (empty($actieveTickets)): ?>
        <div class="card-body text-body-secondary">Geen actieve tickets.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 90px;">#</th>
                        <th>Taak</th>
                        <th style="width: 140px;">Afdeling</th>
                        <th style="width: 120px;">Prioriteit</th>
                        <th style="width: 140px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actieveTickets as $t): ?>
                        <tr class="cursor-pointer" onclick="window.location='/tickets/<?= (int) $t['id'] ?>'">
                            <td class="text-body-secondary text-nowrap">#<?= (int) $t['id'] ?></td>
                            <td>
                                <span title="<?= htmlspecialchars($t['titel']) ?>">
                                    <?= htmlspecialchars(truncateWoorden($t['titel'])) ?>
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
<?php endif; ?>

<?php if ($mag['voorraad']['lezen']): ?>
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
                <thead>
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
<?php endif; ?>

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

    var verwijderBtn = document.getElementById('dashAgendaVerwijderBtn');
    var opslaanBtn = document.getElementById('dashAgendaOpslaanBtn');
    var magSchrijven = !!opslaanBtn;

    function openNieuw() {
        document.getElementById('dashAgendaModalTitel').textContent = 'Nieuwe afspraak';
        document.getElementById('dashAgendaId').value = '';
        document.getElementById('dashAgendaTitel').value = '';
        document.getElementById('dashAgendaLocatie').value = '';
        document.getElementById('dashAgendaStart').value = '09:00';
        document.getElementById('dashAgendaEind').value = '10:00';
        if (verwijderBtn) {
            verwijderBtn.classList.add('d-none');
        }
        modal.show();
    }

    function openBewerken(ev) {
        if (!magSchrijven) {
            return;
        }
        document.getElementById('dashAgendaModalTitel').textContent = 'Afspraak bewerken';
        document.getElementById('dashAgendaId').value = ev.id;
        document.getElementById('dashAgendaTitel').value = ev.title;
        document.getElementById('dashAgendaLocatie').value = ev.extendedProps.locatie || '';
        document.getElementById('dashAgendaStart').value = tijd(ev.start);
        document.getElementById('dashAgendaEind').value = tijd(ev.end);
        if (verwijderBtn) {
            verwijderBtn.classList.remove('d-none');
        }
        modal.show();
    }

    var nieuwBtn = document.getElementById('dashAgendaNieuwBtn');
    if (nieuwBtn) {
        nieuwBtn.addEventListener('click', openNieuw);
    }
    datumInput.addEventListener('change', laadDag);

    if (opslaanBtn) {
    opslaanBtn.addEventListener('click', function () {
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
    }

    if (verwijderBtn) {
    verwijderBtn.addEventListener('click', function () {
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
    }

    datumInput.value = vandaagStr();
    laadDag();
});
</script>