<?php
declare(strict_types=1);

function config(?string $key = null, mixed $default = null): mixed
{
    $config = $GLOBALS['app_config'] ?? [];

    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function db(): PDO
{
    return App\Core\Database::pdo();
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    return (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
}

function base_path(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = str_replace(chr(92), '/', dirname($script));
    $dir = rtrim($dir, '/.');

    $base = ($dir === '' || $dir === '/') ? '' : '/' . trim($dir, '/');

    return $base;
}

function url(string $path = ''): string
{
    if ($path === '') {
        return base_path() . '/';
    }

    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'mailto:') || str_starts_with($path, 'tel:') || str_starts_with($path, '#')) {
        return $path;
    }

    return base_path() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $relative = ltrim($path, '/');
    $file = BASE_PATH . '/' . $relative;
    $version = is_file($file) ? (string) filemtime($file) : '1';

    return url($relative) . '?v=' . $version;
}

function site_url(string $path = ''): string
{
    $configured = (string) config('app.url', '');
    if ($configured !== '') {
        return rtrim($configured, '/') . ($path === '' ? '/' : '/' . ltrim($path, '/'));
    }

    $scheme = is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . url($path);
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_path();

    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }

    $path = '/' . trim(rawurldecode($path), '/');

    return $path === '//' ? '/' : $path;
}

function is_active(string $prefix): bool
{
    $path = current_path();

    if ($prefix === '/') {
        return $path === '/';
    }

    return $path === $prefix || str_starts_with($path, $prefix . '/');
}

function redirect(string $path, int $status = 302): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

    if (!is_string($token) || empty($_SESSION['_csrf']) || !hash_equals((string) $_SESSION['_csrf'], $token)) {
        abort(419, 'Сессия истекла. Обновите страницу и попробуйте снова.');
    }
}

function flash(string $key, mixed $value = null): mixed
{
    if (func_num_args() === 2) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $stored = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $stored;
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);

    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || str_starts_with($_SERVER['REQUEST_URI'] ?? '', url('/api/'))) {
        json_response(['error' => $message], $code);
    }

    $titles = [
        403 => 'Доступ запрещён',
        404 => 'Страница не найдена',
        405 => 'Метод не поддерживается',
        419 => 'Сессия истекла',
        500 => 'Ошибка сервера',
    ];

    echo view('errors/error', [
        'pageTitle' => $titles[$code] ?? 'Ошибка',
        'metaDescription' => $message,
        'errorCode' => $code,
        'errorMessage' => $message,
        'errorDetails' => null,
        'activeNav' => '',
    ]);

    exit;
}

function view(string $template, array $data = []): string
{
    $file = BASE_PATH . '/app/Views/' . $template . '.php';

    if (!is_file($file)) {
        throw new RuntimeException('View not found: ' . $template);
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $file;
    $content = (string) ob_get_clean();

    $pageTitle = $pageTitle ?? config('app.name');
    $metaDescription = $metaDescription ?? '';
    $canonical = $canonical ?? null;
    $jsonLd = $jsonLd ?? [];
    $activeNav = $activeNav ?? '';
    $bodyClass = $bodyClass ?? '';
    $ogImage = $ogImage ?? null;
    $noindex = $noindex ?? false;

    ob_start();
    require BASE_PATH . '/app/Views/layout.php';

    return (string) ob_get_clean();
}

function admin_view(string $template, array $data = []): string
{
    $file = BASE_PATH . '/app/Views/admin/' . $template . '.php';

    if (!is_file($file)) {
        throw new RuntimeException('Admin view not found: ' . $template);
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $file;
    $content = (string) ob_get_clean();

    $pageTitle = $pageTitle ?? 'Админ-панель';
    $adminSection = $adminSection ?? '';
    $adminUser = App\Core\Auth::user();

    ob_start();
    require BASE_PATH . '/app/Views/admin/layout.php';

    return (string) ob_get_clean();
}

function admin_auth_view(string $template, array $data = []): string
{
    $file = BASE_PATH . '/app/Views/admin/' . $template . '.php';

    if (!is_file($file)) {
        throw new RuntimeException('Admin view not found: ' . $template);
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $file;
    $content = (string) ob_get_clean();

    $pageTitle = $pageTitle ?? 'Вход в админ-панель';

    ob_start();
    require BASE_PATH . '/app/Views/admin/auth_layout.php';

    return (string) ob_get_clean();
}

function format_number(float|int $number, int $decimals = 2): string
{
    $formatted = number_format((float) $number, $decimals, ',', ' ');

    if ($decimals > 0) {
        $formatted = rtrim(rtrim($formatted, '0'), ',');
    }

    return $formatted;
}

function format_date(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }

    $months = [
        1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
    ];

    $result = date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);

    if ($withTime) {
        $result .= ', ' . date('H:i', $timestamp);
    }

    return $result;
}

function transliterate(string $text): string
{
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
    $text = trim($text, '-');

    return mb_substr($text, 0, 180, 'UTF-8');
}

function slugify(string $text): string
{
    $slug = transliterate($text);

    return $slug !== '' ? $slug : 'article-' . date('YmdHis');
}

function excerpt_text(string $html, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');

    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $length, 'UTF-8'), ' ,.;:') . '…';
}

function reading_time(string $html): int
{
    $text = strip_tags($html);
    $words = preg_split('/\s+/u', trim($text)) ?: [];
    $count = count(array_filter($words));

    return max(1, (int) ceil($count / 180));
}

function sanitize_url(string $url): string
{
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    if (preg_match('#^(https?://|/|\#|mailto:|tel:)#i', $url)) {
        return $url;
    }

    return '';
}

function sanitize_html(string $html): string
{
    if (trim($html) === '') {
        return '';
    }

    if (!class_exists('DOMDocument')) {
        return strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><ul><ol><li><a><blockquote><img><figure><figcaption><table><thead><tbody><tr><th><td><hr><code><pre>');
    }

    $allowed = [
        'p' => [], 'br' => [], 'h2' => ['id'], 'h3' => ['id'], 'h4' => ['id'],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'blockquote' => [], 'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'figure' => [], 'figcaption' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [],
        'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
        'hr' => [], 'code' => [], 'pre' => [],
    ];

    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML(
        '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div data-root="1">' . $html . '</div></body></html>',
        LIBXML_NONET
    );
    libxml_clear_errors();

    $root = $doc->getElementsByTagName('div')->item(0);
    if (!$root) {
        return '';
    }

    $clean = static function (DOMNode $node) use (&$clean, $allowed): void {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
                $node->removeChild($child);
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (!array_key_exists($tag, $allowed)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math', 'link', 'meta'], true)) {
                    $node->removeChild($child);
                } else {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                }
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                if (!in_array($name, $allowed[$tag], true) || str_starts_with($name, 'on')) {
                    $child->removeAttribute($attribute->name);
                }
            }

            if ($tag === 'a') {
                $href = sanitize_url($child->getAttribute('href'));
                if ($href === '') {
                    $child->removeAttribute('href');
                } else {
                    $child->setAttribute('href', $href);
                }
                if ($child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            }

            if ($tag === 'img') {
                $src = sanitize_url($child->getAttribute('src'));
                if ($src === '') {
                    $node->removeChild($child);
                    continue;
                }
                $child->setAttribute('src', $src);
                if (!$child->hasAttribute('alt')) {
                    $child->setAttribute('alt', '');
                }
                if (!$child->hasAttribute('loading')) {
                    $child->setAttribute('loading', 'lazy');
                }
            }

            $clean($child);
        }
    };

    $clean($root);

    $result = '';
    foreach ($root->childNodes as $child) {
        $result .= $doc->saveHTML($child);
    }

    return trim($result);
}

/**
 * Добавляет id заголовкам, собирает оглавление и префиксирует внутренние ссылки.
 *
 * @return array{html: string, toc: array<int, array{level: int, id: string, title: string}>}
 */
function render_article_content(string $html): array
{
    $html = sanitize_html($html);
    $toc = [];

    if (!class_exists('DOMDocument')) {
        return ['html' => $html, 'toc' => $toc];
    }

    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML(
        '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div data-root="1">' . $html . '</div></body></html>',
        LIBXML_NONET
    );
    libxml_clear_errors();

    $root = $doc->getElementsByTagName('div')->item(0);
    if (!$root) {
        return ['html' => $html, 'toc' => []];
    }

    $used = [];

    foreach ($root->getElementsByTagName('*') as $element) {
        $tag = strtolower($element->tagName);

        if ($tag === 'a') {
            $href = $element->getAttribute('href');
            if (str_starts_with($href, '/') && !str_starts_with($href, '//')) {
                $element->setAttribute('href', url($href));
            }
        }

        if ($tag === 'img') {
            $src = $element->getAttribute('src');
            if (str_starts_with($src, '/') && !str_starts_with($src, '//')) {
                $element->setAttribute('src', url($src));
            }
        }

        if (in_array($tag, ['h2', 'h3'], true)) {
            $title = trim($element->textContent);
            if ($title === '') {
                continue;
            }

            $id = $element->getAttribute('id');
            if ($id === '') {
                $id = transliterate($title);
                if ($id === '') {
                    $id = 'section-' . (count($toc) + 1);
                }
                $base = $id;
                $suffix = 2;
                while (isset($used[$id])) {
                    $id = $base . '-' . $suffix;
                    $suffix++;
                }
                $element->setAttribute('id', $id);
            }

            $used[$id] = true;
            $toc[] = [
                'level' => $tag === 'h2' ? 2 : 3,
                'id' => $id,
                'title' => $title,
            ];
        }
    }

    $result = '';
    foreach ($root->childNodes as $child) {
        $result .= $doc->saveHTML($child);
    }

    return ['html' => trim($result), 'toc' => $toc];
}

/**
 * @return array<string, array<string, mixed>>
 */
function calculator_definitions(): array
{
    static $definitions = null;

    if ($definitions === null) {
        $definitions = require BASE_PATH . '/app/data/calculators.php';
    }

    return $definitions;
}

function calculator_by_path(string $path): ?array
{
    foreach (calculator_definitions() as $slug => $definition) {
        if ($definition['path'] === $path) {
            $definition['slug'] = $slug;
            return $definition;
        }
    }

    return null;
}

function calculator_by_slug(string $slug): ?array
{
    $definitions = calculator_definitions();

    if (!isset($definitions[$slug])) {
        return null;
    }

    $definition = $definitions[$slug];
    $definition['slug'] = $slug;

    return $definition;
}

function calculator_settings(): array
{
    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

    $settings = [];

    foreach (calculator_definitions() as $slug => $definition) {
        $settings[$slug] = $definition['coefficients'];
    }

    try {
        $rows = db()->query('SELECT slug, coefficients FROM calculators')->fetchAll();
        foreach ($rows as $row) {
            $decoded = json_decode((string) $row['coefficients'], true);
            if (is_array($decoded) && isset($settings[$row['slug']])) {
                $settings[$row['slug']] = array_merge($settings[$row['slug']], $decoded);
            }
        }
    } catch (Throwable) {
        // Если БД ещё не установлена, работаем со значениями по умолчанию.
    }

    return $settings;
}

function client_ip_hash(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    return hash('sha256', $ip . '|' . config('app.name', 'stroycalc'));
}

function icon(string $name, string $class = ''): string
{
    $paths = [
        'logo' => '<path d="M4 19V7.5L12 3l8 4.5V19"/><path d="M8 19v-6h8v6"/><path d="M4 19h16"/>',
        'tile' => '<rect x="3.5" y="3.5" width="7" height="7" rx="1"/><rect x="13.5" y="3.5" width="7" height="7" rx="1"/><rect x="3.5" y="13.5" width="7" height="7" rx="1"/><rect x="13.5" y="13.5" width="7" height="7" rx="1"/>',
        'laminate' => '<path d="M3 6.5h18M3 12h18M3 17.5h18"/><path d="M9 3v3.5M15 6.5V12M7 12v5.5M17 12v5.5M12 17.5V21"/>',
        'wallpaper' => '<path d="M6 3h9a3 3 0 0 1 3 3v15H6z"/><path d="M9 3v18M12 3v18M15 3v18"/><path d="M18 7h2.5v14H18"/>',
        'arrow-right' => '<path d="M4 12h16"/><path d="m14 6 6 6-6 6"/>',
        'check' => '<path d="m4.5 12.5 5 5 10-11"/>',
        'shield' => '<path d="M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6z"/><path d="m9 12 2 2 4-4.5"/>',
        'calculator' => '<rect x="4" y="2.5" width="16" height="19" rx="2.5"/><path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01M8 19h.01M12 19h.01M16 19h.01"/>',
        'spark' => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/><circle cx="12" cy="12" r="3"/>',
        'copy' => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>',
        'save' => '<path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/>',
        'reset' => '<path d="M4 4v6h6"/><path d="M20 20v-6h-6"/><path d="M20 8a8 8 0 0 0-14.6-2M4 16a8 8 0 0 0 14.6 2"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'book' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16"/><path d="M8 7h8M8 11h6"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4v-.2a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3v-4h.2a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.4 7 7.2 4.2l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6V3h4v.2a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L20 7.2l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4h-.2a1.7 1.7 0 0 0-1.8 1z"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10z"/><path d="m13.5 6.5 3 3"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/>',
        'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="2.5"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'share' => '<circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="m8.2 10.8 7.6-4.6M8.2 13.2l7.6 4.6"/>',
        'home' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m3 17 5-5 4 4 3-3 6 6"/>',
        'bold' => '<path d="M7 4h6a4 4 0 0 1 0 8H7zM7 12h7a4 4 0 0 1 0 8H7z"/>',
        'italic' => '<path d="M10 4h8M6 20h8M14 4 10 20"/>',
        'list' => '<path d="M9 6h12M9 12h12M9 18h12M4 6h.01M4 12h.01M4 18h.01"/>',
        'list-ordered' => '<path d="M10 6h11M10 12h11M10 18h11"/><path d="M4 5h1v4M4 9h2M4 14h2l-2 3h2"/>',
        'link' => '<path d="M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"/><path d="M14 11a5 5 0 0 0-7.1 0l-2 2A5 5 0 0 0 12 20.1l1.1-1.1"/>',
        'undo' => '<path d="M9 7 4 12l5 5"/><path d="M4 12h9a6 6 0 0 1 6 6v1"/>',
        'redo' => '<path d="m15 7 5 5-5 5"/><path d="M20 12h-9a6 6 0 0 0-6 6v1"/>',
        'quote' => '<path d="M7 7H4v5h4v4H4M17 7h-3v5h4v4h-4"/>',
        'heading' => '<path d="M6 4v16M18 4v16M6 12h12"/>',
        'code' => '<path d="m8 7-5 5 5 5M16 7l5 5-5 5"/>',
        'send' => '<path d="m21 3-7 18-4-7-7-4z"/><path d="m21 3-11 11"/>',
    ];

    $path = $paths[$name] ?? $paths['info'];
    $classAttr = $class !== '' ? ' class="' . e($class) . '"' : '';

    return '<svg' . $classAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
}

function json_ld(array $schemas): string
{
    $output = '';

    foreach ($schemas as $schema) {
        $output .= '<script type="application/ld+json">' . json_encode(
            $schema,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
        ) . '</script>' . "\n";
    }

    return $output;
}
