<?php
/** @var array $oud */
/** @var string|null $fout */

use App\Shared\ApiKey\Models\ApiKeyModel;
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/beheer/api-sleutels" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuwe API-sleutel</div>
  </div>
</div>

<?php if ($fout): ?>
  <div class="alert alert-error"><?= htmlspecialchars($fout) ?></div>
<?php endif; ?>

<div class="card">
  <form class="new-form" method="post" action="/beheer/api-sleutels">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Naam</label>
        <input type="text" name="naam" value="<?= htmlspecialchars($oud['naam']) ?>" placeholder="bv. Outlook-intake IT@vhe.nl" required>
      </div>
    </div>
    <div class="form-group" style="margin-top:8px">
      <label class="form-label">Toegang (scopes)</label>
      <?php foreach (ApiKeyModel::SCOPES as $scope => $label): ?>
        <label style="display:flex;align-items:center;gap:8px;font-weight:400;margin-top:4px">
          <input type="checkbox" style="width:auto" name="scopes[]" value="<?= htmlspecialchars($scope) ?>" <?= in_array($scope, $oud['scopes'], true) ? 'checked' : '' ?>>
          <?= htmlspecialchars($label) ?>
        </label>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:8px;margin-top:16px">
      <button class="btn btn-primary" type="submit">Aanmaken</button>
      <a class="btn" href="/beheer/api-sleutels">Annuleren</a>
    </div>
  </form>
</div>
