/**
 * Kopieert tekst naar het klembord. Gebruikt de moderne Clipboard API wanneer
 * beschikbaar (HTTPS/localhost), anders een textarea + execCommand('copy') fallback
 * (nodig omdat deze site over HTTP draait, waar navigator.clipboard niet bestaat).
 * @param {string} text
 * @returns {Promise<void>}
 */
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    }

    return new Promise(function (resolve, reject) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '0';
        ta.style.left = '0';
        ta.style.opacity = '0';
        document.body.appendChild(ta);

        ta.focus();
        ta.select();
        ta.setSelectionRange(0, text.length); // nodig voor o.a. mobiele Safari

        var successful = false;
        try {
            successful = document.execCommand('copy');
        } catch (err) {
            successful = false;
        }

        document.body.removeChild(ta);

        if (successful) {
            resolve();
        } else {
            reject(new Error('execCommand(copy) is mislukt'));
        }
    });
}

/**
 * Toont tijdelijke visuele feedback op een copy-knop en herstelt de originele inhoud.
 * @param {Element} el
 * @param {boolean} success
 */
function showCopyFeedback(el, success) {
    if (el.dataset.originalHtml === undefined) {
        el.dataset.originalHtml = el.innerHTML;
    }
    var original = el.dataset.originalHtml;

    el.innerHTML = success
        ? '<i class="bi bi-check2"></i> Gekopieerd!'
        : '<i class="bi bi-x-lg"></i> Mislukt';
    el.classList.add(success ? 'btn-success' : 'btn-danger');

    setTimeout(function () {
        el.innerHTML = original;
        el.classList.remove('btn-success', 'btn-danger');
    }, 1500);
}

// Herbruikbare "kopieer naar klembord"-knop: <button class="js-copy-btn" data-command="...">
// Event delegation op document zodat dit ook werkt voor rows die na page-load
// toegevoegd worden (bijv. via AJAX-herrender van een tabel).
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-copy-btn');
    if (!btn) {
        return;
    }

    if (btn.disabled || btn.getAttribute('aria-disabled') === 'true') {
        return;
    }

    var text = btn.dataset.command;
    if (!text) {
        showCopyFeedback(btn, false);
        return;
    }

    copyToClipboard(text)
        .then(function () {
            showCopyFeedback(btn, true);
        })
        .catch(function () {
            window.prompt('Kopieer handmatig (Ctrl+C):', text);
            showCopyFeedback(btn, false);
        });
});
