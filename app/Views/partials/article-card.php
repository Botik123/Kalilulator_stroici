<?php
$articleCalculator = $article['calculator_slug'] ? calculator_by_slug((string) $article['calculator_slug']) : null;
$articleSlug = $articleCalculator['slug'] ?? 'article';
$articleLabel = $articleCalculator['name'] ?? 'Статья';
$articleMinutes = reading_time((string) $article['content']);
$cover = trim((string) ($article['cover_image'] ?? ''));
?>
<article class="post-card">
    <a class="post-card-link" href="<?= e(url('/articles/' . $article['slug'])) ?>">
        <div class="post-card-cover post-card-cover--<?= e($articleSlug) ?>">
            <?php if ($cover !== ''): ?>
                <img src="<?= e($cover) ?>" alt="" loading="lazy">
            <?php else: ?>
                <span class="post-card-icon"><?= icon($articleCalculator['icon'] ?? 'book') ?></span>
                <span class="post-card-pattern" aria-hidden="true"></span>
            <?php endif; ?>
            <span class="post-card-time"><?= icon('clock') ?> <?= $articleMinutes ?> мин</span>
        </div>
        <div class="post-card-body">
            <div class="post-card-meta">
                <span><?= e($articleLabel) ?></span>
                <span><?= e(format_date((string) $article['created_at'])) ?></span>
            </div>
            <h3><?= e($article['title']) ?></h3>
            <p><?= e($article['excerpt']) ?></p>
            <span class="post-card-cta">Читать статью <?= icon('arrow-right') ?></span>
        </div>
    </a>
</article>
