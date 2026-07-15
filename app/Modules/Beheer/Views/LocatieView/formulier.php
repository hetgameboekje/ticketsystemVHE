<?php
/** @var array|null $locatie */
/** @var array $gebruikers */
/** @var int[] $geselecteerdeGebruikers */
/** @var array $oud */
/** @var string|null $fout */
/** @var string $actieBasis */
/** @var array $zichtbaarheidOpties */

use App\Modules\Beheer\Models\LocatieModel;

$actieBasis = $actieBasis ?? '/beheer/locaties';
$backUrl = $backUrl ?? $actieBasis;
$zichtbaarheidOpties = $zichtbaarheidOpties ?? LocatieModel::ZICHTBAARHEID_OPTIES;
$actie = $locatie === null ? $actieBasis : "{$actieBasis}/{$locatie['id']}";
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="<?= htmlspecialchars($backUrl) ?>" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= $locatie === null ? 'Nieuwe locatie' : 'Locatie bewerken' ?></div>
  </div>
</div>

<?php if ($fout): ?>
  <div class="alert alert-error"><?= htmlspecialchars($fout) ?></div>
<?php endif; ?>

<div class="card">
  <form class="new-form" method="post" action="<?= htmlspecialchars($actie) ?>">
    <div class="form-group"><label class="form-label">Naam</label><input type="text" name="naam" value="<?= htmlspecialchars($oud['naam'] ?? '') ?>" required></div>
    <div class="form-group"><label class="form-label">Adres</label><input type="text" name="adres" value="<?= htmlspecialchars($oud['adres'] ?? '') ?>"></div>
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Breedtegraad (latitude)</label><input type="text" name="latitude" value="<?= htmlspecialchars($oud['latitude'] ?? '') ?>" placeholder="bv. 52.3702157"></div>
      <div class="form-group"><label class="form-label">Lengtegraad (longitude)</label><input type="text" name="longitude" value="<?= htmlspecialchars($oud['longitude'] ?? '') ?>" placeholder="bv. 4.8951679"></div>
    </div>
    <div class="form-group">
      <label class="form-label">Zichtbaarheid</label>
      <select name="zichtbaarheid" id="zichtbaarheid-select">
        <?php foreach ($zichtbaarheidOpties as $waarde => $label): ?>
          <option value="<?= htmlspecialchars($waarde) ?>" <?= ($oud['zichtbaarheid'] ?? 'iedereen') === $waarde ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" id="gebruikers-selectie" style="display:none">
      <label class="form-label">Zichtbaar voor</label>
      <?php foreach ($gebruikers as $gebruiker): ?>
        <label style="display:flex;align-items:center;gap:8px;font-weight:400;margin-top:4px">
          <input type="checkbox" style="width:auto" name="gebruikers[]" value="<?= (int) $gebruiker['id'] ?>" <?= in_array((int) $gebruiker['id'], $geselecteerdeGebruikers, true) ? 'checked' : '' ?>>
          <?= htmlspecialchars($gebruiker['naam']) ?>
        </label>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:8px;margin-top:16px">
      <button class="btn btn-primary" type="submit">Opslaan</button>
      <a class="btn" href="<?= htmlspecialchars($backUrl) ?>">Annuleren</a>
    </div>
  </form>
</div>

<script>
(function () {
  var select = document.getElementById('zichtbaarheid-select');
  var blok = document.getElementById('gebruikers-selectie');
  function bijwerken() { blok.style.display = select.value === 'selectie' ? 'block' : 'none'; }
  select.addEventListener('change', bijwerken);
  bijwerken();
})();
</script>
