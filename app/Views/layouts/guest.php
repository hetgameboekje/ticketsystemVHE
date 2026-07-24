<?php
/** @var string $content */
/** @var string|null $pageTitle */
/** @var string $csrfToken */
?>
<!DOCTYPE html>
<html lang="nl" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Inloggen') ?> · Intranet</title>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '') ?>">
<script>
    (function () {
        var theme = localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
</script>
<script src="/assets/js/csrf.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<?= $content ?>
</body>
</html>
