<?php
/** @var array $item */
/** @var array $logs */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/kennisbank" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['titel']) ?></div>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn" href="/kennisbank/<?= $item['id'] ?>/edit">Bewerken</a>
    <?= deleteButton('kennisbank', $item['id']) ?>
  </div>
</div>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">Inhoud</span></div>
      <?php if (!empty($item['samenvatting'])): ?>
        <div style="padding:12px 16px 0;font-size:13px;color:var(--color-text-secondary)"><?= htmlspecialchars($item['samenvatting']) ?></div>
      <?php endif; ?>
      <?php $tags = \App\Modules\Kennisbank\Models\KennisbankModel::splitTags($item['tags'] ?? null); ?>
      <?php if (!empty($tags)): ?>
        <div style="padding:10px 16px 0;display:flex;flex-wrap:wrap;gap:4px">
          <?php foreach ($tags as $tag): ?>
            <a href="/kennisbank?tag=<?= urlencode($tag) ?>" class="badge" style="background:var(--color-background-tertiary);color:var(--color-text-secondary);font-weight:400;text-decoration:none"><?= htmlspecialchars($tag) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="collapsible-text" style="padding:16px;font-size:13px;line-height:1.7;color:var(--color-text-secondary);max-height:4.5em;overflow:hidden">
        <?= nl2br(htmlspecialchars($item['inhoud'])) ?>
      </div>
      <div class="collapsible-toggle" style="display:none;padding:0 16px 12px">
        <a href="#" class="collapsible-toggle-link" style="font-size:12px">Meer tonen</a>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Opmerkingen</span></div>

      <div style="padding:16px;border-bottom:0.5px solid var(--color-border-tertiary)">
        <form method="post" action="/kennisbank/<?= $item['id'] ?>/log">
          <div class="form-group">
            <label class="form-label">Titel</label>
            <input type="text" name="titel" placeholder="Korte titel voor deze opmerking">
          </div>
          <div class="form-group">
            <label class="form-label">Omschrijving</label>
            <textarea name="omschrijving" placeholder="Opmerking..."></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Opslaan</button>
        </form>
      </div>

      <?php if (empty($logs)): ?>
        <div class="empty-state">Nog geen opmerkingen.</div>
      <?php else: ?>
        <div style="font-size:12px;color:var(--color-text-tertiary);padding:8px 16px 0">Sleep aan <i class="bi bi-grip-vertical"></i> om de volgorde aan te passen.</div>
        <div id="opmerkingenList">
          <?php foreach ($logs as $log): ?>
          <div class="log-item" data-id="<?= (int) $log['id'] ?>">
            <div class="log-meta">
              <span class="log-drag-handle" style="color:var(--color-text-tertiary);cursor:grab"><i class="bi bi-grip-vertical"></i></span>
              <span class="log-user"><?= htmlspecialchars($log['user_naam'] ?? 'Onbekend') ?></span>
              <span class="log-time"><?= formatDatumTijd($log['created_at']) ?></span>
            </div>
            <div class="log-title" style="font-weight:600;margin-bottom:2px"><?= htmlspecialchars($log['titel']) ?></div>
            <div class="log-text"><?= nl2br(htmlspecialchars($log['omschrijving'])) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Details</span></div>
      <div style="padding:0 16px">
        <div class="meta-row"><span class="meta-key">Categorie</span><span><a href="/kennisbank?categorie=<?= urlencode($item['categorie']) ?>"><?= htmlspecialchars($item['categorie']) ?></a></span></div>
        <?php if (!empty($item['subcategorie'])): ?>
        <div class="meta-row"><span class="meta-key">Subcategorie</span><span><a href="/kennisbank?categorie=<?= urlencode($item['categorie']) ?>&subcategorie=<?= urlencode($item['subcategorie']) ?>"><?= htmlspecialchars($item['subcategorie']) ?></a></span></div>
        <?php endif; ?>
        <div class="meta-row"><span class="meta-key">Auteur</span><span><?= htmlspecialchars($item['auteur_naam'] ?? '—') ?></span></div>
        <div class="meta-row"><span class="meta-key">Aangemaakt</span><span><?= formatDatum($item['created_at']) ?></span></div>
      </div>
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

<script>
(function () {
    var list = document.getElementById('opmerkingenList');
    if (!list) {
        return;
    }

    var dragging = null;

    list.addEventListener('mousedown', function (e) {
        var handle = e.target.closest('.log-drag-handle');
        if (!handle) {
            return;
        }
        var item = handle.closest('.log-item');
        if (item) {
            item.setAttribute('draggable', 'true');
        }
    });

    list.addEventListener('mouseup', function () {
        list.querySelectorAll('.log-item[draggable]').forEach(function (item) {
            item.removeAttribute('draggable');
        });
    });

    list.addEventListener('dragstart', function (e) {
        var item = e.target.closest('.log-item');
        if (!item) {
            return;
        }
        dragging = item;
        item.style.opacity = '0.5';
    });

    list.addEventListener('dragend', function (e) {
        var item = e.target.closest('.log-item');
        if (item) {
            item.style.opacity = '';
        }
        dragging = null;
        saveOrder();
    });

    list.addEventListener('dragover', function (e) {
        e.preventDefault();
        var target = e.target.closest('.log-item');
        if (!target || target === dragging || !dragging) {
            return;
        }
        var rect = target.getBoundingClientRect();
        var before = (e.clientY - rect.top) < rect.height / 2;
        list.insertBefore(dragging, before ? target : target.nextSibling);
    });

    function saveOrder() {
        var ids = Array.prototype.map.call(list.querySelectorAll('.log-item'), function (el) {
            return parseInt(el.getAttribute('data-id'), 10);
        });

        fetch('/kennisbank/<?= (int) $item['id'] ?>/log/volgorde', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order: ids }),
        });
    }
})();
</script>

