<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArticleRepository;

final class HomeController
{
    public function index(): string
    {
        $articleRepository = new ArticleRepository();
        $articles = $articleRepository->published(3);
        $settings = calculator_settings();

        $calculators = [];
        foreach (calculator_definitions() as $slug => $definition) {
            $definition['slug'] = $slug;
            $definition['coefficients'] = array_merge($definition['coefficients'], $settings[$slug] ?? []);
            $calculators[$slug] = $definition;
        }

        $faq = [
            [
                'question' => 'Калькуляторы действительно бесплатные?',
                'answer' => 'Да. Все расчёты доступны без регистрации и оплаты. Можно сохранить ссылку на расчёт или отправить его в смету.',
            ],
            [
                'question' => 'Насколько точны расчёты?',
                'answer' => 'Мы используем стандартные формулы и коэффициенты запаса на подрезку. Результат стоит сверить с рекомендациями производителя материала и продавца — особенно для сложных помещений.',
            ],
            [
                'question' => 'Как сохранить расчёт?',
                'answer' => 'Нажмите «Поделиться» — ссылка с параметрами скопируется в буфер обмена. Открыв её позже, вы увидите те же размеры и результат. Кнопка «Сохранить расчёт» отправляет данные на сервер.',
            ],
            [
                'question' => 'Учитывается ли запас на подрезку?',
                'answer' => 'Да. Для плитки и ламината запас зависит от схемы укладки: от 5% при прямой до 15% при диагонали или ёлочке. Коэффициенты можно изменить в админ-панели.',
            ],
            [
                'question' => 'Можно ли считать с телефона?',
                'answer' => 'Да, интерфейс адаптирован для смартфонов: удобно считать прямо в магазине, стоя у полки с материалом.',
            ],
        ];

        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => (string) config('app.name'),
                'url' => site_url('/'),
                'description' => 'Бесплатные калькуляторы плитки, ламината и обоев с учётом запаса на подрезку.',
                'inLanguage' => 'ru-RU',
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(static fn (array $item): array => [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ], $faq),
            ],
        ];

        return view('home', [
            'pageTitle' => 'Калькулятор стройматериалов — плитка, ламинат, обои',
            'metaDescription' => 'Бесплатные онлайн-калькуляторы стройматериалов: рассчитайте плитку, ламинат и обои с учётом запаса на подрезку, упаковок и раппорта.',
            'canonical' => site_url('/'),
            'activeNav' => 'home',
            'bodyClass' => 'home-page',
            'calculators' => $calculators,
            'articles' => $articles,
            'faq' => $faq,
            'jsonLd' => $jsonLd,
        ]);
    }
}
