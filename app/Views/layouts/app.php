<?php
/** @var string $content */
/** @var string $activeModule */
/** @var string $pageTitle */
/** @var array|null $currentUser */
/** @var array<string,bool> $navRechten */

$navRechten = $navRechten ?? [];
$magIct = array_filter(['tickets', 'verbeterpunten', 'reflecties', 'kennisbank', 'voorraad', 'uitgiften', 'apparaten', 'printers', 'cyberrisicos', 'schijfgebruik'], fn($m) => $navRechten[$m] ?? false);
$magCrm = array_filter(['medewerkers'], fn($m) => $navRechten[$m] ?? false);

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

                <?php if ($navRechten['agenda'] ?? false): ?>
                <li class="nav-item">
                    <a class="nav-link<?= navActive('agenda', $active) ?>" href="/agenda">Agenda</a>
                </li>
                <?php endif; ?>

                <?php if ($magIct): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= dropdownActive(['ict', 'tickets', 'verbeterpunten', 'reflecties', 'kennisbank', 'voorraad', 'uitgiften', 'apparaten', 'printers', 'cyberrisicos', 'schijfgebruik'], $active) ?>"
                       href="#"
                       id="ictDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        ICT
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="ictDropdown">
                        <li><a class="dropdown-item<?= navActive('ict', $active) ?>" href="/ict">Overzicht</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if ($navRechten['tickets'] ?? false): ?><li><a class="dropdown-item<?= navActive('tickets', $active) ?>" href="/tickets">Ticket systeem</a></li><?php endif; ?>
                        <?php if ($navRechten['verbeterpunten'] ?? false): ?><li><a class="dropdown-item<?= navActive('verbeterpunten', $active) ?>" href="/verbeterpunten">Verbeterpunten</a></li><?php endif; ?>
                        <?php if ($navRechten['reflecties'] ?? false): ?><li><a class="dropdown-item<?= navActive('reflecties', $active) ?>" href="/reflecties">Reflectie</a></li><?php endif; ?>
                        <?php if ($navRechten['kennisbank'] ?? false): ?><li><a class="dropdown-item<?= navActive('kennisbank', $active) ?>" href="/kennisbank">Kennisbank</a></li><?php endif; ?>
                        <?php if ($navRechten['voorraad'] ?? false): ?><li><a class="dropdown-item<?= navActive('voorraad', $active) ?>" href="/voorraad">Voorraad</a></li><?php endif; ?>
                        <?php if ($navRechten['uitgiften'] ?? false): ?><li><a class="dropdown-item<?= navActive('uitgiften', $active) ?>" href="/uitgiften">Uitgifte</a></li><?php endif; ?>
                        <?php if ($navRechten['apparaten'] ?? false): ?><li><a class="dropdown-item<?= navActive('apparaten', $active) ?>" href="/apparaten">Apparaten</a></li><?php endif; ?>
                        <?php if ($navRechten['printers'] ?? false): ?><li><a class="dropdown-item<?= navActive('printers', $active) ?>" href="/printers">Printers</a></li><?php endif; ?>
                        <?php if ($navRechten['cyberrisicos'] ?? false): ?><li><a class="dropdown-item<?= navActive('cyberrisicos', $active) ?>" href="/cyberrisicos">Cyberrisico's</a></li><?php endif; ?>
                        <?php if ($navRechten['schijfgebruik'] ?? false): ?><li><a class="dropdown-item<?= navActive('schijfgebruik', $active) ?>" href="/schijfgebruik">Schijfgebruik</a></li><?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= dropdownActive(['tools'], $active) ?>"
                       href="#"
                       id="toolsDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        Tools
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="toolsDropdown">
                        <li><a class="dropdown-item<?= navActive('tools', $active) ?>" href="/tools">Overzicht</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/tools/telefoonlijst">Telefoonlijst naar VCF</a></li>
                        <li><a class="dropdown-item" href="/tools/handtekeningen">Handtekeningen</a></li>
                    </ul>
                </li>

                <?php if ($magCrm): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= dropdownActive(['crm', 'medewerkers'], $active) ?>"
                       href="#"
                       id="crmDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        CRM
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="crmDropdown">
                        <li><a class="dropdown-item<?= navActive('crm', $active) ?>" href="/crm">Overzicht</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if ($navRechten['medewerkers'] ?? false): ?><li><a class="dropdown-item<?= navActive('medewerkers', $active) ?>" href="/medewerkers">Medewerkers</a></li><?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="themeToggleBtn" title="Donkere modus wisselen" aria-label="Donkere modus wisselen">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <?php if ($currentUser): ?>
                    <div class="dropdown">
                        <a class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="text-muted small"><?= htmlspecialchars($currentUser['naam']) ?></span>
                            <?php if (!empty($currentUser['foto'])): ?>
                                <img src="<?= htmlspecialchars($currentUser['foto']) ?>" alt="" class="avatar" style="object-fit:cover">
                            <?php else: ?>
                                <div class="avatar"><?= htmlspecialchars(initials($currentUser['naam'])) ?></div>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/account"><i class="bi bi-person me-2"></i>Mijn profiel</a></li>
                            <?php if (($currentUser['rol'] ?? '') === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/beheer/rechten"><i class="bi bi-shield-lock me-2"></i>Rechten</a></li>
                                <li><a class="dropdown-item" href="/beheer/api-sleutels"><i class="bi bi-key me-2"></i>API-sleutels</a></li>
                                <li><a class="dropdown-item" href="/beheer"><i class="bi bi-gear me-2"></i>Beheer</a></li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="#" onclick="return false"><i class="bi bi-clock-history me-2"></i>Logs</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="/beheer/log">Pagina</a></li>
                                        <li><a class="dropdown-item" href="/beheer/emails">E-mail</a></li>
                                        <li><a class="dropdown-item" href="/beheer/beveiliging">Beveiliging (logins)</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/privacybeleid"><i class="bi bi-file-earmark-text me-2"></i>Privacybeleid</a></li>
                            <li>
                                <form method="post" action="/logout" class="m-0">
                                    <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Uitloggen</button>
                                </form>
                            </li>
                        </ul>
                    </div>
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