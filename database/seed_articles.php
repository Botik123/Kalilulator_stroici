<?php
declare(strict_types=1);

$tileContent = file_get_contents(__DIR__ . '/articles/kak-rasschitat-plitku.html') ?: '';
$laminateContent = file_get_contents(__DIR__ . '/articles/kak-rasschitat-laminat.html') ?: '';
$wallpaperContent = file_get_contents(__DIR__ . '/articles/kak-rasschitat-oboi.html') ?: '';

return [
    [
        'slug' => 'kak-rasschitat-plitku',
        'title' => 'Как рассчитать количество плитки: пошаговая инструкция',
        'excerpt' => 'Формулы, запас на подрезку и перевод плиток в упаковки. Примеры для прямой и диагональной укладки, таблица размеров и частые ошибки.',
        'content' => $tileContent,
        'meta_title' => 'Как рассчитать плитку: формулы, запас и упаковки',
        'meta_description' => 'Пошаговый расчёт плитки для пола: площадь, количество штук, запас 5–15% и упаковки. Примеры для прямой и диагональной укладки.',
        'calculator_slug' => 'tile',
        'status' => 'published',
        'views' => 184,
    ],
    [
        'slug' => 'kak-rasschitat-laminat',
        'title' => 'Как рассчитать ламинат: упаковки, запас и схема укладки',
        'excerpt' => 'Считаем площадь комнаты, площадь упаковки и запас для прямой укладки, диагонали и ёлочки. Примеры и таблица размеров досок.',
        'content' => $laminateContent,
        'meta_title' => 'Как рассчитать ламинат: сколько упаковок нужно',
        'meta_description' => 'Инструкция по расчёту ламината: площадь комнаты и упаковки, запас 5–15%, примеры для прямой укладки, диагонали и ёлочки.',
        'calculator_slug' => 'laminate',
        'status' => 'published',
        'views' => 137,
    ],
    [
        'slug' => 'kak-rasschitat-oboi',
        'title' => 'Как рассчитать обои: рулоны, раппорт и вычеты проёмов',
        'excerpt' => 'Почему площадь рулона не работает, как считать полотна с раппортом и учитывать окна с дверями. Пример расчёта на комнату 4 × 5 м.',
        'content' => $wallpaperContent,
        'meta_title' => 'Как рассчитать обои: сколько рулонов нужно',
        'meta_description' => 'Пошаговый расчёт обоев: периметр, высота стен, полотна в рулоне, раппорт, окна и двери. Пример для комнаты 4 × 5 м и таблица.',
        'calculator_slug' => 'wallpaper',
        'status' => 'published',
        'views' => 96,
    ],
];
