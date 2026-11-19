<?php
$unitFor = static function (string $key): string {
    return match ($key) {
        'trim_allowance' => 'см',
        'door_area', 'window_area' => 'м²',
        default => '%',
    };
};
$maxFor = static function (string $key): float {
    return match ($key) {
        'trim_allowance' => 30,
        'door_area' => 10,
        'window_area' => 20,
        default => 50,
    };
};
?>
<div class="admin-page-head">
    <div>
        <p class="admin-eyebrow">Настройки расчётов</p>
        <h1>Коэффициенты калькуляторов</h1>
        <p>Меняйте запас на подрезку, припуск и площади проёмов без правки кода.</p>
    </div>
</div>

<div class="admin-alert admin-alert--info">
    <?= icon('info') ?>
    <span>Значения применяются сразу после сохранения: на страницах калькуляторов и в конфиге JavaScript. Рекомендуемые значения указаны в подсказках.</span>
</div>

<form action="<?= e(url('/admin/calculators')) ?>" method="post" class="admin-settings-form">
    <?= csrf_field() ?>

    <div class="admin-settings-grid">
        <?php foreach ($calculators as $slug => $calc): ?>
            <section class="admin-card admin-settings-card">
                <div class="admin-settings-head">
                    <span class="admin-settings-icon admin-settings-icon--<?= e($slug) ?>"><?= icon((string) $calc['icon']) ?></span>
                    <div>
                        <h2><?= e($calc['full_name']) ?></h2>
                        <p><?= e($calc['lead']) ?></p>
                    </div>
                </div>

                <div class="admin-settings-fields">
                    <?php foreach ($calc['current'] as $key => $value): ?>
                        <div class="admin-field">
                            <label for="coef-<?= e($slug) ?>-<?= e($key) ?>"><?= e($calc['coefficient_labels'][$key] ?? $key) ?></label>
                            <div class="admin-unit-input">
                                <input
                                    id="coef-<?= e($slug) ?>-<?= e($key) ?>"
                                    name="coefficients[<?= e($slug) ?>][<?= e($key) ?>]"
                                    type="number"
                                    min="0"
                                    max="<?= e((string) $maxFor($key)) ?>"
                                    step="0.1"
                                    value="<?= e(format_number((float) $value)) ?>"
                                    data-default="<?= e(format_number((float) $calc['coefficients'][$key])) ?>"
                                    required
                                >
                                <span><?= e($unitFor($key)) ?></span>
                            </div>
                            <p class="field-hint">По умолчанию: <?= e(format_number((float) $calc['coefficients'][$key])) ?> <?= e($unitFor($key)) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="admin-settings-foot">
                    <a class="admin-text-btn" href="<?= e(url('/calculator/' . $calc['path'])) ?>" target="_blank" rel="noopener"><?= icon('eye') ?> Открыть калькулятор</a>
                    <button class="admin-text-btn" type="button" data-coefficient-reset>Сбросить к рекомендуемым</button>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <div class="admin-save-bar">
        <div>
            <strong>Изменения вступят в силу сразу</strong>
            <span>Проверьте значения на странице калькулятора после сохранения.</span>
        </div>
        <button class="btn btn--primary" type="submit"><?= icon('save') ?> Сохранить коэффициенты</button>
    </div>
</form>
