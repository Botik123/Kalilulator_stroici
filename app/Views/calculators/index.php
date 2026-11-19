<?php
$breadcrumbs = [
    ['label' => 'Главная', 'url' => url('/')],
    ['label' => 'Калькуляторы'],
];
?>
<section class="page-hero">
    <div class="container">
        <?php require BASE_PATH . '/app/Views/partials/breadcrumbs.php'; ?>
        <div class="page-hero-grid">
            <div>
                <p class="eyebrow">Инструменты</p>
                <h1>Калькуляторы стройматериалов</h1>
                <p class="page-lead">Три инструмента для точной закупки: плитка, ламинат и обои. Считают в браузере, учитывают запас и сохраняют результат в ссылке.</p>
            </div>
            <div class="page-hero-note">
                <span class="note-icon"><?= icon('spark') ?></span>
                <p><strong>Без регистрации</strong> и без установки приложений. Откройте с телефона прямо в магазине.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
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
                <p class="eyebrow">Что внутри</p>
                <h2>У каждого материала — своя логика</h2>
            </div>
            <p class="section-lead">Мы не используем одну универсальную формулу: у плитки, ламината и обоев разные единицы закупки и разные источники отходов.</p>
        </div>

        <div class="detail-grid">
            <article class="detail-card detail-card--tile">
                <span class="detail-icon"><?= icon('tile') ?></span>
                <h3>Плитка</h3>
                <p>Площадь пола, площадь плитки и запас на подрезку. Результат — штуки, упаковки и площадь с запасом.</p>
                <ul class="check-list">
                    <li><?= icon('check') ?> Прямая, диагональ, смещение</li>
                    <li><?= icon('check') ?> Расчёт по площади или количеству</li>
                    <li><?= icon('check') ?> Метраж упаковки</li>
                </ul>
                <a class="link-arrow" href="<?= e(url('/calculator/' . $calculators['tile']['path'])) ?>">Открыть калькулятор <?= icon('arrow-right') ?></a>
            </article>

            <article class="detail-card detail-card--laminate">
                <span class="detail-icon"><?= icon('laminate') ?></span>
                <h3>Ламинат</h3>
                <p>Площадь комнаты и упаковки, размеры доски и количество досок. Результат — целые упаковки.</p>
                <ul class="check-list">
                    <li><?= icon('check') ?> Прямая, диагональ, ёлочка</li>
                    <li><?= icon('check') ?> Площадь закупки с запасом</li>
                    <li><?= icon('check') ?> Количество досок</li>
                </ul>
                <a class="link-arrow" href="<?= e(url('/calculator/' . $calculators['laminate']['path'])) ?>">Открыть калькулятор <?= icon('arrow-right') ?></a>
            </article>

            <article class="detail-card detail-card--wallpaper">
                <span class="detail-icon"><?= icon('wallpaper') ?></span>
                <h3>Обои</h3>
                <p>Периметр, высота стен, размер рулона и раппорт. Результат — полотна и рулоны.</p>
                <ul class="check-list">
                    <li><?= icon('check') ?> Учёт окон и дверей</li>
                    <li><?= icon('check') ?> Раппорт и подгонка рисунка</li>
                    <li><?= icon('check') ?> Полотна в рулоне</li>
                </ul>
                <a class="link-arrow" href="<?= e(url('/calculator/' . $calculators['wallpaper']['path'])) ?>">Открыть калькулятор <?= icon('arrow-right') ?></a>
            </article>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-band">
            <div class="cta-copy">
                <p class="eyebrow eyebrow--light">Не уверены в формуле?</p>
                <h2>Разберитесь в расчёте</h2>
                <p>В статьях — пошаговые примеры, таблицы запаса и разбор частых ошибок. Ссылки на калькуляторы внутри.</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn--light btn--lg" href="<?= e(url('/articles')) ?>"><?= icon('book') ?> Читать статьи</a>
            </div>
            <span class="cta-decor" aria-hidden="true"></span>
        </div>
    </div>
</section>
