<?php
$breadcrumbs = [
    ['label' => 'Главная', 'url' => url('/')],
    ['label' => 'Статьи'],
];
?>
<section class="page-hero">
    <div class="container">
        <?php require BASE_PATH . '/app/Views/partials/breadcrumbs.php'; ?>
        <div class="page-hero-grid">
            <div>
                <p class="eyebrow">База знаний</p>
                <h1>Статьи и инструкции</h1>
                <p class="page-lead">Формулы, примеры и нюансы расчёта стройматериалов. Без воды: только то, что помогает купить правильное количество.</p>
            </div>
            <div class="page-hero-note">
                <span class="note-icon"><?= icon('book') ?></span>
                <p><strong><?= count($articles) ?> материала</strong> о плитке, ламинате и обоях. В каждой статье — ссылка на калькулятор.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <?php if ($articles === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('book') ?></span>
                <h2>Статьи скоро появятся</h2>
                <p>Пока воспользуйтесь калькуляторами — они уже работают.</p>
                <a class="btn btn--primary" href="<?= e(url('/calculators')) ?>">К калькуляторам <?= icon('arrow-right') ?></a>
            </div>
        <?php else: ?>
            <div class="post-grid post-grid--wide">
                <?php foreach ($articles as $article): ?>
                    <?php require BASE_PATH . '/app/Views/partials/article-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section--tint">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Не хотите читать?</p>
                <h2>Сразу к расчёту</h2>
            </div>
            <p class="section-lead">Если размеры уже под рукой, калькулятор покажет результат за пару секунд. Статьи пригодятся, чтобы проверить логику и разобраться в нюансах.</p>
        </div>
        <div class="calc-grid">
            <?php foreach (calculator_definitions() as $slug => $calculator): ?>
                <?php
                $calculator['slug'] = $slug;
                $calculator['coefficients'] = array_merge($calculator['coefficients'], calculator_settings()[$slug] ?? []);
                require BASE_PATH . '/app/Views/partials/calculator-card.php';
                ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
