<?php
$calculatorLabels = [
    'tile' => 'Плитка',
    'laminate' => 'Ламинат',
    'wallpaper' => 'Обои',
];
?>
<div class="admin-page-head">
    <div>
        <p class="admin-eyebrow">Панель управления</p>
        <h1>Обзор</h1>
        <p>Статьи, коэффициенты и последние сохранённые расчёты.</p>
    </div>
    <a class="btn btn--primary" href="<?= e(url('/admin/articles/create')) ?>"><?= icon('plus') ?> Новая статья</a>
</div>

<div class="admin-stats">
    <article class="admin-stat">
        <span class="admin-stat-icon"><?= icon('book') ?></span>
        <span class="admin-stat-value"><?= (int) $stats['articles'] ?></span>
        <span class="admin-stat-label">Всего статей</span>
    </article>
    <article class="admin-stat">
        <span class="admin-stat-icon admin-stat-icon--green"><?= icon('eye') ?></span>
        <span class="admin-stat-value"><?= (int) $stats['published'] ?></span>
        <span class="admin-stat-label">Опубликовано</span>
    </article>
    <article class="admin-stat">
        <span class="admin-stat-icon admin-stat-icon--amber"><?= icon('calculator') ?></span>
        <span class="admin-stat-value"><?= (int) $stats['calculations'] ?></span>
        <span class="admin-stat-label">Сохранённых расчётов</span>
    </article>
    <article class="admin-stat">
        <span class="admin-stat-icon admin-stat-icon--violet"><?= icon('spark') ?></span>
        <span class="admin-stat-value"><?= (int) $stats['today'] ?></span>
        <span class="admin-stat-label">Расчётов сегодня</span>
    </article>
</div>

<div class="admin-dashboard-grid">
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Последние расчёты</h2>
                <p>Анонимные сохранения с публичных страниц.</p>
            </div>
            <span class="admin-chip"><?= count($recent) ?> записей</span>
        </div>

        <?php if ($recent === []): ?>
            <div class="admin-empty">
                <span class="admin-empty-icon"><?= icon('calculator') ?></span>
                <p>Пока никто не сохранял расчёты. Кнопка «Сохранить расчёт» находится на странице каждого калькулятора.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Калькулятор</th>
                            <th>Результат</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $row): ?>
                            <tr>
                                <td><span class="admin-calc-dot admin-calc-dot--<?= e($row['calculator_slug']) ?>"></span><?= e($calculatorLabels[$row['calculator_slug']] ?? $row['calculator_slug']) ?></td>
                                <td><strong><?= e($row['summary']) ?></strong></td>
                                <td class="admin-muted"><?= e(format_date((string) $row['created_at'], true)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <aside class="admin-side-stack">
        <section class="admin-card">
            <div class="admin-card-head">
                <div><h2>Быстрые действия</h2></div>
            </div>
            <div class="admin-quick-links">
                <a href="<?= e(url('/admin/articles/create')) ?>">
                    <span class="admin-quick-icon"><?= icon('plus') ?></span>
                    <span><strong>Написать статью</strong><small>Создать новую инструкцию</small></span>
                    <?= icon('arrow-right') ?>
                </a>
                <a href="<?= e(url('/admin/calculators')) ?>">
                    <span class="admin-quick-icon"><?= icon('settings') ?></span>
                    <span><strong>Настроить коэффициенты</strong><small>Запас на подрезку и проёмы</small></span>
                    <?= icon('arrow-right') ?>
                </a>
                <a href="<?= e(url('/calculators')) ?>" target="_blank" rel="noopener">
                    <span class="admin-quick-icon"><?= icon('eye') ?></span>
                    <span><strong>Проверить калькуляторы</strong><small>Открыть публичные страницы</small></span>
                    <?= icon('arrow-right') ?>
                </a>
            </div>
        </section>

        <section class="admin-note-card">
            <span class="admin-note-icon"><?= icon('info') ?></span>
            <div>
                <strong>Коэффициенты применяются сразу</strong>
                <p>После сохранения новые значения попадут на страницы калькуляторов и в JSON-конфиг для JavaScript.</p>
            </div>
        </section>
    </aside>
</div>
