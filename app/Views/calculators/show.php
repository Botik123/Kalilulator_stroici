<?php
$pageScripts = [asset('assets/js/calc-core.js'), asset('assets/js/calculators.js')];
$inlineScript = 'window.CALC_CONFIG = ' . $calcConfig . ';';
$defaults = $calculator['defaults'];
$otherCalculators = array_filter(calculator_definitions(), static fn (string $slug): bool => $slug !== $calculator['slug'], ARRAY_FILTER_USE_KEY);
$breadcrumbs = [
    ['label' => 'Главная', 'url' => url('/')],
    ['label' => 'Калькуляторы', 'url' => url('/calculators')],
    ['label' => $calculator['full_name']],
];
$tip = match ($calculator['slug']) {
    'tile' => 'Купите плитку одной партии и оставьте 1–2 штуки про запас: оттенок в новой партии может отличаться.',
    'laminate' => 'Проверьте номер партии на всех коробках и оставьте 1–2 доски на случай ремонта.',
    'wallpaper' => 'Для обоев с крупным рисунком берите один рулон про запас — подобрать оттенок позже сложно.',
    default => 'Проверьте расчёт у продавца перед оплатой.',
};
?>
<section class="calc-hero">
    <div class="container">
        <?php require BASE_PATH . '/app/Views/partials/breadcrumbs.php'; ?>
        <div class="calc-hero-grid">
            <div class="calc-hero-copy">
                <p class="eyebrow"><span class="eyebrow-dot"></span> Онлайн-расчёт</p>
                <h1><?= e($calculator['h1']) ?></h1>
                <p class="page-lead"><?= e($calculator['lead']) ?></p>
                <ul class="calc-badges">
                    <?php foreach ($calculator['features'] as $feature): ?>
                        <li><?= icon('check') ?> <?= e($feature) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php if ($article): ?>
                <a class="hero-article-link" href="<?= e(url('/articles/' . $article['slug'])) ?>">
                    <span class="hero-article-icon"><?= icon('book') ?></span>
                    <span class="hero-article-text">
                        <small>Как мы считаем</small>
                        <strong><?= e($article['title']) ?></strong>
                        <em><?= reading_time((string) $article['content']) ?> мин чтения</em>
                    </span>
                    <?= icon('arrow-right') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="calc-section">
    <div class="container">
        <div class="calc-layout" data-calc-root data-calculator="<?= e($calculator['slug']) ?>">
            <div class="calc-main">
                <form class="calc-form" data-calc-form novalidate>
                    <div class="calc-form-head">
                        <div>
                            <h2>Данные для расчёта</h2>
                            <p>Результат пересчитывается автоматически</p>
                        </div>
                        <span class="calc-form-live"><i></i> live</span>
                    </div>

                    <?php require BASE_PATH . '/app/Views/calculators/forms/' . $calculator['slug'] . '.php'; ?>

                    <noscript>
                        <p class="noscript-note">Для мгновенного расчёта нужен JavaScript. Включите его в настройках браузера — или воспользуйтесь формулами в статье.</p>
                    </noscript>
                </form>

                <div class="calc-help-strip">
                    <span class="calc-help-icon"><?= icon('info') ?></span>
                    <p>Все поля обязательны, кроме метража упаковки. Значения можно менять — расчёт обновится без перезагрузки.</p>
                </div>
            </div>

            <aside class="calc-aside" aria-label="Результат расчёта">
                <div class="result-card" data-result-card>
                    <div class="result-top">
                        <span class="result-kicker">Результат</span>
                        <span class="result-status" data-result-status>Считаем…</span>
                    </div>

                    <div class="result-main">
                        <span class="result-value" data-result-value>—</span>
                        <span class="result-unit" data-result-unit></span>
                        <span class="result-label" data-result-label>Заполните поля</span>
                    </div>

                    <div class="result-rows" data-result-rows></div>

                    <div class="result-breakdown-wrap">
                        <h3>Детали расчёта</h3>
                        <dl class="result-breakdown" data-result-breakdown></dl>
                    </div>

                    <p class="result-note" data-result-note hidden></p>

                    <div class="result-actions">
                        <button class="btn btn--primary btn--block" type="button" data-action="save"><?= icon('save') ?> Сохранить расчёт</button>
                        <div class="result-actions-row">
                            <button class="btn btn--ghost-light" type="button" data-action="share"><?= icon('share') ?> Поделиться</button>
                            <button class="btn btn--ghost-light" type="button" data-action="reset"><?= icon('reset') ?> Сбросить</button>
                        </div>
                        <p class="result-message" data-result-message role="status" aria-live="polite"></p>
                    </div>
                </div>

                <div class="aside-tip">
                    <span class="aside-tip-icon"><?= icon('shield') ?></span>
                    <p><strong>Совет:</strong> <?= e($tip) ?></p>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="calc-explainer" id="how">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Прозрачная логика</p>
                <h2>Как мы считаем</h2>
            </div>
            <?php if ($article): ?>
                <a class="link-arrow" href="<?= e(url('/articles/' . $article['slug'])) ?>">Подробная инструкция <?= icon('arrow-right') ?></a>
            <?php endif; ?>
        </div>

        <div class="how-grid">
            <?php foreach ($calculator['how'] as $index => $step): ?>
                <article class="how-card" id="step-<?= $index + 1 ?>">
                    <span class="how-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <h3><?= e($step['title']) ?></h3>
                    <p><?= e($step['text']) ?></p>
                    <code class="formula"><?= e($step['formula']) ?></code>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="coefficients-note">
            <span class="coefficients-icon"><?= icon('settings') ?></span>
            <div>
                <strong>Текущие коэффициенты</strong>
                <p>
                    <?php
                    $coefficientParts = [];
                    foreach ($calculator['coefficients'] as $key => $value) {
                        $label = $calculator['coefficient_labels'][$key] ?? $key;
                        $unit = in_array($key, ['trim_allowance'], true) ? ' см' : (in_array($key, ['door_area', 'window_area'], true) ? ' м²' : '%');
                        $coefficientParts[] = $label . ' — ' . format_number((float) $value) . $unit;
                    }
                    echo e(implode(' · ', $coefficientParts));
                    ?>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container faq-layout">
        <div class="faq-intro">
            <p class="eyebrow">Вопросы по расчёту</p>
            <h2>Частые вопросы</h2>
            <p>Собрали то, что чаще всего уточняют перед покупкой материала.</p>
            <a class="btn btn--ghost" href="mailto:<?= e((string) config('app.email')) ?>"><?= icon('send') ?> Задать свой вопрос</a>
        </div>
        <div class="faq-list">
            <?php foreach ($calculator['faq'] as $index => $item): ?>
                <details class="faq-item"<?= $index === 0 ? ' open' : '' ?>>
                    <summary><?= e($item['question']) ?><?= icon('chevron-down') ?></summary>
                    <div class="faq-answer"><p><?= e($item['answer']) ?></p></div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($article): ?>
    <section class="section section--tight">
        <div class="container">
            <a class="article-promo" href="<?= e(url('/articles/' . $article['slug'])) ?>">
                <span class="article-promo-icon"><?= icon('book') ?></span>
                <span class="article-promo-text">
                    <small>Статья по теме</small>
                    <strong><?= e($article['title']) ?></strong>
                    <em><?= e($article['excerpt']) ?></em>
                </span>
                <span class="article-promo-cta">Читать <?= icon('arrow-right') ?></span>
            </a>
        </div>
    </section>
<?php endif; ?>

<?php if ($relatedArticles !== []): ?>
    <section class="section section--tint">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Читайте также</p>
                    <h2>Полезное по теме</h2>
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

<section class="section section--tight">
    <div class="container">
        <div class="other-calcs">
            <span>Другие калькуляторы</span>
            <?php foreach ($otherCalculators as $otherSlug => $other): ?>
                <a href="<?= e(url('/calculator/' . $other['path'])) ?>"><?= icon((string) $other['icon']) ?> <?= e($other['full_name']) ?> <?= icon('arrow-right') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
