<?php
$cardSlug = (string) ($calculator['slug'] ?? 'tile');
$cardTag = match ($cardSlug) {
    'tile' => 'Плитка для пола и стен',
    'laminate' => 'Ламинат и паркетная доска',
    'wallpaper' => 'Обои под покраску и с рисунком',
    default => 'Стройматериалы',
};
$cardWaste = '';
if (isset($calculator['coefficients']['straight'], $calculator['coefficients']['diagonal'])) {
    $cardWaste = 'Запас ' . format_number($calculator['coefficients']['straight'], 0) . '–' . format_number($calculator['coefficients']['diagonal'], 0) . '%';
}
?>
<article class="calc-card calc-card--<?= e($cardSlug) ?>">
    <a class="calc-card-link" href="<?= e(url('/calculator/' . $calculator['path'])) ?>">
        <div class="calc-card-visual calc-card-visual--<?= e($cardSlug) ?>">
            <span class="calc-card-icon"><?= icon((string) $calculator['icon']) ?></span>
            <span class="calc-card-tag"><?= e($cardTag) ?></span>
            <span class="calc-card-pattern" aria-hidden="true"></span>
            <span class="calc-card-waste"><?= e($cardWaste) ?></span>
        </div>
        <div class="calc-card-body">
            <h3><?= e($calculator['full_name']) ?></h3>
            <p><?= e($calculator['lead']) ?></p>
            <ul class="mini-list">
                <?php foreach (array_slice($calculator['features'], 0, 2) as $feature): ?>
                    <li><?= icon('check') ?><span><?= e($feature) ?></span></li>
                <?php endforeach; ?>
            </ul>
            <span class="calc-card-cta">Рассчитать <?= icon('arrow-right') ?></span>
        </div>
    </a>
</article>
