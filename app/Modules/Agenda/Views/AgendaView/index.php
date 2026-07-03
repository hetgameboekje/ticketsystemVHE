<?php
/** @var array $gebruikers */
/** @var array $tickets */
/** @var array $verbeterpunten */
/** @var int $huidigeGebruikerId */
/** @var array $types */

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="page-header">
  <div class="page-title">Agenda</div>
  <div style="display:flex;align-items:center;gap:10px">
    <select id="agenda-persoon" class="form-select" style="width:auto">
      <?php foreach ($gebruikers as $g): ?>
        <option value="<?= $g['id'] ?>" <?= (int) $g['id'] === (int) $huidigeGebruikerId ? 'selected' : '' ?>><?= htmlspecialchars($g['naam']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="button" id="agenda-nieuw-btn">+ Nieuwe afspraak</button>
  </div>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<div class="card" style="padding:16px">
  <div id="agenda-calendar"></div>
</div>

<div class="modal fade" id="agendaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="agendaModalTitel">Nieuwe afspraak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="agenda-id" value="">
        <div class="form-group">
          <label class="form-label">Titel</label>
          <input type="text" id="agenda-titel" required>
        </div>
        <div class="form-group">
          <label class="form-label">Type</label>
          <select id="agenda-type">
            <?php foreach ($types as $val => $label): ?>
              <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" id="agenda-gekoppeld-wrap" style="display:none">
          <label class="form-label" id="agenda-gekoppeld-label">Koppelen aan</label>
          <select id="agenda-gekoppeld-id"></select>
        </div>
        <div class="form-group">
          <label class="form-label">Persoon</label>
          <select id="agenda-user-id">
            <?php foreach ($gebruikers as $g): ?>
              <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['naam']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grid" style="grid-template-columns:1fr 1fr">
          <div class="form-group">
            <label class="form-label">Start</label>
            <input type="datetime-local" id="agenda-start">
          </div>
          <div class="form-group">
            <label class="form-label">Einde</label>
            <input type="datetime-local" id="agenda-eind">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Locatie</label>
          <input type="text" id="agenda-locatie">
        </div>
        <div class="form-group">
          <label class="form-label">Omschrijving</label>
          <textarea id="agenda-omschrijving"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger d-none" id="agenda-verwijder-btn">Verwijderen</button>
        <button type="button" class="btn" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-primary" id="agenda-opslaan-btn">Opslaan</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/nl.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tickets = <?= json_encode(array_map(fn ($t) => ['id' => (int) $t['id'], 'titel' => $t['titel']], $tickets), JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var verbeterpunten = <?= json_encode(array_map(fn ($v) => ['id' => (int) $v['id'], 'titel' => $v['titel']], $verbeterpunten), JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var huidigeGebruikerId = <?= (int) $huidigeGebruikerId ?>;

    var calendarEl = document.getElementById('agenda-calendar');
    var personSelect = document.getElementById('agenda-persoon');
    var modalEl = document.getElementById('agendaModal');
    var modal = new bootstrap.Modal(modalEl);

    function gekoppeldeOpties() {
        var type = document.getElementById('agenda-type').value;
        var wrap = document.getElementById('agenda-gekoppeld-wrap');
        var select = document.getElementById('agenda-gekoppeld-id');
        var label = document.getElementById('agenda-gekoppeld-label');
        select.innerHTML = '<option value="">— Geen —</option>';

        if (type === 'ticket') {
            wrap.style.display = '';
            label.textContent = 'Ticket';
            tickets.forEach(function (t) {
                select.innerHTML += '<option value="' + t.id + '">' + t.titel.replace(/</g, '&lt;') + '</option>';
            });
        } else if (type === 'verbeterpunt') {
            wrap.style.display = '';
            label.textContent = 'Verbeterpunt';
            verbeterpunten.forEach(function (v) {
                select.innerHTML += '<option value="' + v.id + '">' + v.titel.replace(/</g, '&lt;') + '</option>';
            });
        } else {
            wrap.style.display = 'none';
        }
    }
    document.getElementById('agenda-type').addEventListener('change', gekoppeldeOpties);

    function toDatetimeLocal(iso) {
        return iso.slice(0, 16);
    }

    function openCreateModal(startStr, endStr) {
        document.getElementById('agendaModalTitel').textContent = 'Nieuwe afspraak';
        document.getElementById('agenda-id').value = '';
        document.getElementById('agenda-titel').value = '';
        document.getElementById('agenda-type').value = 'afspraak';
        document.getElementById('agenda-omschrijving').value = '';
        document.getElementById('agenda-locatie').value = '';
        document.getElementById('agenda-user-id').value = personSelect.value;
        document.getElementById('agenda-start').value = startStr ? toDatetimeLocal(startStr) : '';
        document.getElementById('agenda-eind').value = endStr ? toDatetimeLocal(endStr) : '';
        document.getElementById('agenda-verwijder-btn').classList.add('d-none');
        gekoppeldeOpties();
        modal.show();
    }

    function openEditModal(event) {
        var props = event.extendedProps;
        document.getElementById('agendaModalTitel').textContent = 'Afspraak bewerken';
        document.getElementById('agenda-id').value = event.id;
        document.getElementById('agenda-titel').value = event.title;
        document.getElementById('agenda-type').value = props.type;
        document.getElementById('agenda-omschrijving').value = props.omschrijving || '';
        document.getElementById('agenda-locatie').value = props.locatie || '';
        document.getElementById('agenda-user-id').value = props.user_id;
        document.getElementById('agenda-start').value = toDatetimeLocal(event.startStr);
        document.getElementById('agenda-eind').value = toDatetimeLocal(event.endStr);
        document.getElementById('agenda-verwijder-btn').classList.remove('d-none');
        gekoppeldeOpties();
        if (props.gekoppeld_id) {
            document.getElementById('agenda-gekoppeld-id').value = props.gekoppeld_id;
        }
        modal.show();
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'nl',
        firstDay: 1,
        height: 'auto',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
        editable: true,
        selectable: true,
        events: function (info, success, failure) {
            fetch('/agenda/events?user_id=' + personSelect.value + '&start=' + info.startStr + '&end=' + info.endStr)
                .then(function (r) { return r.json(); })
                .then(success)
                .catch(failure);
        },
        select: function (info) {
            openCreateModal(info.startStr, info.endStr);
        },
        eventClick: function (info) {
            openEditModal(info.event);
        },
        eventDrop: function (info) {
            saveDragOrResize(info);
        },
        eventResize: function (info) {
            saveDragOrResize(info);
        }
    });
    calendar.render();

    personSelect.addEventListener('change', function () {
        calendar.refetchEvents();
    });

    document.getElementById('agenda-nieuw-btn').addEventListener('click', function () {
        openCreateModal(null, null);
    });

    function saveDragOrResize(info) {
        var event = info.event;
        fetch('/agenda/' + event.id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ start_op: event.startStr, eind_op: event.endStr })
        })
        .then(function (r) {
            return r.json()
                .catch(function () {
                    throw new Error('Onverwacht antwoord van de server (status ' + r.status + ').');
                })
                .then(function (res) {
                    if (!r.ok || !res.success) {
                        throw new Error(res.error || 'Opslaan is mislukt.');
                    }
                });
        })
        .catch(function (err) {
            info.revert();
            window.alert('Verplaatsen/opslaan is mislukt: ' + err.message);
        });
    }

    document.getElementById('agenda-opslaan-btn').addEventListener('click', function () {
        var id = document.getElementById('agenda-id').value;
        var payload = {
            titel: document.getElementById('agenda-titel').value,
            type: document.getElementById('agenda-type').value,
            gekoppeld_id: document.getElementById('agenda-gekoppeld-id').value || null,
            user_id: document.getElementById('agenda-user-id').value,
            start_op: document.getElementById('agenda-start').value,
            eind_op: document.getElementById('agenda-eind').value,
            locatie: document.getElementById('agenda-locatie').value,
            omschrijving: document.getElementById('agenda-omschrijving').value
        };

        if (!payload.titel || !payload.start_op || !payload.eind_op) {
            window.alert('Titel, start en einde zijn verplicht.');
            return;
        }

        var url = id ? '/agenda/' + id : '/agenda';
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (res.success) {
                modal.hide();
                calendar.refetchEvents();
            } else {
                window.alert(res.error || 'Opslaan is mislukt.');
            }
        });
    });

    document.getElementById('agenda-verwijder-btn').addEventListener('click', function () {
        var id = document.getElementById('agenda-id').value;
        if (!id || !window.confirm('Deze afspraak verwijderen?')) {
            return;
        }
        fetch('/agenda/' + id + '/verwijderen', { method: 'POST' })
            .then(function (r) { return r.json(); })
            .then(function () {
                modal.hide();
                calendar.refetchEvents();
            });
    });
});
</script>
