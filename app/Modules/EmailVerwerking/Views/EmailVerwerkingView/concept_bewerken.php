<?php
/** @var array $item */
/** @var array $bronnen */
/** @var bool $magSchrijven */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/email-verwerking/review" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= htmlspecialchars($item['titel']) ?></div>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <?= statusBadge($item['status']) ?>
    <?php if ($item['confidence'] !== null): ?>
      <span class="badge"><?= number_format(((float) $item['confidence']) * 100, 0) ?>% confidence</span>
    <?php endif; ?>
  </div>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>

<div class="detail-layout">
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Conceptartikel</span></div>
      <form method="post" action="/email-verwerking/concepten/<?= $item['id'] ?>" style="padding:16px">
        <div class="form-group">
          <label class="form-label">Titel</label>
          <input type="text" name="titel" value="<?= htmlspecialchars($item['titel']) ?>" <?= $magSchrijven ? '' : 'disabled' ?>>
        </div>
        <div class="form-group">
          <label class="form-label">Categorie</label>
          <input type="text" name="categorie" value="<?= htmlspecialchars($item['categorie']) ?>" <?= $magSchrijven ? '' : 'disabled' ?>>
        </div>
        <div class="form-group">
          <label class="form-label">Subcategorie</label>
          <input type="text" name="subcategorie" value="<?= htmlspecialchars($item['subcategorie'] ?? '') ?>" <?= $magSchrijven ? '' : 'disabled' ?>>
        </div>
        <div class="form-group">
          <label class="form-label">Samenvatting</label>
          <textarea name="samenvatting" <?= $magSchrijven ? '' : 'disabled' ?>><?= htmlspecialchars($item['samenvatting'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Probleem</label>
          <textarea name="probleem" <?= $magSchrijven ? '' : 'disabled' ?>><?= htmlspecialchars($item['probleem'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Oplossing</label>
          <textarea name="oplossing" <?= $magSchrijven ? '' : 'disabled' ?>><?= htmlspecialchars($item['oplossing'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Stappenplan</label>
          <textarea name="stappenplan" placeholder="Eén stap per regel..." <?= $magSchrijven ? '' : 'disabled' ?>><?= htmlspecialchars($item['stappenplan'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Tags</label>
          <input type="text" name="tags" value="<?= htmlspecialchars($item['tags'] ?? '') ?>" <?= $magSchrijven ? '' : 'disabled' ?>>
        </div>
        <?php if ($magSchrijven): ?>
          <button class="btn btn-primary" type="submit">Opslaan (zet klaar voor review)</button>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Bron-e-mails (<?= count($bronnen) ?>)</span></div>
      <?php foreach ($bronnen as $bron): ?>
        <div class="meta-row" style="display:block">
          <a href="/email-verwerking/<?= $bron['imported_email_id'] ?>" style="font-weight:600;display:block">
            <?= htmlspecialchars($bron['onderwerp']) ?>
          </a>
          <span style="font-size:11.5px;color:var(--color-text-tertiary)">
            <?= htmlspecialchars($bron['afzender_naam'] ?: $bron['afzender_email']) ?> &middot; <?= formatDatumTijd($bron['email_created_at']) ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($magSchrijven && !in_array($item['status'], ['published', 'afgewezen'], true)): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Reviewactie</span></div>
      <div style="padding:16px;display:flex;flex-direction:column;gap:8px">
        <form method="post" action="/email-verwerking/concepten/<?= $item['id'] ?>/publiceren"
              onsubmit="return confirm('Dit artikel publiceren naar de kennisbank?')">
          <button class="btn btn-primary" type="submit" style="width:100%">Publiceren naar kennisbank</button>
        </form>
        <form method="post" action="/email-verwerking/concepten/<?= $item['id'] ?>/afwijzen"
              onsubmit="return confirm('Dit conceptartikel afwijzen?')">
          <button class="btn btn-danger" type="submit" style="width:100%">Afwijzen</button>
        </form>
      </div>
    </div>
    <?php elseif ($item['status'] === 'published'): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Gepubliceerd</span></div>
      <div style="padding:16px">
        <a class="btn" href="/kennisbank/<?= $item['kennisbank_artikel_id'] ?>">Bekijk in kennisbank &rarr;</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
