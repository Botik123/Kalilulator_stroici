<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArticleRepository;

final class ArticleController
{
    public function index(): string
    {
        $articles = (new ArticleRepository())->published();

        return view('articles/index', [
            'pageTitle' => 'Статьи о расчёте стройматериалов — плитка, ламинат, обои',
            'metaDescription' => 'Понятные инструкции с формулами и примерами: как рассчитать плитку, ламинат и обои, какой запас на подрезку заложить и как не переплатить.',
            'canonical' => site_url('/articles'),
            'activeNav' => 'articles',
            'bodyClass' => 'articles-page',
            'articles' => $articles,
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Blog',
                    'name' => 'Статьи о расчёте стройматериалов',
                    'url' => site_url('/articles'),
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
        $repository = new ArticleRepository();
        $article = $repository->findBySlug((string) ($params['slug'] ?? ''));

        if ($article === null) {
            abort(404, 'Статья не найдена. Возможно, она была удалена или ссылка устарела.');
        }

        $repository->incrementViews((int) $article['id']);
        $article['views'] = (int) $article['views'] + 1;

        $rendered = render_article_content((string) $article['content']);
        $calculator = $article['calculator_slug'] ? calculator_by_slug((string) $article['calculator_slug']) : null;
        $relatedArticles = $repository->related((int) $article['id'], 3);

        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $article['title'],
                'description' => $article['meta_description'] ?: $article['excerpt'],
                'url' => site_url('/articles/' . $article['slug']),
                'datePublished' => date('c', strtotime((string) $article['created_at'])),
                'dateModified' => date('c', strtotime((string) $article['updated_at'])),
                'inLanguage' => 'ru-RU',
                'author' => [
                    '@type' => 'Organization',
                    'name' => (string) config('app.name'),
                    'url' => site_url('/'),
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => (string) config('app.name'),
                    'url' => site_url('/'),
                ],
                'mainEntityOfPage' => site_url('/articles/' . $article['slug']),
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
                        'name' => 'Статьи',
                        'item' => site_url('/articles'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $article['title'],
                        'item' => site_url('/articles/' . $article['slug']),
                    ],
                ],
            ],
        ];

        return view('articles/show', [
            'pageTitle' => $article['meta_title'] ?: $article['title'],
            'metaDescription' => $article['meta_description'] ?: $article['excerpt'],
            'canonical' => site_url('/articles/' . $article['slug']),
            'activeNav' => 'articles',
            'bodyClass' => 'article-page',
            'article' => $article,
            'articleHtml' => $rendered['html'],
            'toc' => $rendered['toc'],
            'calculator' => $calculator,
            'relatedArticles' => $relatedArticles,
            'jsonLd' => $jsonLd,
        ]);
    }
}
