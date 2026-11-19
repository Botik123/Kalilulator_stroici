<?php
$success = flash('admin_success');
$error = flash('admin_error');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> — СтройКалькулятор</title>
    <link rel="icon" href="<?= e(asset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin-auth-body">
<main class="admin-auth-main">
    <div class="admin-auth-brand">
        <a class="brand" href="<?= e(url('/')) ?>">
            <span class="brand-mark"><?= icon('logo') ?></span>
            <span class="brand-text"><strong>Строй</strong>Калькулятор</span>
        </a>
        <span>панель управления</span>
    </div>

    <?php if ($success): ?>
        <div class="admin-alert admin-alert--success" role="status">
            <?= icon('check') ?><span><?= e($success) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="admin-alert admin-alert--error" role="alert">
            <?= icon('info') ?><span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <?= $content ?>

    <p class="admin-auth-footer"><a href="<?= e(url('/')) ?>">← Вернуться на сайт</a></p>
</main>
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>
