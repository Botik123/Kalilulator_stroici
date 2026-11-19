<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArticleRepository;
use App\Repositories\CalculationRepository;

final class ApiController
{
    public function articles(): never
    {
        $limit = (int) ($_GET['limit'] ?? 20);
        $limit = max(1, min(50, $limit));

        $articles = (new ArticleRepository())->published($limit);

        $data = array_map(static function (array $article): array {
            $calculator = $article['calculator_slug'] ? calculator_by_slug((string) $article['calculator_slug']) : null;

            return [
                'id' => (int) $article['id'],
                'slug' => $article['slug'],
                'title' => $article['title'],
                'excerpt' => $article['excerpt'],
                'url' => site_url('/articles/' . $article['slug']),
                'calculator' => $calculator ? [
                    'slug' => $calculator['slug'],
                    'name' => $calculator['name'],
                    'url' => site_url('/calculator/' . $calculator['path']),
                ] : null,
                'published_at' => date('c', strtotime((string) $article['created_at'])),
                'reading_time' => reading_time((string) $article['content']),
            ];
        }, $articles);

        json_response([
            'data' => $data,
            'meta' => ['count' => count($data)],
        ]);
    }

    /**
     * @param array<string, string> $params
     */
    public function article(array $params): never
    {
        $article = (new ArticleRepository())->findBySlug((string) ($params['slug'] ?? ''));

        if ($article === null) {
            json_response(['error' => 'Статья не найдена.'], 404);
        }

        $calculator = $article['calculator_slug'] ? calculator_by_slug((string) $article['calculator_slug']) : null;

        json_response([
            'data' => [
                'id' => (int) $article['id'],
                'slug' => $article['slug'],
                'title' => $article['title'],
                'excerpt' => $article['excerpt'],
                'content' => $article['content'],
                'meta_title' => $article['meta_title'],
                'meta_description' => $article['meta_description'],
                'url' => site_url('/articles/' . $article['slug']),
                'calculator' => $calculator ? [
                    'slug' => $calculator['slug'],
                    'name' => $calculator['name'],
                    'url' => site_url('/calculator/' . $calculator['path']),
                ] : null,
                'published_at' => date('c', strtotime((string) $article['created_at'])),
                'updated_at' => date('c', strtotime((string) $article['updated_at'])),
            ],
        ]);
    }

    public function calculators(): never
    {
        $settings = calculator_settings();
        $data = [];

        foreach (calculator_definitions() as $slug => $definition) {
            $data[] = [
                'slug' => $slug,
                'name' => $definition['name'],
                'url' => site_url('/calculator/' . $definition['path']),
                'coefficients' => array_merge($definition['coefficients'], $settings[$slug] ?? []),
            ];
        }

        json_response(['data' => $data]);
    }

    public function saveCalculation(): never
    {
        if (($_SERVER['CONTENT_TYPE'] ?? '') !== '' && !str_contains((string) $_SERVER['CONTENT_TYPE'], 'application/json')) {
            json_response(['error' => 'Ожидается JSON.'], 415);
        }

        $now = time();
        $attempts = $_SESSION['calc_saves'] ?? [];
        $attempts = array_values(array_filter($attempts, static fn (int $timestamp): bool => $timestamp > $now - 600));
        $limit = (int) config('security.calculations_per_10min', 30);

        if (count($attempts) >= $limit) {
            json_response(['error' => 'Слишком много сохранений подряд. Попробуйте через несколько минут.'], 429);
        }

        $payload = request_json();
        $slug = (string) ($payload['calculator'] ?? '');
        $definition = calculator_by_slug($slug);

        if ($definition === null) {
            json_response(['error' => 'Неизвестный калькулятор.'], 422);
        }

        $inputs = $this->normalizePayload($payload['inputs'] ?? null);
        $results = $this->normalizePayload($payload['results'] ?? null);

        if ($inputs === null || $results === null || $inputs === [] || $results === []) {
            json_response(['error' => 'Некорректные данные расчёта.'], 422);
        }

        if (strlen((string) json_encode($inputs)) > 12000 || strlen((string) json_encode($results)) > 12000) {
            json_response(['error' => 'Слишком большой объём данных.'], 422);
        }

        $id = (new CalculationRepository())->create($slug, $inputs, $results);

        $attempts[] = $now;
        $_SESSION['calc_saves'] = $attempts;

        json_response([
            'ok' => true,
            'id' => $id,
            'message' => 'Расчёт сохранён.',
        ], 201);
    }

    /**
     * Оставляет только плоские скалярные значения разумной длины.
     *
     * @return array<string, scalar>|null
     */
    private function normalizePayload(mixed $value): ?array
    {
        if (!is_array($value) || count($value) > 40) {
            return null;
        }

        $result = [];

        foreach ($value as $key => $item) {
            if (!is_string($key) || strlen($key) > 60) {
                return null;
            }

            if (is_bool($item)) {
                $result[$key] = $item;
            } elseif (is_int($item) || is_float($item)) {
                $result[$key] = $item;
            } elseif (is_string($item)) {
                if (mb_strlen($item) > 500) {
                    return null;
                }
                $result[$key] = $item;
            } else {
                return null;
            }
        }

        return $result;
    }
}
