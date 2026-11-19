<?php
$success = flash('admin_success');
$error = flash('admin_error');
$adminName = $adminUser['login'] ?? 'admin';
$navItems = [
    ['key' => 'dashboard', 'label' => 'Обзор', 'url' => url('/admin'), 'icon' => 'home'],
    ['key' => 'articles', 'label' => 'Статьи', 'url' => url('/admin/articles'), 'icon' => 'book'],
    ['key' => 'calculators', 'label' => 'Коэффициенты', 'url' => url('/admin/calculators'), 'icon' => 'settings'],
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> — Админ-панель</title>
    <link rel="icon" href="<?= e(asset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" data-admin-sidebar>
        <div class="admin-sidebar-head">
            <a class="brand brand--admin" href="<?= e(url('/admin')) ?>">
                <span class="brand-mark"><?= icon('logo') ?></span>
                <span class="brand-text"><strong>Строй</strong>Калькулятор</span>
            </a>
            <span class="admin-badge">админка</span>
        </div>

        <nav class="admin-nav" aria-label="Навигация админ-панели">
            <?php foreach ($navItems as $item): ?>
                <a class="admin-nav-link<?= $adminSection === $item['key'] ? ' is-active' : '' ?>" href="<?= e($item['url']) ?>"<?= $adminSection === $item['key'] ? ' aria-current="page"' : '' ?>>
                    <?= icon($item['icon']) ?>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
            <a class="admin-nav-link" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">
                <?= icon('eye') ?>
                <span>Открыть сайт</span>
            </a>
        </nav>

        <div class="admin-sidebar-foot">
            <div class="admin-user">
                <span class="admin-avatar"><?= icon('user') ?></span>
                <span><strong><?= e($adminName) ?></strong><small>редактор</small></span>
            </div>
            <form action="<?= e(url('/admin/logout')) ?>" method="post">
                <?= csrf_field() ?>
                <button class="admin-logout" type="submit"><?= icon('logout') ?> Выйти</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-menu-toggle" type="button" data-admin-nav-toggle aria-expanded="false" aria-controls="admin-sidebar">
                <?= icon('menu') ?>
                <span class="sr-only">Открыть меню</span>
            </button>
            <span class="admin-topbar-title"><?= e($pageTitle) ?></span>
            <a class="admin-topbar-link" href="<?= e(url('/')) ?>" target="_blank" rel="noopener"><?= icon('eye') ?> На сайт</a>
        </header>

        <main class="admin-content">
            <?php if ($success): ?>
                <div class="admin-alert admin-alert--success" role="status">
                    <?= icon('check') ?>
                    <span><?= e($success) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="admin-alert admin-alert--error" role="alert">
                    <?= icon('info') ?>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<div class="admin-overlay" data-admin-overlay hidden></div>

<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>
