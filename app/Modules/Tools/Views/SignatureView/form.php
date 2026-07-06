<?php
/** @var array|null $signature */
/** @var array $icons */
/** @var array $logos */
/** @var string $previewHtml */
require_once APP_ROOT . '/app/Views/partials/ticket-helpers.php';

$isEdit = $signature !== null;
$lines = $signature['lines'] ?? [];
if (empty($lines)) {
    $lines = [['type' => 'text', 'text' => '', 'bold' => false, 'href' => '', 'icon' => '']];
}

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$terugUrl = $isEdit ? "/tools/handtekeningen/{$signature['id']}/bewerken" : '/tools/handtekeningen/nieuw';
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
    <div class="card" style="margin-bottom:16px">
      <div style="padding:16px">
        <form method="post" action="<?= $isEdit ? "/tools/handtekeningen/{$signature['id']}" : '/tools/handtekeningen' ?>" id="signatureForm">
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

    <div class="card">
      <div class="card-header"><span class="card-title">Logo's</span></div>
      <div style="padding:16px">
        <?php if (empty($logos)): ?>
          <p style="font-size:13px;color:var(--color-text-secondary);margin-top:0">
            Nog geen logo's geüpload. Upload hieronder bijvoorbeeld het logo van "VHE Automation Industry" en van "VHE | Asia" — daarna kun je ze per handtekening kiezen bij een regel van het type "Logo".
          </p>
        <?php else: ?>
          <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:16px">
            <?php foreach ($logos as $logo): ?>
              <div style="border:0.5px solid var(--color-border-secondary);border-radius:var(--border-radius-md);padding:8px;text-align:center;width:160px">
                <img src="/uploads/tools/logos/<?= htmlspecialchars($logo['bestand']) ?>" alt="<?= htmlspecialchars($logo['naam']) ?>" style="max-width:100%;max-height:60px;display:block;margin:0 auto 6px">
                <div style="font-size:12px;margin-bottom:6px"><?= htmlspecialchars($logo['naam']) ?></div>
                <form method="post" action="/tools/handtekeningen/logos/<?= (int) $logo['id'] ?>/verwijderen" onsubmit="return confirm('Logo verwijderen?')">
                  <input type="hidden" name="terug_naar" value="<?= htmlspecialchars($terugUrl) ?>">
                  <button class="btn btn-danger" type="submit" style="width:100%">Verwijderen</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="/tools/handtekeningen/logos" enctype="multipart/form-data">
          <input type="hidden" name="terug_naar" value="<?= htmlspecialchars($terugUrl) ?>">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Naam</label>
              <input type="text" name="logo_naam" placeholder="Bijv. VHE Automation Industry" required>
            </div>
            <div class="form-group">
              <label class="form-label">Breedte (px)</label>
              <input type="number" name="logo_breedte" value="200" min="20">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Bestand (PNG, JPG, GIF of SVG)</label>
            <input type="file" name="logo_bestand" accept=".png,.jpg,.jpeg,.gif,.svg" required>
          </div>
          <button class="btn btn-primary" type="submit">Logo toevoegen</button>
        </form>
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Voorbeeld</span></div>
      <div style="padding:16px">
        <div id="preview" style="border:0.5px solid var(--color-border-secondary);border-radius:var(--border-radius-md);padding:12px;background:#fff;margin-bottom:12px">
          <?= $previewHtml !== '' ? $previewHtml : '<p style="color:var(--color-text-tertiary);margin:0">Vul regels in om de preview te zien.</p>' ?>
        </div>
        <label class="form-label">HTML (kopiëren naar Outlook)</label>
        <textarea id="html-source" rows="6" readonly><?= htmlspecialchars($previewHtml) ?></textarea>
        <button type="button" id="copy-html" class="btn" style="margin-top:8px">Kopieer HTML</button>
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
    var ICON_URLS = <?= json_encode(array_map(fn (array $i) => '/assets/img/signature-icons/' . $i['file'], $icons)) ?>;
    var LOGOS = <?= json_encode(array_column(array_map(fn (array $l) => [
        'id' => (int) $l['id'],
        'bestand' => $l['bestand'],
        'breedte' => (int) $l['breedte'],
    ], $logos), null, 'id')) ?>;

    var FONT = 'font-family:Tahoma,Arial,sans-serif;font-size:10pt;color:#242424;';
    var LINK = 'color:#0b95d3;text-decoration:none;';

    var form = document.getElementById('signatureForm');
    var container = document.getElementById('line-rows');
    var addButton = document.getElementById('add-line');
    var template = document.getElementById('line-row-template');
    var preview = document.getElementById('preview');
    var htmlSource = document.getElementById('html-source');
    var nextIndex = <?= count($lines) ?>;

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    }

    function toggleFields(row) {
        var type = row.querySelector('[data-role="type"]').value;
        row.querySelector('[data-role="icon-field"]').style.display = type === 'icon' ? '' : 'none';
        row.querySelector('[data-role="logo-field"]').style.display = type === 'logo' ? '' : 'none';
        row.querySelector('[data-role="text-field"]').style.display = type === 'logo' ? 'none' : '';
        row.querySelector('[data-role="bold-field"]').style.display = type === 'logo' ? 'none' : '';
    }

    function readRow(row) {
        return {
            type: row.querySelector('[data-role="type"]').value,
            text: row.querySelector('[data-role="text"]').value,
            href: row.querySelector('[data-role="href"]').value,
            bold: row.querySelector('[data-role="bold"]').checked,
            icon: row.querySelector('[data-role="icon-select"]').value,
            logoId: row.querySelector('[data-role="logo-select"]').value,
        };
    }

    function renderLine(line) {
        if (line.type === 'logo') {
            var logo = LOGOS[line.logoId];
            if (!logo) {
                return '';
            }
            var img = '<img src="/uploads/tools/logos/' + logo.bestand + '" width="' + logo.breedte + '" alt="" style="display:block;max-width:100%;height:auto;">';
            if (line.href.trim() !== '') {
                img = '<a href="' + escapeHtml(line.href) + '">' + img + '</a>';
            }
            return '<div style="margin:0 0 6px 0;">' + img + '</div>';
        }

        var text = escapeHtml(line.text);
        if (text === '') {
            return '';
        }

        var weight = line.bold ? 'font-weight:bold;' : '';
        var inner = text;
        if (line.href.trim() !== '') {
            inner = '<a href="' + escapeHtml(line.href) + '" style="' + LINK + weight + '">' + inner + '</a>';
        }

        if (line.type === 'icon') {
            var iconUrl = ICON_URLS[line.icon] || '';
            var iconImg = iconUrl !== '' ? '<img src="' + iconUrl + '" width="14" height="14" alt="" style="display:block;">' : '';
            return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 2px 0;">'
                + '<tr><td style="padding-right:6px;vertical-align:middle;">' + iconImg + '</td>'
                + '<td style="' + FONT + weight + '">' + inner + '</td></tr></table>';
        }

        return '<p style="margin:0 0 2px 0;' + FONT + weight + '">' + inner + '</p>';
    }

    function updatePreview() {
        var html = '';
        container.querySelectorAll('.line-row').forEach(function (row) {
            html += renderLine(readRow(row));
        });

        preview.innerHTML = html !== '' ? html : '<p style="color:var(--color-text-tertiary);margin:0">Vul regels in om de preview te zien.</p>';
        htmlSource.value = html;
    }

    function bindRow(row) {
        row.querySelector('[data-role="type"]').addEventListener('change', function () {
            toggleFields(row);
            updatePreview();
        });
        row.querySelector('[data-role="remove"]').addEventListener('click', function () {
            row.remove();
            updatePreview();
        });
        row.querySelectorAll('input, select').forEach(function (field) {
            field.addEventListener('input', updatePreview);
            field.addEventListener('change', updatePreview);
        });
        toggleFields(row);
    }

    document.querySelectorAll('.line-row').forEach(bindRow);
    updatePreview();

    addButton.addEventListener('click', function () {
        var html = template.innerHTML.replace(/__INDEX__/g, nextIndex);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        var row = wrapper.firstElementChild;
        container.appendChild(row);
        bindRow(row);
        nextIndex++;
        updatePreview();
    });

    var copyButton = document.getElementById('copy-html');
    copyButton.addEventListener('click', function () {
        htmlSource.select();
        navigator.clipboard.writeText(htmlSource.value);
        copyButton.textContent = 'Gekopieerd!';
        setTimeout(function () { copyButton.textContent = 'Kopieer HTML'; }, 1500);
    });
})();
</script>
