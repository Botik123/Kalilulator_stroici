<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArticleRepository;

final class CalculatorController
{
    public function index(): string
    {
        $settings = calculator_settings();
        $calculators = [];

        foreach (calculator_definitions() as $slug => $definition) {
            $definition['slug'] = $slug;
            $definition['coefficients'] = array_merge($definition['coefficients'], $settings[$slug] ?? []);
            $calculators[$slug] = $definition;
        }

        return view('calculators/index', [
            'pageTitle' => 'Калькуляторы стройматериалов — плитка, ламинат, обои',
            'metaDescription' => 'Все калькуляторы стройматериалов в одном месте: плитка, ламинат и обои. Мгновенный расчёт, запас на подрезку и сохранение результата.',
            'canonical' => site_url('/calculators'),
            'activeNav' => 'calculators',
            'bodyClass' => 'calculators-index-page',
            'calculators' => $calculators,
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => 'Калькуляторы стройматериалов',
                    'url' => site_url('/calculators'),
                    'inLanguage' => 'ru-RU',
                ],
            ],
        ]);
    }

    /**
     * @param array<string, string> $params
     */
    public function show(array $params): string
    {
        $calculator = calculator_by_path($params['slug'] ?? '');

        if ($calculator === null) {
            abort(404, 'Такого калькулятора нет. Выберите плитку, ламинат или обои в меню.');
        }

        $settings = calculator_settings();
        $calculator['coefficients'] = array_merge($calculator['coefficients'], $settings[$calculator['slug']] ?? []);

        $articleRepository = new ArticleRepository();
        $article = $articleRepository->findBySlug((string) $calculator['article']);
        $relatedArticles = $articleRepository->forCalculator($calculator['slug']);

        $faqSchema = array_map(static fn (array $item): array => [
            '@type' => 'Question',
            'name' => $item['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $item['answer'],
            ],
        ], $calculator['faq']);

        $howToSteps = array_map(static fn (array $step, int $index): array => [
            '@type' => 'HowToStep',
            'position' => $index + 1,
            'name' => $step['title'],
            'text' => $step['text'],
            'url' => site_url('/calculator/' . $calculator['path'] . '#step-' . ($index + 1)),
        ], $calculator['how'], array_keys($calculator['how']));

        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebApplication',
                'name' => $calculator['full_name'],
                'url' => site_url('/calculator/' . $calculator['path']),
                'applicationCategory' => 'UtilitiesApplication',
                'operatingSystem' => 'Any',
                'browserRequirements' => 'Требуется JavaScript',
                'isAccessibleForFree' => true,
                'inLanguage' => 'ru-RU',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'RUB',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'HowTo',
                'name' => 'Как рассчитать ' . mb_strtolower($calculator['name'], 'UTF-8'),
                'description' => $calculator['meta_description'],
                'totalTime' => 'PT2M',
                'step' => $howToSteps,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqSchema,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Главная',
                        'item' => site_url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Калькуляторы',
                        'item' => site_url('/calculators'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $calculator['full_name'],
                        'item' => site_url('/calculator/' . $calculator['path']),
                    ],
                ],
            ],
        ];

        $calcConfig = json_encode([
            'slug' => $calculator['slug'],
            'coefficients' => $calculator['coefficients'],
            'defaults' => $calculator['defaults'],
            'api_url' => url('/api/calculations'),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return view('calculators/show', [
            'pageTitle' => $calculator['meta_title'],
            'metaDescription' => $calculator['meta_description'],
            'canonical' => site_url('/calculator/' . $calculator['path']),
            'activeNav' => 'calculators',
            'bodyClass' => 'calculator-page calculator-page--' . $calculator['slug'],
            'calculator' => $calculator,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'calcConfig' => $calcConfig,
            'jsonLd' => $jsonLd,
        ]);
    }
}
