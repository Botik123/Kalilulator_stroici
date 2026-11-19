# СтройКалькулятор

Веб-приложение «Калькулятор стройматериалов»: расчёт плитки, ламината и обоев с мгновенным результатом в браузере, статьями-инструкциями и админ-панелью для управления контентом и коэффициентами.

## Возможности

**Публичная часть**

- Три калькулятора: [плитка](app/Views/calculators/forms/tile.php), [ламинат](app/Views/calculators/forms/laminate.php), [обои](app/Views/calculators/forms/wallpaper.php).
- Мгновенный пересчёт на клиенте (Vanilla JS) без перезагрузки страницы.
- Валидация полей: отрицательные значения, буквы, выход за диапазон показывают ошибку прямо под полем.
- «Поделиться» — ссылка с параметрами (`?l=4.2&w=3.6&tl=30…`), при открытии все поля восстанавливаются.
- «Сбросить» — возврат к значениям по умолчанию.
- «Сохранить расчёт» — отправка через Fetch API в БД (для статистики в админке).
- Статьи с оглавлением, перелинковкой и блоком «Как мы считаем» с формулами.
- SEO: ЧПУ, canonical, meta-теги, микроразметка Schema.org (WebApplication, HowTo, FAQPage, Article, BreadcrumbList).
- Адаптивная вёрстка Mobile First, работает без внешних CDN и библиотек.

**Админ-панель** (`/admin`)

- Авторизация по логину/паролю (bcrypt, `password_hash`), защита от перебора.
- CRUD статей: WYSIWYG-редактор с переключением в HTML, автослаг из кириллицы, счётчики символов, SEO-поля, статус «черновик/опубликована», привязка к калькулятору.
- Редактирование коэффициентов запаса каждого калькулятора без правки кода — значения сразу попадают на страницы калькуляторов и в JS-конфиг.
- Обзор: количество статей, сохранённых расчётов, последние сохранения.

## Стек

| Слой | Технология |
|---|---|
| Backend | PHP 8.1+ (без фреймворков), PDO, prepared statements |
| БД | SQLite (основа, файл `storage/database.sqlite`) или MySQL/MariaDB (переключение через `.env`) |
| Frontend | HTML5, CSS3 (Flexbox/Grid), Vanilla JS |
| Редактор | Собственный WYSIWYG на `contenteditable` + HTML-режим |

Расчёты выполняются на клиенте (`assets/js/calc-core.js` — чистые функции, те же формулы можно проверить в Node.js). PHP отдаёт страницы, статьи и сохраняет расчёты.

## Структура проекта

```
app/
  bootstrap.php        загрузка .env, сессии, автозагрузчик
  config.php           конфигурация (env-переменные)
  helpers.php          view/url/CSRF/санитайзеры/форматтеры
  routes.php           карта маршрутов
  Core/                Router, Database, Auth, Installer
  Controllers/         Home, Calculator, Article, Page, Api, Admin
  Repositories/        Article, Calculator, Calculation
  Views/               публичные шаблоны + admin/
  data/calculators.php описания калькуляторов и формул (дефолты)
assets/
  css/app.css          публичные стили
  css/admin.css        стили админки
  js/calc-core.js      формулы расчёта (браузер + Node)
  js/calculators.js    интерактив страниц калькуляторов
  js/app.js            меню, копирование ссылок
  js/admin.js          редактор, slug, подтверждения
bin/install.php        установка схемы, демо-данных и администратора
database/
  schema.mysql.sql     схема MySQL
  schema.sqlite.sql    схема SQLite
  seed_articles.php    тексты статей для наполнения
  articles/*.html      исходники статей
storage/               SQLite-файл и логи (не в git)
```

## Быстрый старт

Требования: PHP 8.1+ с расширениями `pdo`, `mbstring`, `dom`, `json`; `pdo_sqlite` и `sqlite3` (используются по умолчанию); `pdo_mysql` — только если переключаетесь на MySQL.

### Установка (SQLite — по умолчанию)

SQLite — основная база данных проекта. Файл создаётся автоматически, схема и демо-контент наполняются при первом обращении к БД или запуском установщика.

```bash
cp .env.example .env
# в .env достаточно:
#   DB_DRIVER=sqlite
#   DB_SQLITE_PATH=storage/database.sqlite
#   DB_AUTO_INSTALL=1

php bin/install.php
php -S localhost:8000 router.php
```

Сайт: http://localhost:8000. Файл базы лежит в `storage/database.sqlite` — для полного сброса удалите его и запустите установщик заново.

### Запуск с MySQL (опционально)

Если нужна MySQL/MariaDB, переключите драйвер в `.env`:

```sql
CREATE DATABASE stroycalc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
# в .env: DB_DRIVER=mysql, DB_HOST/DB_NAME/DB_USER/DB_PASS
php bin/install.php
php -S localhost:8000 router.php
```

Установщик можно запускать повторно — он не дублирует статьи и схемы.

### Создание администратора

Вариант 1 — через переменные окружения при установке:

```bash
ADMIN_LOGIN=admin ADMIN_PASSWORD=supersecret php bin/install.php
```

Вариант 2 — при первом открытии `http://localhost:8000/admin/setup` (страница доступна, пока таблица `users` пуста, и закрывается после создания первого пользователя).

## Деплой на хостинг (Apache)

1. Загрузить файлы в корень сайта. Корневой `.htaccess` направляет все запросы на `index.php`, служебные каталоги (`app/`, `database/`, `bin/`, `storage/`) закрыты своими `.htaccess`.
2. Заполнить `.env` (`APP_ENV=production`, `APP_DEBUG=0`, `APP_URL=https://site.ru`). Для SQLite дополнительно указать `DB_AUTO_INSTALL=0` и создать базу установщиком; для MySQL — создать БД и пользователя заранее.
3. Выполнить `php bin/install.php` (или открыть `/admin/setup`).
4. Выдать права на запись в `storage/` — там лежат файл SQLite-базы и логи.
5. Для Nginx достаточно `try_files $uri /index.php?$query_string;`.

## Маршруты

| URL | Описание |
|---|---|
| `/` | Главная |
| `/calculators` | Список калькуляторов |
| `/calculator/plitka`, `/calculator/laminat`, `/calculator/oboi` | Страницы калькуляторов |
| `/articles`, `/articles/{slug}` | Статьи |
| `/about`, `/privacy` | О проекте, политика |
| `/admin/login`, `/admin/setup` | Вход и первичная настройка |
| `/admin` | Обзор админки |
| `/admin/articles[...]` | CRUD статей |
| `/admin/calculators` | Коэффициенты |

### REST API

| Метод | Эндпоинт | Описание |
|---|---|---|
| GET | `/api/articles?limit=20` | Список опубликованных статей (JSON) |
| GET | `/api/articles/{slug}` | Одна статья (JSON) |
| GET | `/api/calculators` | Калькуляторы и текущие коэффициенты |
| POST | `/api/calculations` | Сохранение расчёта: `{"calculator":"tile","inputs":{…},"results":{…}}`, ответ `201 {"ok":true,"id":N}` |

## Формулы расчёта

**Плитка.** `S = длина × ширина`; `Sплитки = (a × b) / 10 000`; количество с запасом `= S / Sплитки × (1 + запас/100)`, округление вверх; упаковки `= плитки / плиток в упаковке` (округление вверх). Запас: прямая 5%, смещение 10%, диагональ 15%.

**Ламинат.** `Sупак = (a × b) / 1 000 000 × досок`; `Nупак = ⌈S × (1 + запас/100) / Sупак⌉`. Запас: прямая 5%, диагональ 10%, ёлочка 15%.

**Обои.** Периметр `P = 2(a + b)`; площадь стен минус проёмы; длина полотна `= высота + раппорт + припуск 5 см`; полотен в рулоне `= ⌊Lрулона / Lполотна⌋`; рулоны `= ⌈полотен / полотнен в рулоне⌉`.

Все коэффициенты редактируются в `/admin/calculators`.

## Безопасность

- Все обращения к БД — через PDO prepared statements.
- CSRF-токены на всех POST-формах админки и API сохранений.
- Экранирование вывода (`e()`), HTML статей проходит whitelist-санитайзер (`sanitize_html`): удаляются `script/style/iframe`, атрибуты `on*`, опасные схемы URL.
- Пароли — `password_hash` (bcrypt), сессии с `HttpOnly`/`SameSite=Lax`, перегенерация ID после входа.
- Rate-limit: 5 попыток входа за 10 минут, 30 сохранений расчётов с одного клиента за 10 минут.
- Заголовки `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`.

## Полезные команды

```bash
# проверка синтаксиса всех PHP-файлов
find app bin database -name '*.php' -exec php -l {} \;

# быстрая проверка формул в Node.js (тот же код, что в браузере)
node -e "const c=require('./assets/js/calc-core.js'); console.log(c.calculateTile({length:4.2,width:3.6,tile_length:30,tile_width:30,layout:'straight',pack_coverage:1.44},c.DEFAULT_COEFFICIENTS.tile));"

# сброс локальной SQLite-базы
rm storage/database.sqlite && php bin/install.php
```

## Лицензия и контакты

Расчёты носят справочный характер: перед покупкой сверяйтесь с рекомендациями производителя материала. Контакты для вопросов: x0bar@yandex.ru, Telegram — https://t.me/tgkrostikaizkms, ВКонтакте — https://vk.ru/yfvyv5 (настраиваются через `APP_EMAIL`, `APP_TELEGRAM`, `APP_VK` в `.env`).
