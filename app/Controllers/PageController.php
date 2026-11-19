<?php
declare(strict_types=1);

namespace App\Controllers;

final class PageController
{
    public function about(): string
    {
        return view('pages/about', [
            'pageTitle' => 'О проекте — СтройКалькулятор',
            'metaDescription' => 'Кто и зачем сделал СтройКалькулятор: бесплатные инструменты для точного расчёта плитки, ламината и обоев без переплат.',
            'canonical' => site_url('/about'),
            'activeNav' => 'about',
            'bodyClass' => 'about-page',
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'AboutPage',
                    'name' => 'О проекте СтройКалькулятор',
                    'url' => site_url('/about'),
                    'inLanguage' => 'ru-RU',
                ],
            ],
        ]);
    }

    public function privacy(): string
    {
        return view('pages/privacy', [
            'pageTitle' => 'Политика конфиденциальности — СтройКалькулятор',
            'metaDescription' => 'Как СтройКалькулятор обрабатывает данные: какие сведения собираются, зачем нужны cookie и как удалить сохранённые расчёты.',
            'canonical' => site_url('/privacy'),
            'activeNav' => '',
            'bodyClass' => 'privacy-page',
        ]);
    }
}
