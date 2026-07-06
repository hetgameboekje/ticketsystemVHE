<?php
/** @var array|null $signature */
/** @var array $icons */
/** @var string $previewHtml */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$isEdit = $signature !== null;
$lines = $signature['lines'] ?? [];
if (empty($lines)) {
    $lines = [['type' => 'text', 'text' => '', 'bold' => false, 'href' => '', 'icon' => '']];
}

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/tools/handtekeningen" style="padding:6px 10px">&larr;</a>
    <div class="page-title"><?= $isEdit ? 'Handtekening bewerken' : 'Nieuwe handtekening' ?></div>
  </div>
</div>

<?php if (isset($_GET['saved'])): ?>
  <div class="alert alert-success" style="margin-bottom:16px">Opgeslagen.</div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="alert alert-error" style="margin-bottom:16px"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<div class="detail-layout">
  <div>
    <div class="card">
      <div style="padding:16px">
        <form method="post" action="<?= $isEdit ? "/tools/handtekeningen/{$signature['id']}" : '/tools/handtekeningen' ?>">
          <div class="form-group">
            <label class="form-label">Naam</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($signature['name'] ?? '') ?>">
          </div>

          <label class="form-label">Regels</label>
          <div id="line-rows">
            <?php foreach ($lines as $index => $line): ?>
              <?php include __DIR__ . '/partials/line_row.php'; ?>
            <?php endforeach; ?>
          </div>

          <button type="button" id="add-line" class="btn" style="margin-bottom:16px">+ Regel toevoegen</button>

          <div style="display:flex;gap:8px">
            <button class="btn btn-primary" type="submit">Opslaan</button>
            <a class="btn" href="/tools/handtekeningen">Annuleren</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Voorbeeld</span></div>
      <div style="padding:16px">
        <div style="border:0.5px solid var(--color-border-secondary);border-radius:var(--border-radius-md);padding:12px;background:#fff;margin-bottom:12px">
          <?= $previewHtml !== '' ? $previewHtml : '<p style="color:var(--color-text-tertiary);margin:0">Sla op om de preview te zien.</p>' ?>
        </div>
        <?php if ($previewHtml !== ''): ?>
          <label class="form-label">HTML (kopiëren naar Outlook)</label>
          <textarea id="html-source" rows="6" readonly><?= htmlspecialchars($previewHtml) ?></textarea>
          <button type="button" id="copy-html" class="btn" style="margin-top:8px">Kopieer HTML</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<template id="line-row-template">
  <?php $index = '__INDEX__'; $line = ['type' => 'text', 'text' => '', 'bold' => false, 'href' => '', 'icon' => '']; ?>
  <?php include __DIR__ . '/partials/line_row.php'; ?>
</template>

<script>
(function () {
    var container = document.getElementById('line-rows');
    var addButton = document.getElementById('add-line');
    var template = document.getElementById('line-row-template');
    var nextIndex = <?= count($lines) ?>;

    function toggleIconField(row) {
        var type = row.querySelector('[data-role="type"]').value;
        var iconField = row.querySelector('[data-role="icon-field"]');
        iconField.style.display = type === 'icon' ? '' : 'none';
    }

    function bindRow(row) {
        row.querySelector('[data-role="type"]').addEventListener('change', function () {
            toggleIconField(row);
        });
        row.querySelector('[data-role="remove"]').addEventListener('click', function () {
            row.remove();
        });
        toggleIconField(row);
    }

    document.querySelectorAll('.line-row').forEach(bindRow);

    addButton.addEventListener('click', function () {
        var html = template.innerHTML.replace(/__INDEX__/g, nextIndex);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        var row = wrapper.firstElementChild;
        container.appendChild(row);
        bindRow(row);
        nextIndex++;
    });

    var copyButton = document.getElementById('copy-html');
    if (copyButton) {
        copyButton.addEventListener('click', function () {
            var source = document.getElementById('html-source');
            source.select();
            navigator.clipboard.writeText(source.value);
            copyButton.textContent = 'Gekopieerd!';
            setTimeout(function () { copyButton.textContent = 'Kopieer HTML'; }, 1500);
        });
    }
})();
</script>
