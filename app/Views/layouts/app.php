<?php
/** @var string $content */
/** @var string $activeModule */
/** @var string $pageTitle */
/** @var array|null $currentUser */

function navActive(string $module, string $active): string
{
    return $module === $active ? ' active' : '';
}

function initials(string $naam): string
{
    $parts = preg_split('/\s+/', trim($naam));
    $letters = array_map(fn ($p) => mb_substr($p, 0, 1), array_slice($parts, 0, 2));
    return mb_strtoupper(implode('', $letters)) ?: '?';
}
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
  <div class="nav-brand">Intranet</div>
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

<div class="layout">
  <div class="sidebar">
    <div class="nav-section">Algemeen</div>
    <a class="nav-item<?= navActive('dashboard', $activeModule ?? '') ?>" href="/">Dashboard</a>

    <div class="nav-section">IT</div>
    <a class="nav-item<?= navActive('tickets', $activeModule ?? '') ?>" href="/tickets">Ticket systeem</a>
    <a class="nav-item<?= navActive('verbeterpunten', $activeModule ?? '') ?>" href="/verbeterpunten">Verbeterpunten</a>
    <a class="nav-item<?= navActive('reflecties', $activeModule ?? '') ?>" href="/reflecties">Reflectie</a>
    <a class="nav-item<?= navActive('kennisbank', $activeModule ?? '') ?>" href="/kennisbank">Kennisbank</a>
    <a class="nav-item<?= navActive('hardware', $activeModule ?? '') ?>" href="/hardware-uitgaven">Uitgaven hardware</a>

    <div class="nav-section">CRM</div>
    <a class="nav-item<?= navActive('medewerkers', $activeModule ?? '') ?>" href="/medewerkers">Medewerkers</a>
  </div>

  <div class="main">
    <?= $content ?>
  </div>
</div>

</body>
</html>
