<?php if (!empty($items)): ?>
    <nav class="breadcrumbs" aria-label="Хлебные крошки">
        <ol>
            <?php foreach ($items as $index => $item): ?>
                <li>
                    <?php if (!empty($item['url']) && $index < count($items) - 1): ?>
                        <a href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a>
                    <?php else: ?>
                        <span<?= $index === count($items) - 1 ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>
