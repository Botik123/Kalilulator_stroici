<section class="error-page">
    <div class="container error-inner">
        <div class="error-code"><?= e($errorCode) ?></div>
        <h1><?= $errorCode === 404 ? 'Страница не нашлась' : 'Что-то пошло не так' ?></h1>
        <p><?= e($errorMessage) ?></p>
        <?php if (!empty($errorDetails)): ?>
            <pre class="error-details"><?= e($errorDetails) ?></pre>
        <?php endif; ?>
        <div class="error-actions">
            <a class="btn btn--primary btn--lg" href="<?= e(url('/')) ?>"><?= icon('home') ?> На главную</a>
            <a class="btn btn--ghost btn--lg" href="<?= e(url('/calculators')) ?>">К калькуляторам</a>
        </div>
    </div>
</section>
