<?php
/**
 * Sidebar-navigatie — vervangt de vorige top-navbar (zie CLAUDE.md > Frontend design direction).
 * Groepering en patroon (zoekbalk, statusbadge-telling, gebruikersblok onderaan) volgen het
 * Lovable-concept "Leen van Punt Hub" (src/components/app-sidebar.tsx).
 *
 * @var string $active
 * @var array<string,bool> $navRechten
 * @var array<string,int> $navBadges
 * @var array|null $currentUser
 */

/**
 * @param string $label
 * @param string $to
 * @param string $icon Bootstrap Icons class, bv. 'bi-ticket-perforated'
 * @param string|null $permModule sleutel in $navRechten, of null voor altijd zichtbaar
 * @param string|null $badgeKey sleutel in $navBadges
 */
function sidebarItem(string $label, string $to, string $icon, ?string $permModule = null, ?string $badgeKey = null): ?array
{
    return compact('label', 'to', 'icon', 'permModule', 'badgeKey');
}

$groups = [
    'Werkplek' => [
        sidebarItem('Dashboard', '/', 'bi-grid-1x2'),
        sidebarItem('Agenda', '/agenda', 'bi-calendar3', 'agenda'),
        sidebarItem('Account', '/account', 'bi-person-circle'),
    ],
    'Support' => [
        sidebarItem('Tickets', '/tickets', 'bi-ticket-perforated', 'tickets', 'tickets'),
        sidebarItem('E-mail verwerking', '/email-verwerking', 'bi-stars', 'email_verwerking'),
        sidebarItem('Kennisbank', '/kennisbank', 'bi-journal-bookmark', 'kennisbank'),
        sidebarItem('Verbeterpunten', '/verbeterpunten', 'bi-lightbulb', 'verbeterpunten'),
        sidebarItem('Reflectie', '/reflecties', 'bi-chat-square-quote', 'reflecties'),
    ],
    'Assets & Beheer' => [
        sidebarItem('Voorraad', '/voorraad', 'bi-boxes', 'voorraad'),
        sidebarItem('Apparaten', '/apparaten', 'bi-laptop', 'apparaten'),
        sidebarItem('Printers', '/printers', 'bi-printer', 'printers'),
        sidebarItem('Hardware-uitgaven', '/hardware-uitgaven', 'bi-box-seam', 'hardware'),
        sidebarItem('Uitgifte', '/uitgiften', 'bi-truck', 'uitgiften'),
        sidebarItem("Cyberrisico's", '/cyberrisicos', 'bi-shield-exclamation', 'cyberrisicos'),
    ],
    'HR & CRM' => [
        sidebarItem('Medewerkers', '/medewerkers', 'bi-people', 'medewerkers'),
        sidebarItem('Urenstaat', '/urenstaat', 'bi-clock-history', 'urenstaat'),
    ],
    'Systeem' => [
        sidebarItem('Tools', '/tools', 'bi-tools'),
        sidebarItem('Scripts', '/scripts', 'bi-code-slash', 'scripts'),
        sidebarItem('Schijfgebruik', '/schijfgebruik', 'bi-hdd', 'schijfgebruik'),
    ],
];

function sidebarActive(string $to, string $active): bool
{
    $map = [
        '/' => 'dashboard', '/agenda' => 'agenda', '/account' => 'account',
        '/tickets' => 'tickets', '/email-verwerking' => 'email_verwerking', '/kennisbank' => 'kennisbank',
        '/verbeterpunten' => 'verbeterpunten', '/reflecties' => 'reflecties',
        '/voorraad' => 'voorraad', '/apparaten' => 'apparaten', '/printers' => 'printers',
        '/hardware-uitgaven' => 'hardware-uitgaven', '/uitgiften' => 'uitgiften', '/cyberrisicos' => 'cyberrisicos',
        '/medewerkers' => 'medewerkers', '/urenstaat' => 'urenstaat',
        '/tools' => 'tools', '/scripts' => 'scripts', '/schijfgebruik' => 'schijfgebruik',
        '/beheer' => 'beheer',
    ];
    return ($map[$to] ?? null) === $active;
}

function initials(string $naam): string
{
    $parts = preg_split('/\s+/', trim($naam));
    $letters = array_map(fn($p) => mb_substr($p, 0, 1), array_slice($parts, 0, 2));
    return mb_strtoupper(implode('', $letters)) ?: '?';
}

$isAdmin = ($currentUser['rol'] ?? '') === 'admin';
?>
<aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <a href="/">
            <span class="sidebar-brand-mark">L</span>
            <span class="sidebar-brand-text">
                <span class="sidebar-brand-name">Leen van Punt</span>
                <span class="sidebar-brand-sub">Intranet</span>
            </span>
        </a>
        <button type="button" class="sidebar-close d-lg-none" id="sidebarCloseBtn" aria-label="Navigatie sluiten">&times;</button>
    </div>

    <div class="sidebar-search">
        <i class="bi bi-search"></i>
        <input type="text" id="sidebarSearchInput" placeholder="Zoek module&hellip;" autocomplete="off">
    </div>

    <nav class="sidebar-nav" id="sidebarNav">
        <?php foreach ($groups as $groupLabel => $items): ?>
            <?php
            $visible = array_filter($items, fn($i) => $i['permModule'] === null || ($navRechten[$i['permModule']] ?? false));
            if (empty($visible)) {
                continue;
            }
            ?>
            <div class="sidebar-group" data-sidebar-group>
                <div class="sidebar-group-label"><?= htmlspecialchars($groupLabel) ?></div>
                <ul class="sidebar-list">
                    <?php foreach ($visible as $item): ?>
                        <li data-sidebar-item data-label="<?= htmlspecialchars(strtolower($item['label'])) ?>">
                            <a href="<?= htmlspecialchars($item['to']) ?>" class="sidebar-link<?= sidebarActive($item['to'], $active) ? ' active' : '' ?>">
                                <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
                                <span class="sidebar-link-label"><?= htmlspecialchars($item['label']) ?></span>
                                <?php if ($item['badgeKey'] !== null && !empty($navBadges[$item['badgeKey']])): ?>
                                    <span class="sidebar-badge"><?= (int) $navBadges[$item['badgeKey']] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <?php if ($groupLabel === 'Systeem' && $isAdmin): ?>
                        <li data-sidebar-item data-label="beheer rechten api-sleutels locaties exporteren logs">
                            <details class="sidebar-details"<?= in_array($active, ['beheer', 'overzicht'], true) ? ' open' : '' ?>>
                                <summary class="sidebar-link<?= $active === 'beheer' ? ' active' : '' ?>">
                                    <i class="bi bi-gear"></i>
                                    <span class="sidebar-link-label">Beheer</span>
                                    <i class="bi bi-chevron-right sidebar-chevron"></i>
                                </summary>
                                <ul class="sidebar-sublist">
                                    <li><a href="/overzicht" class="sidebar-sublink<?= $active === 'overzicht' ? ' active' : '' ?>">Overzicht</a></li>
                                    <li><a href="/beheer" class="sidebar-sublink<?= $active === 'beheer' ? ' active' : '' ?>">Instellingen</a></li>
                                    <li><a href="/beheer/rechten" class="sidebar-sublink">Gebruikers &amp; rechten</a></li>
                                    <li><a href="/beheer/api-sleutels" class="sidebar-sublink">API-sleutels</a></li>
                                    <li><a href="/beheer/locaties" class="sidebar-sublink">Locaties</a></li>
                                    <li><a href="/beheer/exporteren" class="sidebar-sublink">Exporteren</a></li>
                                    <li><a href="/beheer/log" class="sidebar-sublink">Logs &mdash; pagina</a></li>
                                    <li><a href="/beheer/emails" class="sidebar-sublink">Logs &mdash; e-mail</a></li>
                                    <li><a href="/beheer/beveiliging" class="sidebar-sublink">Logs &mdash; beveiliging</a></li>
                                </ul>
                            </details>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </nav>

    <?php if ($currentUser): ?>
        <div class="sidebar-footer dropdown dropup">
            <a href="#" class="sidebar-user" id="sidebarUserToggle" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if (!empty($currentUser['foto'])): ?>
                    <img src="<?= htmlspecialchars($currentUser['foto']) ?>" alt="" class="sidebar-avatar" style="object-fit:cover">
                <?php else: ?>
                    <span class="sidebar-avatar"><?= htmlspecialchars(initials($currentUser['naam'])) ?></span>
                <?php endif; ?>
                <span class="sidebar-user-info">
                    <span class="sidebar-user-name"><?= htmlspecialchars($currentUser['naam']) ?></span>
                    <span class="sidebar-user-role"><?= htmlspecialchars(ucfirst($currentUser['rol'] ?? '')) ?></span>
                </span>
                <span class="sidebar-online-dot" title="Online"></span>
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/account"><i class="bi bi-person me-2"></i>Mijn profiel</a></li>
                <li><button type="button" class="dropdown-item" id="themeToggleBtn"><i class="bi bi-moon-stars me-2"></i>Donkere modus wisselen</button></li>
                <li><a class="dropdown-item" href="/privacybeleid"><i class="bi bi-file-earmark-text me-2"></i>Privacybeleid</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="post" action="/logout" class="m-0">
                        <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Uitloggen</button>
                    </form>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
