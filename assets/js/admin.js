(function () {
    'use strict';

    /* ---------- Мобильное меню ---------- */

    const sidebar = document.querySelector('[data-admin-sidebar]');
    const navToggle = document.querySelector('[data-admin-nav-toggle]');
    const overlay = document.querySelector('[data-admin-overlay]');

    function closeSidebar() {
        if (!sidebar || !navToggle) {
            return;
        }
        sidebar.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('nav-open');
        if (overlay) {
            overlay.hidden = true;
        }
    }

    if (sidebar && navToggle) {
        navToggle.addEventListener('click', function () {
            const isOpen = sidebar.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            document.body.classList.toggle('nav-open', isOpen);
            if (overlay) {
                overlay.hidden = !isOpen;
            }
        });

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 900) {
                closeSidebar();
            }
        });
    }

    /* ---------- Подтверждение удаления ---------- */

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.getAttribute('data-confirm') || 'Вы уверены?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    /* ---------- Счётчики символов ---------- */

    document.querySelectorAll('[data-counter]').forEach(function (field) {
        if (!field.id) {
            return;
        }

        const counter = document.querySelector('[data-counter-for="' + field.id + '"]');
        if (!counter) {
            return;
        }

        const max = Number(counter.getAttribute('data-max')) || Number(field.getAttribute('maxlength')) || 0;

        const update = function () {
            const length = field.value.length;
            counter.textContent = length + ' / ' + max;
            counter.classList.toggle('is-limit', max > 0 && length >= max);
        };

        field.addEventListener('input', update);
        update();
    });

    /* ---------- Автоматический slug ---------- */

    const slugSource = document.querySelector('[data-slug-source]');
    const slugInput = document.querySelector('[data-slug-input]');
    const slugButton = document.querySelector('[data-slug-generate]');

    function transliterate(text) {
        const map = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
            'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
            'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
            'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch', 'ъ': '',
            'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
        };

        return text
            .toLowerCase()
            .split('')
            .map(function (char) { return map[char] !== undefined ? map[char] : char; })
            .join('')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 180);
    }

    if (slugSource && slugInput) {
        let autoSlug = slugInput.value.trim() === '';

        slugSource.addEventListener('input', function () {
            if (autoSlug) {
                slugInput.value = transliterate(slugSource.value);
            }
        });

        slugInput.addEventListener('input', function () {
            autoSlug = slugInput.value.trim() === '';
        });

        if (slugButton) {
            slugButton.addEventListener('click', function () {
                slugInput.value = transliterate(slugSource.value);
                autoSlug = true;
                slugInput.focus();
            });
        }
    }

    /* ---------- WYSIWYG ---------- */

    document.querySelectorAll('[data-editor]').forEach(function (editor) {
        const surface = editor.querySelector('[data-editor-surface]');
        const source = editor.querySelector('[data-editor-source]');
        const toolbar = editor.querySelector('[data-editor-toolbar]');
        const sourceToggle = editor.querySelector('[data-editor-source-toggle]');
        const modeLabel = editor.querySelector('[data-editor-mode]');
        const countLabel = editor.querySelector('[data-editor-count]');
        const form = editor.closest('form');

        if (!surface || !source) {
            return;
        }

        let sourceMode = false;

        surface.innerHTML = source.value;
        surface.hidden = false;
        source.hidden = true;
        if (toolbar) {
            toolbar.hidden = false;
        }
        editor.classList.add('is-enhanced');

        const updateCount = function () {
            if (!countLabel) {
                return;
            }
            const text = sourceMode ? source.value : (surface.textContent || '');
            countLabel.textContent = text.length.toLocaleString('ru-RU') + ' символов';
        };

        const syncSource = function () {
            if (!sourceMode) {
                source.value = surface.innerHTML;
            }
        };

        surface.addEventListener('input', function () {
            syncSource();
            updateCount();
        });

        if (form) {
            form.addEventListener('submit', function () {
                syncSource();
            });
        }

        if (toolbar) {
            toolbar.querySelectorAll('button[data-command]').forEach(function (button) {
                button.addEventListener('click', function () {
                    surface.focus();
                    document.execCommand(button.getAttribute('data-command'), false, null);
                    syncSource();
                    updateCount();
                });
            });

            toolbar.querySelectorAll('button[data-block]').forEach(function (button) {
                button.addEventListener('click', function () {
                    surface.focus();
                    document.execCommand('formatBlock', false, '<' + button.getAttribute('data-block') + '>');
                    syncSource();
                    updateCount();
                });
            });

            const linkButton = toolbar.querySelector('[data-action="link"]');
            if (linkButton) {
                linkButton.addEventListener('click', function () {
                    const url = window.prompt('Адрес ссылки:', '/calculator/plitka');
                    if (!url) {
                        return;
                    }
                    surface.focus();
                    document.execCommand('createLink', false, url);
                    syncSource();
                });
            }

            const imageButton = toolbar.querySelector('[data-action="image"]');
            if (imageButton) {
                imageButton.addEventListener('click', function () {
                    const url = window.prompt('Адрес изображения (https://… или /assets/…):', '');
                    if (!url) {
                        return;
                    }
                    surface.focus();
                    document.execCommand('insertImage', false, url);
                    syncSource();
                });
            }

            const tableButton = toolbar.querySelector('[data-action="table"]');
            if (tableButton) {
                tableButton.addEventListener('click', function () {
                    surface.focus();
                    document.execCommand('insertHTML', false,
                        '<table><thead><tr><th>Параметр</th><th>Значение</th></tr></thead>' +
                        '<tbody><tr><td>Пример</td><td>0</td></tr><tr><td>Пример</td><td>0</td></tr></tbody></table><p><br></p>'
                    );
                    syncSource();
                    updateCount();
                });
            }
        }

        if (sourceToggle) {
            sourceToggle.addEventListener('click', function () {
                sourceMode = !sourceMode;

                if (sourceMode) {
                    source.value = surface.innerHTML;
                    surface.hidden = true;
                    source.hidden = false;
                    sourceToggle.classList.add('is-active');
                    if (modeLabel) {
                        modeLabel.textContent = 'HTML-код';
                    }
                    source.focus();
                } else {
                    surface.innerHTML = source.value;
                    surface.hidden = false;
                    source.hidden = true;
                    sourceToggle.classList.remove('is-active');
                    if (modeLabel) {
                        modeLabel.textContent = 'Визуальный редактор';
                    }
                    surface.focus();
                }

                updateCount();
            });
        }

        source.addEventListener('input', updateCount);
        updateCount();
    });

    /* ---------- Сброс коэффициентов ---------- */

    document.querySelectorAll('[data-coefficient-reset]').forEach(function (button) {
        button.addEventListener('click', function () {
            const card = button.closest('.admin-settings-card');
            if (!card) {
                return;
            }

            card.querySelectorAll('input[data-default]').forEach(function (input) {
                input.value = input.getAttribute('data-default');
            });
        });
    });

    /* ---------- Автоскрытие успешных уведомлений ---------- */

    document.querySelectorAll('.admin-alert--success').forEach(function (alert) {
        window.setTimeout(function () {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            window.setTimeout(function () {
                alert.remove();
            }, 320);
        }, 6000);
    });
})();
