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
    $letters = array_map(fn ($p) => mb_substr($p, 0, 1), array_slice($parts, 0, 2));
    return mb_strtoupper(implode('', $letters)) ?: '?';
}

$active = $activeModule ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Intranet') ?> · Intranet</title>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="navbar">
  <div class="nav-left">
    <div class="nav-brand">Intranet</div>
    <a class="nav-link<?= navActive('dashboard', $active) ?>" href="/">Dashboard</a>

    <div class="nav-dropdown">
      <span class="nav-link dropdown-toggle<?= dropdownActive(['tickets', 'verbeterpunten', 'reflecties', 'kennisbank', 'hardware'], $active) ?>">ICT</span>
      <div class="dropdown-menu">
        <a class="dropdown-item<?= navActive('tickets', $active) ?>" href="/tickets">Ticket systeem</a>
        <a class="dropdown-item<?= navActive('verbeterpunten', $active) ?>" href="/verbeterpunten">Verbeterpunten</a>
        <a class="dropdown-item<?= navActive('reflecties', $active) ?>" href="/reflecties">Reflectie</a>
        <a class="dropdown-item<?= navActive('kennisbank', $active) ?>" href="/kennisbank">Kennisbank</a>
        <a class="dropdown-item<?= navActive('hardware', $active) ?>" href="/hardware-uitgaven">Uitgaven hardware</a>
      </div>
    </div>

    <div class="nav-dropdown">
      <span class="nav-link dropdown-toggle<?= dropdownActive(['medewerkers'], $active) ?>">CRM</span>
      <div class="dropdown-menu">
        <a class="dropdown-item<?= navActive('medewerkers', $active) ?>" href="/medewerkers">Medewerkers</a>
      </div>
    </div>
  </div>

  <div class="nav-right">
    <?php if ($currentUser): ?>
      <span style="font-size:13px;color:var(--color-text-secondary)"><?= htmlspecialchars($currentUser['naam']) ?></span>
      <div class="avatar"><?= htmlspecialchars(initials($currentUser['naam'])) ?></div>
      <form method="post" action="/logout" style="margin:0">
        <button class="btn" type="submit" style="padding:5px 10px;font-size:12px">Uitloggen</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="main">
  <?= $content ?>
</div>

</body>
</html>
