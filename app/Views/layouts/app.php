<?php
/** @var string $content */
/** @var string $activeModule */
/** @var string $pageTitle */
/** @var array|null $currentUser */

function navActive(string $module, string $active): string
{
    return $module === $active ? ' active' : '';
}

function dropdownActive(array $modules, string $active): string
{
    return in_array($active, $modules, true) ? ' active' : '';
}

function initials(string $naam): string
{
    $parts = preg_split('/\s+/', trim($naam));
    $letters = array_map(fn($p) => mb_substr($p, 0, 1), array_slice($parts, 0, 2));
    return mb_strtoupper(implode('', $letters)) ?: '?';
}

$active = $activeModule ?? '';
?>
<!DOCTYPE html>
<html lang="nl" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Intranet') ?> · Intranet</title>
    <script>
        (function () {
            var theme = localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="/">Intranet</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Navigatie openen">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <button type="button" class="nav-close d-lg-none" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-label="Navigatie sluiten">&times;</button>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link<?= navActive('dashboard', $active) ?>" href="/">Dashboard</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= dropdownActive(['tickets', 'verbeterpunten', 'reflecties', 'kennisbank', 'voorraad', 'uitgiften', 'printers', 'cyberrisicos'], $active) ?>"
                       href="#"
                       id="ictDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        ICT
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="ictDropdown">
                        <li><a class="dropdown-item<?= navActive('tickets', $active) ?>" href="/tickets">Ticket systeem</a></li>
                        <li><a class="dropdown-item<?= navActive('verbeterpunten', $active) ?>" href="/verbeterpunten">Verbeterpunten</a></li>
                        <li><a class="dropdown-item<?= navActive('reflecties', $active) ?>" href="/reflecties">Reflectie</a></li>
                        <li><a class="dropdown-item<?= navActive('kennisbank', $active) ?>" href="/kennisbank">Kennisbank</a></li>
                        <li><a class="dropdown-item<?= navActive('voorraad', $active) ?>" href="/voorraad">Voorraad</a></li>
                        <li><a class="dropdown-item<?= navActive('uitgiften', $active) ?>" href="/uitgiften">Uitgifte</a></li>
                        <li><a class="dropdown-item<?= navActive('printers', $active) ?>" href="/printers">Printers</a></li>
                        <li><a class="dropdown-item<?= navActive('cyberrisicos', $active) ?>" href="/cyberrisicos">Cyberrisico's</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= dropdownActive(['medewerkers'], $active) ?>"
                       href="#"
                       id="crmDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        CRM
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="crmDropdown">
                        <li><a class="dropdown-item<?= navActive('medewerkers', $active) ?>" href="/medewerkers">Medewerkers</a></li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="themeToggleBtn" title="Donkere modus wisselen" aria-label="Donkere modus wisselen">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <?php if ($currentUser): ?>
                    <span class="text-muted small"><?= htmlspecialchars($currentUser['naam']) ?></span>
                    <div class="avatar"><?= htmlspecialchars(initials($currentUser['naam'])) ?></div>
                    <form method="post" action="/logout" class="m-0">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Uitloggen</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="main<?= $active === 'dashboard' ? ' page-dashboard' : '' ?>">
    <?= $content ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="/assets/js/app.js"></script>
<script>
    var mainNavbar = document.getElementById('mainNavbar');
    mainNavbar.addEventListener('show.bs.collapse', function () { document.body.classList.add('nav-open'); });
    mainNavbar.addEventListener('hidden.bs.collapse', function () { document.body.classList.remove('nav-open'); });
</script>
</body>
</html>