<?php
/** @var array $item */
/** @var array $logs */
/** @var array $tijdregistraties */
/** @var int $tijdTotaal */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
$statussen = ['nieuw' => 'Nieuw', 'in_overweging' => 'In overweging', 'goedgekeurd' => 'Goedgekeurd', 'afgewezen' => 'Afgewezen', 'uitgevoerd' => 'Uitgevoerd'];
$statusLogs = array_values(array_filter($logs, fn ($log) => $log['status_naar'] !== null));
$opmerkingen = array_values(array_filter($logs, fn ($log) => trim($log['titel'] ?? '') !== ''));
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/verbeterpunten" style="padding:6px 10px">&larr;</a>
    <div class="page-title">#<?= $item['id'] ?> — <?= htmlspecialchars($item['titel']) ?></div>
    <?= statusBadge($item['status']) ?>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/verbeterpunten/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('verbeterpunten', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Omschrijving</span></div>
      <div class="collapsible-text" style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary);overflow-wrap:anywhere;max-height:4.5em;overflow:hidden">
        <?= nl2br(htmlspecialchars($item['omschrijving'])) ?>
      </div>
      <div class="collapsible-toggle" style="display:none;padding:0 16px 12px">
        <a href="#" class="collapsible-toggle-link" style="font-size:12px">Meer tonen</a>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Opmerkingen</span></div>

      <div style="padding:16px;border-bottom:0.5px solid var(--color-border-tertiary)">
        <form method="post" action="/verbeterpunten/<?= $item['id'] ?>/log" id="verbeterpuntLogForm">
          <div class="form-group">
            <label class="form-label">Titel</label>
            <input type="text" name="titel" placeholder="Korte titel voor deze opmerking">
          </div>
          <div class="form-group">
            <label class="form-label">Omschrijving</label>
            <textarea name="opmerking" placeholder="Beschrijf de voortgang of vraag om meer informatie..."></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Opslaan</button>
        </form>
      </div>

      <?php if (empty($opmerkingen)): ?>
        <div class="empty-state">Nog geen opmerkingen.</div>
      <?php else: ?>
        <?php foreach ($opmerkingen as $log): ?>
        <div class="log-item">
          <div class="log-meta">
            <span class="log-user"><?= htmlspecialchars($log['user_naam'] ?? 'Onbekend') ?></span>
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
      <?php endif; ?>
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Afdeling</span><span><?= htmlspecialchars($item['afdeling_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Categorie</span><span><?= htmlspecialchars($item['categorie'] ?? 'Algemeen') ?></span></div>
        <div class="meta-row"><span class="meta-key">Ingediend door</span><span><?= htmlspecialchars($item['ingediend_door_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Aangemaakt</span><span><?= formatDatum($item['created_at']) ?></span></div>
      </div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Status wijzigen</span></div>
      <div style="padding:16px">
        <!-- Hoort bij #verbeterpuntLogForm (het opmerking-formulier hierboven), via het form="" attribuut —
             zo wordt een eventueel ingevulde opmerking niet verloren wanneer je hier de status wijzigt. -->
        <div class="form-group">
          <select name="status" form="verbeterpuntLogForm" style="width:100%">
            <?php foreach ($statussen as $val => $label): ?>
              <option value="<?= $val ?>" <?= $item['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-primary" type="submit" form="verbeterpuntLogForm" style="width:100%;justify-content:center">Status bijwerken</button>
      </div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <div class="card-header">
        <span class="card-title">Tijdregistratie</span>
        <span style="font-size:12px;color:var(--color-text-tertiary)">Totaal: <?= $tijdTotaal ?> min</span>
      </div>

      <div style="padding:16px;border-bottom:0.5px solid var(--color-border-tertiary)">
        <form method="post" action="/verbeterpunten/<?= $item['id'] ?>/tijd">
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

    <div class="card">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
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
