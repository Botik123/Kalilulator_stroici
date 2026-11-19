<div class="auth-card auth-card--wide">
    <span class="auth-icon"><?= icon('shield') ?></span>
    <h1>Создание администратора</h1>
    <p class="auth-lead">Это первый запуск. Придумайте логин и пароль — они понадобятся для входа в админ-панель.</p>

    <?php if (!empty($errors['general'])): ?>
        <div class="auth-error" role="alert"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form action="<?= e(url('/admin/setup')) ?>" method="post" class="auth-form">
        <?= csrf_field() ?>
        <div class="field">
            <label for="setup-login">Логин</label>
            <div class="input-wrap">
                <input id="setup-login" name="login" type="text" value="<?= e($login ?? '') ?>" autocomplete="username" minlength="3" maxlength="50" required autofocus>
            </div>
            <?php if (!empty($errors['login'])): ?><p class="field-error"><?= e($errors['login']) ?></p><?php endif; ?>
        </div>
        <div class="field">
            <label for="setup-password">Пароль</label>
            <div class="input-wrap">
                <input id="setup-password" name="password" type="password" autocomplete="new-password" minlength="8" required>
            </div>
            <p class="field-hint">Минимум 8 символов. Используйте буквы, цифры и символы.</p>
            <?php if (!empty($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
        </div>
        <div class="field">
            <label for="setup-password-confirm">Повторите пароль</label>
            <div class="input-wrap">
                <input id="setup-password-confirm" name="password_confirm" type="password" autocomplete="new-password" minlength="8" required>
            </div>
            <?php if (!empty($errors['password_confirm'])): ?><p class="field-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
        </div>
        <button class="btn btn--primary btn--block" type="submit">Создать администратора <?= icon('arrow-right') ?></button>
    </form>

    <p class="auth-note">После создания администратора эта страница закроется. Вход — по адресу <code>/admin/login</code>.</p>
</div>
