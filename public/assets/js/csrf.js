/**
 * Voegt automatisch het CSRF-token (uit <meta name="csrf-token">) toe aan elk POST-formulier en
 * elke same-origin fetch()-call, zodat nieuwe formulieren/JS geen aparte CSRF-code nodig hebben.
 * Injecteert bij DOM-insertie (niet pas bij submit) omdat form.submit() geen submit-event vuurt.
 */
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta ? meta.content : '';
    if (!token) {
        return;
    }

    function injectInto(form) {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') {
            return;
        }
        if (form.querySelector('input[name="_csrf"]')) {
            return;
        }

        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_csrf';
        input.value = token;
        form.appendChild(input);
    }

    function injectAll(root) {
        if (root.querySelectorAll) {
            root.querySelectorAll('form').forEach(injectInto);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { injectAll(document); });
    } else {
        injectAll(document);
    }

    // Formulieren die pas na page-load worden toegevoegd (modals, dynamisch gebouwde secties).
    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) {
                    return;
                }
                if (node.matches && node.matches('form')) {
                    injectInto(node);
                }
                injectAll(node);
            });
        });
    }).observe(document.documentElement, { childList: true, subtree: true });

    // Vangnet voor formulieren die precies op het moment van submit worden samengesteld.
    document.addEventListener('submit', function (e) { injectInto(e.target); }, true);

    var originalFetch = window.fetch;
    if (originalFetch) {
        window.fetch = function (input, init) {
            init = init || {};
            var method = ((init.method || (input && input.method) || 'GET') + '').toUpperCase();

            if (method !== 'GET' && method !== 'HEAD') {
                var url = typeof input === 'string' ? input : (input && input.url) || '';
                var isAbsolute = /^https?:\/\//i.test(url);
                if (!isAbsolute || url.indexOf(window.location.origin) === 0) {
                    var headers = new Headers(init.headers || (input && input.headers) || {});
                    headers.set('X-CSRF-Token', token);
                    init.headers = headers;
                }
            }

            return originalFetch.call(this, input, init);
        };
    }
})();
