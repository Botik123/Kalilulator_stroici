<?php
$calculatorLabels = [];
foreach (calculator_definitions() as $slug => $definition) {
    $calculatorLabels[$slug] = $definition['name'];
}
?>
<div class="admin-page-head">
    <div>
        <p class="admin-eyebrow">Контент</p>
        <h1>Статьи</h1>
        <p>Инструкции, которые помогают разобраться в формулах и подготовиться к покупке.</p>
    </div>
    <a class="btn btn--primary" href="<?= e(url('/admin/articles/create')) ?>"><?= icon('plus') ?> Новая статья</a>
</div>

<section class="admin-card">
    <?php if ($articles === []): ?>
        <div class="admin-empty">
            <span class="admin-empty-icon"><?= icon('book') ?></span>
            <h2>Статей пока нет</h2>
            <p>Создайте первую инструкцию — она появится в разделе «Статьи» и в блоке «Как мы считаем» на странице калькулятора.</p>
            <a class="btn btn--primary" href="<?= e(url('/admin/articles/create')) ?>"><?= icon('plus') ?> Создать статью</a>
        </div>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table admin-table--articles">
                <thead>
                    <tr>
                        <th>Статья</th>
                        <th>Статус</th>
                        <th>Калькулятор</th>
                        <th>Обновлена</th>
                        <th>Просмотры</th>
                        <th class="admin-table-actions-head">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $item): ?>
                        <tr>
                            <td>
                                <div class="admin-article-title">
                                    <strong><?= e($item['title']) ?></strong>
                                    <small>/articles/<?= e($item['slug']) ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="admin-status admin-status--<?= $item['status'] === 'published' ? 'published' : 'draft' ?>">
                                    <?= $item['status'] === 'published' ? 'Опубликована' : 'Черновик' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($item['calculator_slug']): ?>
                                    <span class="admin-calc-tag"><?= e($calculatorLabels[$item['calculator_slug']] ?? $item['calculator_slug']) ?></span>
                                <?php else: ?>
                                    <span class="admin-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-muted"><?= e(format_date((string) $item['updated_at'])) ?></td>
                            <td class="admin-muted"><?= number_format((int) $item['views'], 0, ',', ' ') ?></td>
                            <td>
                                <div class="admin-actions">
                                    <?php if ($item['status'] === 'published'): ?>
                                        <a class="admin-icon-btn" href="<?= e(url('/articles/' . $item['slug'])) ?>" target="_blank" rel="noopener" title="Открыть на сайте"><?= icon('eye') ?><span class="sr-only">Открыть</span></a>
                                    <?php endif; ?>
                                    <a class="admin-icon-btn" href="<?= e(url('/admin/articles/' . $item['id'] . '/edit')) ?>" title="Редактировать"><?= icon('edit') ?><span class="sr-only">Редактировать</span></a>
                                    <form action="<?= e(url('/admin/articles/' . $item['id'] . '/delete')) ?>" method="post" data-confirm="Удалить статью «<?= e($item['title']) ?>»? Действие нельзя отменить.">
                                        <?= csrf_field() ?>
                                        <button class="admin-icon-btn admin-icon-btn--danger" type="submit" title="Удалить"><?= icon('trash') ?><span class="sr-only">Удалить</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
