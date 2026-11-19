<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Installer;
use App\Repositories\ArticleRepository;
use App\Repositories\CalculationRepository;
use Throwable;

final class AdminController
{
    public function loginForm(): string
    {
        if (Auth::check()) {
            redirect('/admin');
        }

        if (!Auth::hasUsers()) {
            redirect('/admin/setup');
        }

        return admin_auth_view('login', [
            'pageTitle' => 'Вход в админ-панель',
            'error' => flash('admin_error'),
            'login' => (string) flash('login_old', ''),
        ]);
    }

    public function login(): never
    {
        verify_csrf();

        if (Auth::check()) {
            redirect('/admin');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($this->tooManyLoginAttempts()) {
            flash('admin_error', 'Слишком много попыток входа. Подождите 10 минут.');
            flash('login_old', $login);
            redirect('/admin/login');
        }

        if ($login === '' || $password === '' || !Auth::attempt($login, $password)) {
            $attempts = $_SESSION['admin_login_attempts'] ?? [];
            $attempts[] = time();
            $_SESSION['admin_login_attempts'] = $attempts;

            flash('admin_error', 'Неверный логин или пароль.');
            flash('login_old', $login);
            redirect('/admin/login');
        }

        unset($_SESSION['admin_login_attempts']);
        flash('admin_success', 'Вы вошли в админ-панель.');
        redirect('/admin');
    }

    public function setupForm(): string
    {
        if (Auth::hasUsers()) {
            redirect('/admin/login');
        }

        return admin_auth_view('setup', [
            'pageTitle' => 'Создание администратора',
            'errors' => [],
            'login' => '',
        ]);
    }

    public function setup(): never
    {
        verify_csrf();

        if (Auth::hasUsers()) {
            redirect('/admin/login');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        $errors = [];

        if (mb_strlen($login) < 3 || mb_strlen($login) > 50) {
            $errors['login'] = 'Логин должен быть от 3 до 50 символов.';
        }

        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Пароль должен содержать минимум 8 символов.';
        }

        if ($password !== $confirm) {
            $errors['password_confirm'] = 'Пароли не совпадают.';
        }

        if ($errors !== []) {
            http_response_code(422);
            echo admin_auth_view('setup', [
                'pageTitle' => 'Создание администратора',
                'errors' => $errors,
                'login' => $login,
            ]);
            exit;
        }

        try {
            Installer::createAdmin(db(), $login, $password);
        } catch (Throwable $e) {
            http_response_code(422);
            echo admin_auth_view('setup', [
                'pageTitle' => 'Создание администратора',
                'errors' => ['general' => $e->getMessage()],
                'login' => $login,
            ]);
            exit;
        }

        flash('admin_success', 'Администратор создан. Войдите с новыми данными.');
        redirect('/admin/login');
    }

    public function logout(): never
    {
        verify_csrf();
        Auth::logout();
        flash('admin_success', 'Вы вышли из админ-панели.');
        redirect('/admin/login');
    }

    public function dashboard(): string
    {
        Auth::requireLogin();

        $articles = new ArticleRepository();
        $calculations = new CalculationRepository();

        $recent = [];
        foreach ($calculations->recent(8) as $row) {
            $row['decoded_results'] = json_decode((string) $row['results'], true) ?: [];
            $row['decoded_inputs'] = json_decode((string) $row['inputs'], true) ?: [];
            $row['summary'] = $this->calculationSummary((string) $row['calculator_slug'], $row['decoded_results']);
            $recent[] = $row;
        }

        return admin_view('dashboard', [
            'pageTitle' => 'Обзор',
            'adminSection' => 'dashboard',
            'stats' => [
                'articles' => $articles->count(),
                'published' => $articles->count('published'),
                'calculations' => $calculations->count(),
                'today' => $calculations->countSince(date('Y-m-d 00:00:00')),
            ],
            'recent' => $recent,
        ]);
    }

    public function articles(): string
    {
        Auth::requireLogin();

        return admin_view('articles', [
            'pageTitle' => 'Статьи',
            'adminSection' => 'articles',
            'articles' => (new ArticleRepository())->all(),
        ]);
    }

    public function articleCreate(): string
    {
        Auth::requireLogin();

        return admin_view('article_form', [
            'pageTitle' => 'Новая статья',
            'adminSection' => 'articles',
            'article' => $this->blankArticle(),
            'errors' => [],
            'isEdit' => false,
            'formAction' => url('/admin/articles'),
        ]);
    }

    public function articleStore(): string
    {
        Auth::requireLogin();
        verify_csrf();

        [$data, $errors] = $this->validateArticle($_POST, null);

        if ($errors !== []) {
            http_response_code(422);
            $formArticle = array_merge($this->blankArticle(), $_POST);
            $formArticle['content'] = sanitize_html((string) ($_POST['content'] ?? ''));

            return admin_view('article_form', [
                'pageTitle' => 'Новая статья',
                'adminSection' => 'articles',
                'article' => $formArticle,
                'errors' => $errors,
                'isEdit' => false,
                'formAction' => url('/admin/articles'),
            ]);
        }

        $id = (new ArticleRepository())->create($data);
        flash('admin_success', 'Статья создана. Можно продолжать редактирование.');
        redirect('/admin/articles/' . $id . '/edit');
    }

    /**
     * @param array<string, string> $params
     */
    public function articleEdit(array $params): string
    {
        Auth::requireLogin();

        $article = (new ArticleRepository())->find((int) ($params['id'] ?? 0));

        if ($article === null) {
            abort(404, 'Статья не найдена.');
        }

        return admin_view('article_form', [
            'pageTitle' => 'Редактирование статьи',
            'adminSection' => 'articles',
            'article' => $article,
            'errors' => [],
            'isEdit' => true,
            'formAction' => url('/admin/articles/' . $article['id']),
        ]);
    }

    /**
     * @param array<string, string> $params
     */
    public function articleUpdate(array $params): string
    {
        Auth::requireLogin();
        verify_csrf();

        $id = (int) ($params['id'] ?? 0);
        $repository = new ArticleRepository();
        $article = $repository->find($id);

        if ($article === null) {
            abort(404, 'Статья не найдена.');
        }

        [$data, $errors] = $this->validateArticle($_POST, $id);

        if ($errors !== []) {
            http_response_code(422);
            $formArticle = array_merge($article, $_POST);
            $formArticle['content'] = sanitize_html((string) ($_POST['content'] ?? ''));

            return admin_view('article_form', [
                'pageTitle' => 'Редактирование статьи',
                'adminSection' => 'articles',
                'article' => $formArticle,
                'errors' => $errors,
                'isEdit' => true,
                'formAction' => url('/admin/articles/' . $id),
            ]);
        }

        $repository->update($id, $data);
        flash('admin_success', 'Изменения сохранены.');
        redirect('/admin/articles/' . $id . '/edit');
    }

    /**
     * @param array<string, string> $params
     */
    public function articleDelete(array $params): never
    {
        Auth::requireLogin();
        verify_csrf();

        $repository = new ArticleRepository();
        $article = $repository->find((int) ($params['id'] ?? 0));

        if ($article === null) {
            abort(404, 'Статья не найдена.');
        }

        $repository->delete((int) $article['id']);
        flash('admin_success', 'Статья «' . $article['title'] . '» удалена.');
        redirect('/admin/articles');
    }

    public function calculators(): string
    {
        Auth::requireLogin();

        $settings = calculator_settings();
        $calculators = [];

        foreach (calculator_definitions() as $slug => $definition) {
            $definition['slug'] = $slug;
            $definition['current'] = array_merge($definition['coefficients'], $settings[$slug] ?? []);
            $calculators[$slug] = $definition;
        }

        return admin_view('calculators', [
            'pageTitle' => 'Коэффициенты калькуляторов',
            'adminSection' => 'calculators',
            'calculators' => $calculators,
        ]);
    }

    public function saveCalculators(): never
    {
        Auth::requireLogin();
        verify_csrf();

        $input = $_POST['coefficients'] ?? [];
        $definitions = calculator_definitions();
        $prepared = [];
        $errors = [];

        if (!is_array($input)) {
            flash('admin_error', 'Некорректные данные формы.');
            redirect('/admin/calculators');
        }

        $limits = [
            'trim_allowance' => 30,
            'door_area' => 10,
            'window_area' => 20,
        ];

        foreach ($definitions as $slug => $definition) {
            $prepared[$slug] = [];

            foreach ($definition['coefficients'] as $key => $default) {
                $raw = $input[$slug][$key] ?? null;
                $max = $limits[$key] ?? 50;

                if (!is_numeric($raw)) {
                    $errors[] = $definition['coefficient_labels'][$key] . ': укажите число.';
                    continue;
                }

                $value = (float) $raw;
                if ($value < 0 || $value > $max) {
                    $errors[] = $definition['coefficient_labels'][$key] . ': допустимый диапазон 0–' . $max . '.';
                    continue;
                }

                $prepared[$slug][$key] = round($value, 2);
            }
        }

        if ($errors !== []) {
            flash('admin_error', implode(' ', array_unique($errors)));
            redirect('/admin/calculators');
        }

        $repository = new \App\Repositories\CalculatorRepository();
        foreach ($prepared as $slug => $coefficients) {
            $repository->updateCoefficients($slug, $coefficients);
        }

        flash('admin_success', 'Коэффициенты обновлены. Новые значения уже используются в калькуляторах.');
        redirect('/admin/calculators');
    }

    private function tooManyLoginAttempts(): bool
    {
        $attempts = $_SESSION['admin_login_attempts'] ?? [];
        $attempts = array_values(array_filter($attempts, static fn (int $timestamp): bool => $timestamp > time() - 600));
        $_SESSION['admin_login_attempts'] = $attempts;

        return count($attempts) >= 5;
    }

    private function blankArticle(): array
    {
        return [
            'id' => null,
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'cover_image' => '',
            'meta_title' => '',
            'meta_description' => '',
            'calculator_slug' => '',
            'status' => 'draft',
            'created_at' => null,
            'updated_at' => null,
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validateArticle(array $input, ?int $id): array
    {
        $repository = new ArticleRepository();
        $errors = [];

        $title = trim((string) ($input['title'] ?? ''));
        if (mb_strlen($title) < 3) {
            $errors['title'] = 'Заголовок должен содержать минимум 3 символа.';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Заголовок не должен превышать 255 символов.';
        }

        $contentRaw = (string) ($input['content'] ?? '');
        if (mb_strlen($contentRaw) > 300000) {
            $errors['content'] = 'Слишком большой текст статьи (максимум 300 000 символов).';
        }

        $content = sanitize_html($contentRaw);
        if (trim(strip_tags($content)) === '' && !str_contains($content, '<img')) {
            $errors['content'] = 'Добавьте текст статьи.';
        }

        $slugInput = trim((string) ($input['slug'] ?? ''));
        $slug = slugify($slugInput !== '' ? $slugInput : $title);

        if (mb_strlen($slug) < 2) {
            $errors['slug'] = 'Не удалось сформировать адрес статьи. Укажите slug вручную.';
        }

        $slug = $repository->uniqueSlug($slug, $id);

        $excerpt = trim((string) ($input['excerpt'] ?? ''));
        if ($excerpt === '') {
            $excerpt = excerpt_text($content, 180);
        }
        if (mb_strlen($excerpt) > 500) {
            $errors['excerpt'] = 'Краткое описание не должно превышать 500 символов.';
        }

        $metaTitle = trim((string) ($input['meta_title'] ?? ''));
        if ($metaTitle === '') {
            $metaTitle = $title;
        }
        if (mb_strlen($metaTitle) > 255) {
            $errors['meta_title'] = 'SEO-заголовок не должен превышать 255 символов.';
        }

        $metaDescription = trim((string) ($input['meta_description'] ?? ''));
        if ($metaDescription === '') {
            $metaDescription = $excerpt;
        }
        if (mb_strlen($metaDescription) > 320) {
            $errors['meta_description'] = 'SEO-описание не должно превышать 320 символов.';
        }

        $coverImage = trim((string) ($input['cover_image'] ?? ''));
        if ($coverImage !== '') {
            if (!preg_match('#^(https?://|/)#i', $coverImage)) {
                $coverImage = '/' . ltrim($coverImage, '/');
            }
            $coverImage = sanitize_url($coverImage);
            if ($coverImage === '') {
                $errors['cover_image'] = 'Укажите корректный адрес изображения.';
            }
        }

        $status = (string) ($input['status'] ?? 'draft');
        if (!in_array($status, ['draft', 'published'], true)) {
            $status = 'draft';
        }

        $calculatorSlug = trim((string) ($input['calculator_slug'] ?? ''));
        if ($calculatorSlug !== '' && !array_key_exists($calculatorSlug, calculator_definitions())) {
            $errors['calculator_slug'] = 'Выберите калькулятор из списка.';
        }

        return [[
            'slug' => $slug,
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'cover_image' => $coverImage,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'calculator_slug' => $calculatorSlug,
            'status' => $status,
        ], $errors];
    }

    private function calculationSummary(string $slug, array $results): string
    {
        return match ($slug) {
            'tile' => isset($results['tiles'])
                ? format_number((float) $results['tiles'], 0) . ' шт.' . (isset($results['packs']) ? ' · ' . format_number((float) $results['packs'], 0) . ' упак.' : '')
                : '—',
            'laminate' => isset($results['packs'])
                ? format_number((float) $results['packs'], 0) . ' упак.'
                : '—',
            'wallpaper' => isset($results['rolls'])
                ? format_number((float) $results['rolls'], 0) . ' рул.'
                : '—',
            default => '—',
        };
    }
}
