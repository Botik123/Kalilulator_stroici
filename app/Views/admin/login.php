<div class="auth-card">
    <span class="auth-icon"><?= icon('lock') ?></span>
    <h1>Вход для редактора</h1>
    <p class="auth-lead">Управление статьями и коэффициентами калькуляторов.</p>

    <?php if (!empty($error)): ?>
        <div class="auth-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="<?= e(url('/admin/login')) ?>" method="post" class="auth-form">
        <?= csrf_field() ?>
        <div class="field">
            <label for="admin-login">Логин</label>
            <div class="input-wrap">
                <input id="admin-login" name="login" type="text" value="<?= e($login ?? '') ?>" autocomplete="username" required autofocus>
            </div>
        </div>
        <div class="field">
            <label for="admin-password">Пароль</label>
            <div class="input-wrap">
                <input id="admin-password" name="password" type="password" autocomplete="current-password" required>
            </div>
        </div>
        <button class="btn btn--primary btn--block" type="submit">Войти <?= icon('arrow-right') ?></button>
    </form>

    <p class="auth-note">Забыли пароль? Замените его командой <code>php bin/install.php</code> с переменными <code>ADMIN_LOGIN</code> и <code>ADMIN_PASSWORD</code>.</p>
</div>
