<?php
/** @var array $item */
/** @var array $logs */
/** @var array $tijdregistraties */
/** @var int $tijdTotaal */
/** @var array $gekoppeldeArtikelen */
/** @var array $suggestiesArtikelen */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
$statussen = ['open' => 'Open', 'in_behandeling' => 'In behandeling', 'wacht_op_info' => 'Wacht op info', 'afgehandeld' => 'Afgehandeld'];
// Eén log-rij kan zowel een opmerking als een statuswijziging bevatten (bv. je typt een opmerking en
// klikt op "Status bijwerken" i.p.v. "Opslaan") — zo'n rij hoort dus in BEIDE lijsten thuis, anders
// verdwijnt de opmerking uit beeld zodra hij samen met een statuswijziging is opgeslagen.
$statusLogs = array_values(array_filter($logs, fn ($log) => $log['status_naar'] !== null));
$opmerkingen = array_values(array_filter($logs, fn ($log) => trim($log['titel'] ?? '') !== ''));

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
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

<?php if ($flashError): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<div class="detail-layout-3col">
  <div class="area-col1">
    <div class="card" style="margin-bottom:10px">
      <div class="card-header"><span class="card-title">Omschrijving</span></div>
      <div class="collapsible-text" style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary);overflow-wrap:anywhere;max-height:4.5em;overflow:hidden">
        <?= $item['omschrijving'] !== '' ? nl2br(htmlspecialchars($item['omschrijving'])) : '<span style="color:var(--color-text-tertiary)">Geen omschrijving</span>' ?>
      </div>
      <div class="collapsible-toggle" style="display:none;padding:0 16px 12px">
        <a href="#" class="collapsible-toggle-link" style="font-size:12px">Meer tonen</a>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Opmerkingen</span></div>

      <div style="padding:16px;border-bottom:0.5px solid var(--color-border-tertiary)">
        <form method="post" action="/tickets/<?= $item['id'] ?>/log" id="ticketLogForm">
          <div class="form-group">
            <label class="form-label">Titel</label>
            <input type="text" name="titel" id="opmerkingTitel" placeholder="Korte titel voor deze opmerking">
          </div>
          <div class="form-group">
            <label class="form-label">Opmerking toevoegen</label>
            <textarea name="opmerking" id="opmerkingTekst" placeholder="Beschrijf wat je gedaan hebt of vraag om meer informatie..."></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Opslaan</button>
        </form>
      </div>

      <?php if (empty($opmerkingen)): ?>
        <div class="empty-state">Nog geen opmerkingen.</div>
      <?php else: ?>
        <div class="log-list">
        <?php foreach ($opmerkingen as $log): ?>
        <div class="log-item">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars($log['user_naam'] ?? 'ACA') ?></span>
            <span class="log-time"><?= formatDatumTijd($log['created_at']) ?></span>
          </div>
          <?php if (!empty($log['titel'])): ?>
          <div class="log-title" style="font-weight:600;margin-bottom:2px"><?= htmlspecialchars($log['titel']) ?></div>
          <?php endif; ?>
          <?php if (trim($log['opmerking'] ?? '') !== ''): ?>
          <div class="log-text"><?= nl2br(htmlspecialchars($log['opmerking'])) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="area-right">
  <div class="area-escalatie">
    <div class="card">
      <div class="card-header"><span class="card-title">Status wijzigen</span></div>
      <div style="padding:16px">
        <!-- Hoort bij #ticketLogForm (het opmerking-formulier hierboven), via het form="" attribuut —
             zo wordt een eventueel ingevulde opmerking niet verloren wanneer je hier de status wijzigt. -->
        <div class="form-group">
          <select name="status" form="ticketLogForm" style="width:100%">
            <?php foreach ($statussen as $val => $label): ?>
              <option value="<?= $val ?>" <?= $item['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-primary" type="submit" form="ticketLogForm" style="width:100%;justify-content:center">Status bijwerken</button>
      </div>

      <div class="card-header"><span class="card-title">Escalatie</span></div>
      <div style="padding:16px">
        <!-- Deelt #ticketLogForm met Opmerkingen/Status wijzigen, zodat het invullen van deze velden
             niet verloren gaat als je op een van de andere knoppen op deze pagina klikt. -->
        <div class="form-group">
          <label class="form-label">Escalatienummer</label>
          <input type="text" name="escalatie_nummer" form="ticketLogForm" value="<?= htmlspecialchars($item['escalatie_nummer'] ?? '') ?>" placeholder="Bijv. CAS-109311-L4Z5L7 - ACA:000133869">
        </div>
        <div class="form-group">
          <label class="form-label">Instantie</label>
          <input type="text" name="escalatie_instantie" form="ticketLogForm" value="<?= htmlspecialchars($item['escalatie_instantie'] ?? '') ?>" placeholder="Bijv. ACA, ClearSolutions">
        </div>
        <button class="btn btn-primary" type="submit" form="ticketLogForm">Opslaan</button>
      </div>
    </div>
  </div>

  <div class="area-tijd">
    <div class="card">
      <div class="card-header">
        <span class="card-title">Tijdregistratie</span>
        <span style="font-size:12px;color:var(--color-text-tertiary)">Totaal: <?= $tijdTotaal ?> min</span>
      </div>

      <div style="padding:16px;border-bottom:0.5px solid var(--color-border-tertiary)">
        <form method="post" action="/tickets/<?= $item['id'] ?>/tijd">
          <label class="form-label">Tijd registreren</label>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <?php foreach ([5, 10, 15, 30, 45, 60] as $blok): ?>
              <button class="btn" type="submit" name="minuten" value="<?= $blok ?>"><?= $blok ?> min</button>
            <?php endforeach; ?>
          </div>
        </form>
      </div>

      <?php if (empty($tijdregistraties)): ?>
        <div class="empty-state">Nog geen tijd geregistreerd.</div>
      <?php else: ?>
        <div class="log-list" style="max-height:190px">
        <?php foreach ($tijdregistraties as $tijd): ?>
        <div class="log-item">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars($tijd['user_naam'] ?? 'Onbekend') ?></span>
            <span class="log-time"><?= formatDatumTijd($tijd['created_at']) ?></span>
          </div>
          <div class="log-text"><?= (int) $tijd['minuten'] ?> min</div>
        </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="area-details">
    <div class="card">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Opdrachtgever</span><span><?= htmlspecialchars($item['opdrachtgever_naam']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Categorie</span><span><?= htmlspecialchars($item['categorie'] ?? 'Algemeen') ?></span></div>
        <?php if (!empty($item['is_cyberrisico'])): ?>
        <div class="meta-row"><span class="meta-key">Cyber risico</span><span><?= prioBadge('kritiek') ?></span></div>
        <?php endif; ?>
        <div class="meta-row"><span class="meta-key">Afdeling</span><span><?= htmlspecialchars($item['afdeling_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Prioriteit</span><span><?= prioBadge($item['prioriteit']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Impact</span><span><?= htmlspecialchars($item['impact']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Schatting</span><span><?= ($item['schatting_minuten'] ?? null) !== null ? $item['schatting_minuten'] . ' min' : '—' ?></span></div>
        <div class="meta-row"><span class="meta-key">Behandelaar</span><span><?= htmlspecialchars($item['behandelaar_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Datum aangemaakt</span><span><?= formatDatum($item['created_at']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Deadline</span><span><?= formatDatum($item['deadline']) ?></span></div>
      </div>
    </div>
  </div>

  <div class="area-statuslog">
    <div class="card">
      <div class="card-header"><span class="card-title">Statuslogboek</span></div>

      <?php if (empty($statusLogs)): ?>
        <div class="empty-state">Nog geen statuswijzigingen.</div>
      <?php else: ?>
        <?php foreach ($statusLogs as $log): ?>
        <div class="log-item">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars($log['user_naam'] ?? 'ACA') ?></span>
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

  <div class="area-kb">
    <div class="card">
      <div class="card-header"><span class="card-title">Gerelateerde kennisbank artikelen</span></div>

      <?php if (empty($gekoppeldeArtikelen)): ?>
        <div class="empty-state">Nog geen artikelen gekoppeld.</div>
      <?php else: ?>
        <?php foreach ($gekoppeldeArtikelen as $artikel): ?>
        <div class="log-item">
          <div class="log-meta" style="display:flex;justify-content:space-between;align-items:center;gap:8px">
            <a href="/kennisbank/<?= $artikel['id'] ?>"><?= htmlspecialchars($artikel['titel']) ?></a>
            <form method="post" action="/tickets/<?= $item['id'] ?>/kennisbank/<?= $artikel['id'] ?>/verwijderen"
                  onsubmit="return confirm('Koppeling met dit artikel verwijderen?')">
              <button class="btn btn-danger" type="submit" style="padding:2px 8px;font-size:11px">&times;</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($suggestiesArtikelen)): ?>
        <div style="padding:12px 16px;border-top:0.5px solid var(--color-border-tertiary)">
          <div style="font-size:11px;color:var(--color-text-tertiary);margin-bottom:8px">
            Suggesties (categorie "<?= htmlspecialchars($item['categorie'] ?? 'Algemeen') ?>")
          </div>
          <?php foreach ($suggestiesArtikelen as $artikel): ?>
            <form method="post" action="/tickets/<?= $item['id'] ?>/kennisbank" style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:6px">
              <a href="/kennisbank/<?= $artikel['id'] ?>"><?= htmlspecialchars($artikel['titel']) ?></a>
              <input type="hidden" name="kennisbank_artikel_id" value="<?= $artikel['id'] ?>">
              <button class="btn" type="submit" style="padding:2px 8px;font-size:11px">Koppelen</button>
            </form>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var scrollKey = 'scrollPos:' + window.location.pathname;
    var savedScroll = sessionStorage.getItem(scrollKey);
    if (savedScroll !== null) {
        sessionStorage.removeItem(scrollKey);
        window.scrollTo(0, parseInt(savedScroll, 10));
    }
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            sessionStorage.setItem(scrollKey, String(window.scrollY));
        });
    });

    var ticketLogForm = document.getElementById('ticketLogForm');
    var opmerkingTitel = document.getElementById('opmerkingTitel');
    var opmerkingTekst = document.getElementById('opmerkingTekst');
    if (ticketLogForm && opmerkingTitel && opmerkingTekst) {
        ticketLogForm.addEventListener('submit', function (e) {
            if (opmerkingTekst.value.trim() !== '' && opmerkingTitel.value.trim() === '') {
                e.preventDefault();
                opmerkingTitel.setCustomValidity('Vul een titel in om deze opmerking op te slaan.');
                opmerkingTitel.reportValidity();
                opmerkingTitel.focus();
            }
        });
        opmerkingTitel.addEventListener('input', function () {
            opmerkingTitel.setCustomValidity('');
        });
    }

    document.querySelectorAll('.collapsible-text').forEach(function (el) {
        if (el.scrollHeight <= el.clientHeight + 1) {
            return;
        }
        var toggle = el.nextElementSibling;
        var link = toggle.querySelector('.collapsible-toggle-link');
        toggle.style.display = 'block';
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var expanded = el.style.maxHeight === 'none';
            el.style.maxHeight = expanded ? '4.5em' : 'none';
            link.textContent = expanded ? 'Meer tonen' : 'Minder tonen';
        });
    });
});
</script>
