<?php
$siteName = (string) config('app.name');
$fullTitle = str_contains((string) $pageTitle, $siteName) ? (string) $pageTitle : $pageTitle . ' — ' . $siteName;
$canonicalUrl = $canonical ?: site_url(current_path());
$ogType = $ogType ?? (str_contains((string) $bodyClass, 'article-page') ? 'article' : 'website');
$pageScripts = $pageScripts ?? [];
$pageStyles = $pageStyles ?? [];
$inlineScript = $inlineScript ?? '';
$calculatorsNav = calculator_definitions();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <?php if ($noindex): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
        <meta name="robots" content="index, follow, max-image-preview:large">
    <?php endif; ?>
    <meta name="theme-color" content="#f7f5f0">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <?php if (!empty($ogImage)): ?>
        <meta property="og:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary">
    <link rel="icon" href="<?= e(asset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?= e($style) ?>">
    <?php endforeach; ?>
    <?= json_ld($jsonLd) ?>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Перейти к содержимому</a>

<header class="site-header" data-header>
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('/')) ?>" aria-label="<?= e($siteName) ?> — на главную">
            <span class="brand-mark"><?= icon('logo') ?></span>
            <span class="brand-text"><strong>Строй</strong>Калькулятор</span>
        </a>

        <nav class="site-nav" id="site-nav" data-nav aria-label="Основная навигация">
            <a class="nav-link<?= $activeNav === 'home' ? ' is-active' : '' ?>" href="<?= e(url('/')) ?>"<?= $activeNav === 'home' ? ' aria-current="page"' : '' ?>>Главная</a>
            <a class="nav-link<?= $activeNav === 'calculators' ? ' is-active' : '' ?>" href="<?= e(url('/calculators')) ?>"<?= $activeNav === 'calculators' ? ' aria-current="page"' : '' ?>>Калькуляторы</a>
            <a class="nav-link<?= $activeNav === 'articles' ? ' is-active' : '' ?>" href="<?= e(url('/articles')) ?>"<?= $activeNav === 'articles' ? ' aria-current="page"' : '' ?>>Статьи</a>
            <a class="nav-link<?= $activeNav === 'about' ? ' is-active' : '' ?>" href="<?= e(url('/about')) ?>"<?= $activeNav === 'about' ? ' aria-current="page"' : '' ?>>О проекте</a>
            <a class="btn btn--primary nav-mobile-cta" href="<?= e(url('/calculators')) ?>">Выбрать калькулятор <?= icon('arrow-right') ?></a>
        </nav>

        <div class="header-actions">
            <a class="btn btn--primary btn--sm header-cta" href="<?= e(url('/calculators')) ?>">К калькуляторам <?= icon('arrow-right') ?></a>
            <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-nav">
                <span class="sr-only">Открыть меню</span>
                <span class="nav-toggle-icon" data-icon-open><?= icon('menu') ?></span>
                <span class="nav-toggle-icon" data-icon-close><?= icon('close') ?></span>
            </button>
        </div>
    </div>
</header>

<main id="main">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a class="brand brand--footer" href="<?= e(url('/')) ?>">
                    <span class="brand-mark"><?= icon('logo') ?></span>
                    <span class="brand-text"><strong>Строй</strong>Калькулятор</span>
                </a>
                <p>Помогаем считать стройматериалы без лишних поездок в магазин и переплат. Бесплатно, без регистрации и с учётом запаса на подрезку.</p>
                <div class="footer-socials">
                    <a href="<?= e((string) config('app.telegram')) ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
                    <a href="<?= e((string) config('app.vk')) ?>" target="_blank" rel="noopener noreferrer">ВКонтакте</a>
                    <a href="mailto:<?= e((string) config('app.email')) ?>">Почта</a>
                </div>
            </div>

            <div class="footer-col">
                <h2>Калькуляторы</h2>
                <ul>
                    <?php foreach ($calculatorsNav as $navSlug => $navCalculator): ?>
                        <li><a href="<?= e(url('/calculator/' . $navCalculator['path'])) ?>"><?= e($navCalculator['full_name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h2>Материалы</h2>
                <ul>
                    <li><a href="<?= e(url('/articles')) ?>">Все статьи</a></li>
                    <?php foreach ($calculatorsNav as $navCalculator): ?>
                        <li><a href="<?= e(url('/articles/' . $navCalculator['article'])) ?>">Как рассчитать <?= e(mb_strtolower($navCalculator['name'], 'UTF-8')) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h2>Проект</h2>
                <ul>
                    <li><a href="<?= e(url('/about')) ?>">О проекте</a></li>
                    <li><a href="<?= e(url('/privacy')) ?>">Политика конфиденциальности</a></li>
                    <li><a href="<?= e(url('/admin/login')) ?>">Вход для редактора</a></li>
                </ul>
                <div class="footer-contact">
                    <span>Есть вопрос?</span>
                    <a href="mailto:<?= e((string) config('app.email')) ?>"><?= e((string) config('app.email')) ?></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> <?= e($siteName) ?>. Расчёты носят справочный характер.</p>
            <p>Сделано для тех, кто делает ремонт своими руками.</p>
        </div>
    </div>
</footer>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
<?php if ($inlineScript !== ''): ?>
    <script><?= $inlineScript ?></script>
<?php endif; ?>
<?php foreach ($pageScripts as $script): ?>
    <script src="<?= e($script) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
