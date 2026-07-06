<?php /** @var array $quickActionTypes */ ?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/kennisbank" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuw artikel</div>
  </div>
</div>

<form method="post" action="/kennisbank">
  <div class="card" style="margin-bottom:16px">
    <div style="padding:16px">
      <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" required></div>
      <div class="form-group"><label class="form-label">Categorie</label><input type="text" name="categorie" placeholder="bijv. Handleiding, FAQ, Beleid"></div>
      <div class="form-group"><label class="form-label">Inhoud</label><textarea name="inhoud" style="min-height:160px" required></textarea></div>

      <label style="display:flex;align-items:center;gap:6px;font-weight:normal">
        <input type="hidden" name="quick_action_enabled" value="0">
        <input type="checkbox" id="quickActionToggle" name="quick_action_enabled" value="1">
        Dit artikel bevat een script (quick action)
      </label>
    </div>
  </div>

  <div class="card" id="quickActionCard" style="margin-bottom:16px;display:none">
    <div class="card-header"><span class="card-title">Quick action</span></div>
    <div style="padding:16px">
      <div class="form-group">
        <label class="form-label">Code (script-inhoud)</label>
        <textarea name="quick_action_script" style="min-height:120px;font-family:monospace" placeholder="Script-inhoud..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Script type</label>
        <select name="quick_action_type">
          <?php foreach ($quickActionTypes as $val => $label): ?>
            <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Omschrijving (kort: welke actie voert dit uit?)</label>
        <input type="text" name="quick_action_omschrijving" placeholder="Bijv. Printerpool resetten">
      </div>
    </div>
  </div>

  <div style="display:flex;gap:8px;margin-top:8px">
    <button class="btn btn-primary" type="submit">Opslaan</button>
    <a class="btn" href="/kennisbank">Annuleren</a>
  </div>
</form>

<script>
(function () {
    var toggle = document.getElementById('quickActionToggle');
    var card = document.getElementById('quickActionCard');
    toggle.addEventListener('change', function () {
        card.style.display = toggle.checked ? '' : 'none';
    });
})();
</script>
