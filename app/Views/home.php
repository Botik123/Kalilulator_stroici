<?php
$tileCalculator = $calculators['tile'] ?? null;
$laminateCalculator = $calculators['laminate'] ?? null;
$wallpaperCalculator = $calculators['wallpaper'] ?? null;
?>
<section class="hero">
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="container hero-grid">
        <div class="hero-copy">
            <p class="eyebrow"><span class="eyebrow-dot"></span> Бесплатные калькуляторы стройматериалов</p>
            <h1>Считайте материалы <em>точно</em> — покупайте без лишнего</h1>
            <p class="hero-lead">Плитка, ламинат и обои: введите размеры комнаты и получите количество упаковок с учётом запаса на подрезку. Прямо в браузере, без регистрации.</p>
            <div class="hero-actions">
                <a class="btn btn--primary btn--lg" href="#calculators">Выбрать калькулятор <?= icon('arrow-right') ?></a>
                <a class="btn btn--ghost btn--lg" href="<?= e(url('/articles')) ?>"><?= icon('book') ?> Как мы считаем</a>
            </div>
            <ul class="hero-points">
                <li><?= icon('check') ?> Мгновенный результат</li>
                <li><?= icon('check') ?> Учёт запаса 5–15%</li>
                <li><?= icon('check') ?> Ссылка на расчёт</li>
            </ul>
        </div>

        <div class="hero-visual" aria-hidden="true">
            <div class="visual-board">
                <div class="visual-board-head">
                    <span class="visual-dots"><i></i><i></i><i></i></span>
                    <span>Плитка · 4,2 × 3,6 м</span>
                    <span class="visual-live"><i></i> онлайн</span>
                </div>
                <div class="visual-result">
                    <div class="visual-result-top">
                        <div>
                            <span class="visual-label">Нужно купить</span>
                            <div class="visual-value">12 <small>упаковок</small></div>
                        </div>
                        <span class="visual-badge"><?= icon('check') ?> запас учтён</span>
                    </div>
                    <div class="visual-rows">
                        <div><span>Площадь с запасом</span><strong>15,9 м²</strong></div>
                        <div><span>Плиток 30 × 30 см</span><strong>177 шт.</strong></div>
                        <div><span>Запас на подрезку</span><strong>5%</strong></div>
                    </div>
                    <div class="visual-bar"><span style="width: 72%"></span></div>
                    <div class="visual-caption">Расчёт обновляется при каждом изменении</div>
                </div>
                <div class="visual-tiles" aria-hidden="true">
                    <span></span><span></span><span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span><span></span><span></span>
                </div>
            </div>
            <div class="visual-float visual-float--formula">
                <span class="visual-float-icon"><?= icon('calculator') ?></span>
                <span><strong>4,2 × 3,6</strong><small>площадь пола</small></span>
            </div>
            <div class="visual-float visual-float--save">
                <span class="visual-float-icon"><?= icon('share') ?></span>
                <span><strong>Ссылка готова</strong><small>размеры сохранятся</small></span>
            </div>
        </div>
    </div>
</section>

<div class="trust-strip">
    <div class="container trust-inner">
        <span><?= icon('spark') ?> Три материала</span>
        <span><?= icon('calculator') ?> Точные формулы</span>
        <span><?= icon('shield') ?> Без регистрации</span>
        <span><?= icon('share') ?> Сохранение ссылкой</span>
    </div>
</div>

<section class="section" id="calculators">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Инструменты</p>
                <h2>Выберите материал</h2>
            </div>
            <p class="section-lead">Каждый калькулятор учитывает свою специфику: подрезку плитки, раскрой ламината и раппорт обоев. Результат появляется сразу.</p>
        </div>

        <div class="calc-grid">
            <?php foreach ($calculators as $calculator): ?>
                <?php require BASE_PATH . '/app/Views/partials/calculator-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tint">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Почему мы</p>
                <h2>Меньше переплат, больше уверенности</h2>
            </div>
            <p class="section-lead">Мы не продаём материалы и не зависим от магазинов. Задача одна — помочь купить ровно столько, сколько нужно.</p>
        </div>

        <div class="feature-grid">
            <article class="feature-card">
                <span class="feature-number">01</span>
                <span class="feature-icon"><?= icon('calculator') ?></span>
                <h3>Проверенные формулы</h3>
                <p>Площадь, количество полотен, упаковки — по стандартным методикам, с округлением вверх до целой упаковки.</p>
            </article>
            <article class="feature-card">
                <span class="feature-number">02</span>
                <span class="feature-icon"><?= icon('shield') ?></span>
                <h3>Запас на подрезку</h3>
                <p>5% для прямой укладки, 10–15% для диагонали и ёлочки. Коэффициенты можно настроить под свой объект.</p>
            </article>
            <article class="feature-card">
                <span class="feature-number">03</span>
                <span class="feature-icon"><?= icon('share') ?></span>
                <h3>Расчёт по ссылке</h3>
                <p>Сохраните результат и отправьте мастеру или в магазин. Все размеры останутся в адресной строке.</p>
            </article>
            <article class="feature-card">
                <span class="feature-number">04</span>
                <span class="feature-icon"><?= icon('spark') ?></span>
                <h3>Бесплатно и без регистрации</h3>
                <p>Никаких пробных периодов и скрытых платежей. Откройте с телефона прямо в магазине.</p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head section-head--center">
            <div>
                <p class="eyebrow">Как это работает</p>
                <h2>Три шага до точной закупки</h2>
            </div>
        </div>

        <ol class="steps-grid">
            <li class="step-card">
                <span class="step-index">1</span>
                <span class="step-icon"><?= icon('tile') ?></span>
                <h3>Выберите материал</h3>
                <p>Плитка, ламинат или обои. Для каждого материала — свой набор полей и формул.</p>
            </li>
            <li class="step-card">
                <span class="step-index">2</span>
                <span class="step-icon"><?= icon('edit') ?></span>
                <h3>Введите размеры</h3>
                <p>Комната, материал, схема укладки. Подсказки объяснят, что такое раппорт и метраж упаковки.</p>
            </li>
            <li class="step-card">
                <span class="step-index">3</span>
                <span class="step-icon"><?= icon('save') ?></span>
                <h3>Заберите результат</h3>
                <p>Сколько штук, упаковок или рулонов купить. Сохраните ссылку или отправьте расчёт в смету.</p>
            </li>
        </ol>
    </div>
</section>

<?php if ($articles !== []): ?>
    <section class="section section--tint">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">База знаний</p>
                    <h2>Как считаются материалы</h2>
                </div>
                <a class="link-arrow" href="<?= e(url('/articles')) ?>">Все статьи <?= icon('arrow-right') ?></a>
            </div>

            <div class="post-grid">
                <?php foreach ($articles as $article): ?>
                    <?php require BASE_PATH . '/app/Views/partials/article-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section" id="faq">
    <div class="container faq-layout">
        <div class="faq-intro">
            <p class="eyebrow">Вопросы</p>
            <h2>Коротко о главном</h2>
            <p>Если не нашли ответ, напишите нам — поможем разобраться с расчётом.</p>
            <a class="btn btn--ghost" href="mailto:<?= e((string) config('app.email')) ?>"><?= icon('send') ?> Задать вопрос</a>
        </div>
        <div class="faq-list">
            <?php foreach ($faq as $index => $item): ?>
                <details class="faq-item"<?= $index === 0 ? ' open' : '' ?>>
                    <summary><?= e($item['question']) ?><?= icon('chevron-down') ?></summary>
                    <div class="faq-answer"><p><?= e($item['answer']) ?></p></div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-band">
            <div class="cta-copy">
                <p class="eyebrow eyebrow--light">Начните с расчёта</p>
                <h2>Не покупайте «на глаз»</h2>
                <p>Одна минута с калькулятором экономит деньги и избавляет от повторной поездки в магазин.</p>
            </div>
            <div class="cta-actions">
                <?php if ($tileCalculator): ?>
                    <a class="btn btn--light btn--lg" href="<?= e(url('/calculator/' . $tileCalculator['path'])) ?>">Рассчитать плитку <?= icon('arrow-right') ?></a>
                <?php endif; ?>
                <a class="btn btn--ghost-light btn--lg" href="<?= e(url('/calculators')) ?>">Все калькуляторы</a>
            </div>
            <span class="cta-decor" aria-hidden="true"></span>
        </div>
    </div>
</section>
