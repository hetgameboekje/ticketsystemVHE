<?php
/** @var array $item */
/** @var array $quickActionTypes */
$heeftQuickAction = !empty($item['quick_action_script']);
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/kennisbank/<?= $item['id'] ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Artikel #<?= $item['id'] ?> bewerken</div>
  </div>
</div>

<form method="post" action="/kennisbank/<?= $item['id'] ?>">
  <div class="card" style="margin-bottom:16px">
    <div style="padding:16px">
      <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" value="<?= htmlspecialchars($item['titel']) ?>" required></div>
      <div class="form-group"><label class="form-label">Categorie</label><input type="text" name="categorie" value="<?= htmlspecialchars($item['categorie']) ?>"></div>
      <div class="form-group"><label class="form-label">Inhoud</label><textarea name="inhoud" style="min-height:160px" required><?= htmlspecialchars($item['inhoud']) ?></textarea></div>

      <label style="display:flex;align-items:center;gap:6px;font-weight:normal">
        <input type="hidden" name="quick_action_enabled" value="0">
        <input type="checkbox" id="quickActionToggle" name="quick_action_enabled" value="1" <?= $heeftQuickAction ? 'checked' : '' ?>>
        Dit artikel bevat een script (quick action)
      </label>
    </div>
  </div>

  <div class="card" id="quickActionCard" style="margin-bottom:16px<?= $heeftQuickAction ? '' : ';display:none' ?>">
    <div class="card-header"><span class="card-title">Quick action</span></div>
    <div style="padding:16px">
      <div class="form-group">
        <label class="form-label">Code (script-inhoud)</label>
        <textarea name="quick_action_script" style="min-height:120px;font-family:monospace" placeholder="Script-inhoud..."><?= htmlspecialchars($item['quick_action_script'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Script type</label>
        <select name="quick_action_type">
          <?php foreach ($quickActionTypes as $val => $label): ?>
            <option value="<?= htmlspecialchars($val) ?>" <?= ($item['quick_action_type'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Omschrijving (kort: welke actie voert dit uit?)</label>
        <input type="text" name="quick_action_omschrijving" value="<?= htmlspecialchars($item['quick_action_omschrijving'] ?? '') ?>" placeholder="Bijv. Printerpool resetten">
      </div>
    </div>
  </div>

  <div style="display:flex;gap:8px;margin-top:8px">
    <button class="btn btn-primary" type="submit">Opslaan</button>
    <a class="btn" href="/kennisbank/<?= $item['id'] ?>">Annuleren</a>
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
