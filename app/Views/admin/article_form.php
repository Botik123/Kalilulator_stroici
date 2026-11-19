<?php
$isPublished = ($article['status'] ?? 'draft') === 'published';
$calculatorsList = calculator_definitions();
$generalError = $errors['general'] ?? '';
?>
<div class="admin-page-head">
    <div>
        <p class="admin-eyebrow"><?= $isEdit ? 'Редактирование' : 'Новый материал' ?></p>
        <h1><?= $isEdit ? 'Редактирование статьи' : 'Новая статья' ?></h1>
        <p><?= $isEdit ? 'Измените текст, SEO-поля или связь с калькулятором.' : 'Напишите инструкцию и свяжите её с нужным калькулятором.' ?></p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn--ghost" href="<?= e(url('/admin/articles')) ?>">← К списку</a>
        <?php if ($isEdit && $isPublished): ?>
            <a class="btn btn--ghost" href="<?= e(url('/articles/' . $article['slug'])) ?>" target="_blank" rel="noopener"><?= icon('eye') ?> На сайте</a>
        <?php endif; ?>
        <button class="btn btn--primary" type="submit" form="article-form"><?= icon('save') ?> Сохранить</button>
    </div>
</div>

<?php if ($generalError !== ''): ?>
    <div class="admin-alert admin-alert--error" role="alert"><?= icon('info') ?><span><?= e($generalError) ?></span></div>
<?php endif; ?>

<form id="article-form" class="admin-form" action="<?= e($formAction) ?>" method="post">
    <?= csrf_field() ?>

    <div class="admin-editor-layout">
        <div class="admin-editor-main">
            <section class="admin-card">
                <div class="admin-field">
                    <label for="article-title">Заголовок</label>
                    <input id="article-title" name="title" type="text" value="<?= e($article['title'] ?? '') ?>" maxlength="255" required data-slug-source autofocus>
                    <?php if (!empty($errors['title'])): ?><p class="field-error"><?= e($errors['title']) ?></p><?php endif; ?>
                </div>

                <div class="admin-field">
                    <div class="admin-label-row">
                        <label for="article-slug">Адрес статьи (slug)</label>
                        <button class="admin-text-btn" type="button" data-slug-generate>Сгенерировать из заголовка</button>
                    </div>
                    <div class="admin-slug-input">
                        <span>/articles/</span>
                        <input id="article-slug" name="slug" type="text" value="<?= e($article['slug'] ?? '') ?>" maxlength="190" placeholder="kak-rasschitat-plitku" data-slug-input>
                    </div>
                    <p class="field-hint">Только латиница, цифры и дефис. Если оставить пустым — создадим автоматически.</p>
                    <?php if (!empty($errors['slug'])): ?><p class="field-error"><?= e($errors['slug']) ?></p><?php endif; ?>
                </div>

                <div class="admin-field">
                    <div class="admin-label-row">
                        <label for="article-excerpt">Краткое описание</label>
                        <span class="admin-counter" data-counter-for="article-excerpt" data-max="500">0 / 500</span>
                    </div>
                    <textarea id="article-excerpt" name="excerpt" rows="3" maxlength="500" data-counter><?= e($article['excerpt'] ?? '') ?></textarea>
                    <p class="field-hint">Показывается в карточках статей и в поиске. Если пусто — возьмём начало текста.</p>
                    <?php if (!empty($errors['excerpt'])): ?><p class="field-error"><?= e($errors['excerpt']) ?></p><?php endif; ?>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>Текст статьи</h2>
                        <p>Форматирование сохраняется в HTML. Скрипты и опасные атрибуты удаляются.</p>
                    </div>
                </div>

                <div class="editor" data-editor>
                    <div class="editor-toolbar" data-editor-toolbar hidden>
                        <button type="button" data-command="bold" title="Жирный"><?= icon('bold') ?><span class="sr-only">Жирный</span></button>
                        <button type="button" data-command="italic" title="Курсив"><?= icon('italic') ?><span class="sr-only">Курсив</span></button>
                        <span class="editor-sep" aria-hidden="true"></span>
                        <button type="button" data-block="h2" title="Заголовок 2">H2</button>
                        <button type="button" data-block="h3" title="Заголовок 3">H3</button>
                        <button type="button" data-block="p" title="Обычный текст">¶</button>
                        <span class="editor-sep" aria-hidden="true"></span>
                        <button type="button" data-command="insertUnorderedList" title="Маркированный список"><?= icon('list') ?><span class="sr-only">Список</span></button>
                        <button type="button" data-command="insertOrderedList" title="Нумерованный список"><?= icon('list-ordered') ?><span class="sr-only">Нумерованный список</span></button>
                        <button type="button" data-block="blockquote" title="Цитата"><?= icon('quote') ?><span class="sr-only">Цитата</span></button>
                        <span class="editor-sep" aria-hidden="true"></span>
                        <button type="button" data-action="link" title="Ссылка"><?= icon('link') ?><span class="sr-only">Ссылка</span></button>
                        <button type="button" data-action="image" title="Изображение"><?= icon('image') ?><span class="sr-only">Изображение</span></button>
                        <button type="button" data-action="table" title="Таблица">▦</button>
                        <span class="editor-sep" aria-hidden="true"></span>
                        <button type="button" data-command="removeFormat" title="Очистить формат">⌫</button>
                        <button type="button" data-command="undo" title="Отменить"><?= icon('undo') ?><span class="sr-only">Отменить</span></button>
                        <button type="button" data-command="redo" title="Повторить"><?= icon('redo') ?><span class="sr-only">Повторить</span></button>
                        <span class="editor-toolbar-spacer"></span>
                        <button type="button" class="editor-source-toggle" data-editor-source-toggle title="Показать HTML">HTML</button>
                    </div>

                    <div class="editor-surface" contenteditable="true" data-editor-surface hidden spellcheck="true"></div>
                    <textarea id="article-content" class="editor-source" name="content" rows="20" required data-editor-source><?= e($article['content'] ?? '') ?></textarea>

                    <div class="editor-foot">
                        <span data-editor-mode>Визуальный редактор</span>
                        <span data-editor-count>0 символов</span>
                    </div>
                </div>

                <?php if (!empty($errors['content'])): ?><p class="field-error"><?= e($errors['content']) ?></p><?php endif; ?>
                <p class="field-hint">Вставляйте ссылки на калькуляторы в формате <code>/calculator/plitka</code> — на сайте они станут абсолютными.</p>
            </section>

            <section class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2>SEO</h2>
                        <p>Мета-теги для поисковых систем. Если оставить пустыми, подставим заголовок и описание.</p>
                    </div>
                </div>

                <div class="admin-field">
                    <div class="admin-label-row">
                        <label for="article-meta-title">Meta title</label>
                        <span class="admin-counter" data-counter-for="article-meta-title" data-max="255">0 / 255</span>
                    </div>
                    <input id="article-meta-title" name="meta_title" type="text" value="<?= e($article['meta_title'] ?? '') ?>" maxlength="255" data-counter>
                    <?php if (!empty($errors['meta_title'])): ?><p class="field-error"><?= e($errors['meta_title']) ?></p><?php endif; ?>
                </div>

                <div class="admin-field">
                    <div class="admin-label-row">
                        <label for="article-meta-description">Meta description</label>
                        <span class="admin-counter" data-counter-for="article-meta-description" data-max="320">0 / 320</span>
                    </div>
                    <textarea id="article-meta-description" name="meta_description" rows="3" maxlength="320" data-counter><?= e($article['meta_description'] ?? '') ?></textarea>
                    <?php if (!empty($errors['meta_description'])): ?><p class="field-error"><?= e($errors['meta_description']) ?></p><?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="admin-editor-aside">
            <section class="admin-card">
                <div class="admin-card-head">
                    <div><h2>Публикация</h2></div>
                </div>

                <div class="admin-field">
                    <label for="article-status">Статус</label>
                    <select id="article-status" name="status">
                        <option value="draft"<?= ($article['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Черновик</option>
                        <option value="published"<?= ($article['status'] ?? '') === 'published' ? ' selected' : '' ?>>Опубликована</option>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="article-calculator">Связанный калькулятор</label>
                    <select id="article-calculator" name="calculator_slug">
                        <option value="">Без привязки</option>
                        <?php foreach ($calculatorsList as $calcSlug => $calc): ?>
                            <option value="<?= e($calcSlug) ?>"<?= ($article['calculator_slug'] ?? '') === $calcSlug ? ' selected' : '' ?>><?= e($calc['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="field-hint">Ссылка появится в блоке «Как мы считаем» на странице калькулятора.</p>
                    <?php if (!empty($errors['calculator_slug'])): ?><p class="field-error"><?= e($errors['calculator_slug']) ?></p><?php endif; ?>
                </div>

                <div class="admin-field">
                    <label for="article-cover">Обложка</label>
                    <input id="article-cover" name="cover_image" type="text" value="<?= e($article['cover_image'] ?? '') ?>" placeholder="https://... или /assets/img/...">
                    <p class="field-hint">Необязательно. Без изображения будет декоративный узор.</p>
                    <?php if (!empty($errors['cover_image'])): ?><p class="field-error"><?= e($errors['cover_image']) ?></p><?php endif; ?>
                </div>

                <button class="btn btn--primary btn--block" type="submit"><?= icon('save') ?> Сохранить</button>

                <?php if ($isEdit): ?>
                    <p class="admin-publish-meta">
                        Создана: <?= e(format_date((string) $article['created_at'], true)) ?><br>
                        Обновлена: <?= e(format_date((string) $article['updated_at'], true)) ?>
                    </p>
                <?php endif; ?>
            </section>

            <section class="admin-note-card">
                <span class="admin-note-icon"><?= icon('info') ?></span>
                <div>
                    <strong>Перелинковка</strong>
                    <p>Ссылайтесь на <code>/calculator/plitka</code>, <code>/calculator/laminat</code> и <code>/calculator/oboi</code> — так читатель сразу перейдёт к расчёту.</p>
                </div>
            </section>
        </aside>
    </div>
</form>
