<?php
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="nl" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inloggen · Intranet</title>
<script>
    (function () {
        var theme = localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
</script>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<?= $content ?>
</body>
</html>
