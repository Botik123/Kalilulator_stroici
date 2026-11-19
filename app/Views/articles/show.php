<?php
$articleCalculator = $calculator;
$articleSlug = $articleCalculator['slug'] ?? 'article';
$articleMinutes = reading_time((string) $article['content']);
$cover = trim((string) ($article['cover_image'] ?? ''));
$breadcrumbs = [
    ['label' => 'Главная', 'url' => url('/')],
    ['label' => 'Статьи', 'url' => url('/articles')],
    ['label' => $article['title']],
];
?>
<section class="article-hero">
    <div class="container">
        <?php require BASE_PATH . '/app/Views/partials/breadcrumbs.php'; ?>
        <div class="article-head">
            <div class="article-meta">
                <?php if ($articleCalculator): ?>
                    <a class="meta-pill meta-pill--accent" href="<?= e(url('/calculator/' . $articleCalculator['path'])) ?>"><?= icon((string) $articleCalculator['icon']) ?> <?= e($articleCalculator['full_name']) ?></a>
                <?php endif; ?>
                <span class="meta-pill"><?= icon('clock') ?> <?= $articleMinutes ?> мин чтения</span>
                <span class="meta-pill"><?= icon('eye') ?> <?= number_format((int) $article['views'], 0, ',', ' ') ?></span>
            </div>
            <h1><?= e($article['title']) ?></h1>
            <p class="article-lead"><?= e($article['excerpt']) ?></p>
            <div class="article-byline">
                <span class="byline-avatar"><?= icon('logo') ?></span>
                <span><strong>Команда СтройКалькулятор</strong><small>Обновлено <?= e(format_date((string) $article['updated_at'])) ?></small></span>
                <button class="btn btn--ghost btn--sm" type="button" data-copy-link="<?= e(site_url('/articles/' . $article['slug'])) ?>"><?= icon('share') ?> Скопировать ссылку</button>
            </div>
        </div>

        <div class="article-cover article-cover--<?= e($articleSlug) ?>">
            <?php if ($cover !== ''): ?>
                <img src="<?= e($cover) ?>" alt="" loading="eager">
            <?php else: ?>
                <span class="article-cover-icon"><?= icon($articleCalculator['icon'] ?? 'book') ?></span>
                <span class="article-cover-pattern" aria-hidden="true"></span>
                <span class="article-cover-caption"><?= e($articleCalculator['name'] ?? 'Стройматериалы') ?> · расчёт без переплат</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="article-section">
    <div class="container article-layout">
        <article class="article-body">
            <div class="prose">
                <?= $articleHtml ?>
            </div>

            <div class="article-footer">
                <div class="article-author">
                    <span class="byline-avatar"><?= icon('logo') ?></span>
                    <div>
                        <strong>Материал подготовлен командой СтройКалькулятор</strong>
                        <p>Мы проверяем формулы на примерах и сверяем коэффициенты с рекомендациями производителей. Если заметили неточность — напишите нам.</p>
                    </div>
                </div>
                <a class="btn btn--ghost" href="mailto:<?= e((string) config('app.email')) ?>"><?= icon('send') ?> Сообщить о неточности</a>
            </div>
        </article>

        <aside class="article-aside">
            <?php if ($toc !== []): ?>
                <nav class="article-toc" aria-label="Содержание статьи">
                    <strong>Содержание</strong>
                    <ol>
                        <?php foreach ($toc as $item): ?>
                            <li class="toc-level-<?= (int) $item['level'] ?>"><a href="#<?= e($item['id']) ?>"><?= e($item['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <?php if ($articleCalculator): ?>
                <div class="aside-calc-cta">
                    <span class="aside-calc-icon"><?= icon((string) $articleCalculator['icon']) ?></span>
                    <h2>Посчитайте свой вариант</h2>
                    <p>Подставьте размеры комнаты — калькулятор учтёт запас и округлит до упаковок.</p>
                    <a class="btn btn--primary btn--block" href="<?= e(url('/calculator/' . $articleCalculator['path'])) ?>">Открыть калькулятор <?= icon('arrow-right') ?></a>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php if ($relatedArticles !== []): ?>
    <section class="section section--tint">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Читайте также</p>
                    <h2>Другие инструкции</h2>
                </div>
                <a class="link-arrow" href="<?= e(url('/articles')) ?>">Все статьи <?= icon('arrow-right') ?></a>
            </div>
            <div class="post-grid">
                <?php foreach ($relatedArticles as $article): ?>
                    <?php require BASE_PATH . '/app/Views/partials/article-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
