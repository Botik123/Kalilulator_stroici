(function () {
    'use strict';

    const root = document.querySelector('[data-calc-root]');
    if (!root) {
        return;
    }

    const form = root.querySelector('[data-calc-form]');
    const core = window.StroyCalcCore;
    if (!form || !core) {
        return;
    }

    const config = window.CALC_CONFIG || {};
    const slug = config.slug || root.getAttribute('data-calculator') || '';
    const coefficients = config.coefficients || {};
    const apiUrl = config.api_url || '/api/calculations';

    const result = {
        card: root.querySelector('[data-result-card]'),
        value: root.querySelector('[data-result-value]'),
        unit: root.querySelector('[data-result-unit]'),
        label: root.querySelector('[data-result-label]'),
        status: root.querySelector('[data-result-status]'),
        rows: root.querySelector('[data-result-rows]'),
        breakdown: root.querySelector('[data-result-breakdown]'),
        note: root.querySelector('[data-result-note]'),
        message: root.querySelector('[data-result-message]'),
        saveBtn: root.querySelector('[data-action="save"]'),
        shareBtn: root.querySelector('[data-action="share"]'),
        resetBtn: root.querySelector('[data-action="reset"]')
    };

    const paramMap = {
        tile: {
            mode: 'mode',
            length: 'l',
            width: 'w',
            tile_length: 'tl',
            tile_width: 'tw',
            layout: 'lay',
            pack_coverage: 'pack',
            tile_count: 'cnt'
        },
        laminate: {
            length: 'l',
            width: 'w',
            board_length: 'bl',
            board_width: 'bw',
            boards_per_pack: 'bpp',
            layout: 'lay'
        },
        wallpaper: {
            length: 'l',
            width: 'w',
            height: 'h',
            roll_width: 'rw',
            roll_length: 'rl',
            doors: 'd',
            windows: 'win',
            rapport: 'rap'
        }
    }[slug] || {};

    let currentValues = null;
    let currentRaw = null;
    let urlTimer = null;

    function getValue(name) {
        const radios = form.querySelectorAll('input[type="radio"][name="' + name + '"]');
        if (radios.length > 0) {
            const checked = form.querySelector('input[type="radio"][name="' + name + '"]:checked');
            return checked ? checked.value : '';
        }

        const field = form.querySelector('[name="' + name + '"]');
        return field ? String(field.value).trim() : '';
    }

    function setValue(name, value) {
        const radios = Array.from(form.querySelectorAll('input[type="radio"][name="' + name + '"]'));
        if (radios.length > 0) {
            if (radios.some(function (radio) { return radio.value === value; })) {
                radios.forEach(function (radio) {
                    radio.checked = radio.value === value;
                });
            }
            return;
        }

        const field = form.querySelector('[name="' + name + '"]');
        if (field) {
            field.value = String(value).replace(',', '.');
        }
    }

    function formatNumber(value, maxDecimals, minDecimals) {
        const maximum = typeof maxDecimals === 'number' ? maxDecimals : 2;
        const minimum = typeof minDecimals === 'number' ? minDecimals : 0;

        return new Intl.NumberFormat('ru-RU', {
            maximumFractionDigits: maximum,
            minimumFractionDigits: minimum
        }).format(Number(value) || 0);
    }

    function formatInt(value) {
        return formatNumber(value, 0, 0);
    }

    function formatArea(value) {
        return formatNumber(value, 2, 0) + ' м²';
    }

    function plural(number, one, few, many) {
        const abs = Math.abs(Math.round(Number(number))) % 100;
        const last = abs % 10;

        if (abs > 10 && abs < 20) {
            return many;
        }
        if (last > 1 && last < 5) {
            return few;
        }
        if (last === 1) {
            return one;
        }
        return many;
    }

    function readNumber(name, label, min, max, errors, options) {
        const settings = options || {};
        const raw = getValue(name).replace(',', '.');

        if (raw === '') {
            if (Object.prototype.hasOwnProperty.call(settings, 'defaultValue')) {
                return settings.defaultValue;
            }
            errors[name] = 'Укажите ' + label + '.';
            return null;
        }

        const value = Number(raw);

        if (!Number.isFinite(value)) {
            errors[name] = 'Введите число без букв и пробелов.';
            return null;
        }

        if (settings.integer && !Number.isInteger(value)) {
            errors[name] = 'Введите целое число.';
            return null;
        }

        if (value < min || value > max) {
            errors[name] = 'Допустимый диапазон: ' + formatNumber(min, 2) + '–' + formatNumber(max, 2) + '.';
            return null;
        }

        return value;
    }

    function setError(name, message) {
        const errorElement = root.querySelector('[data-error-for="' + name + '"]');
        if (errorElement) {
            errorElement.textContent = message;
        }

        form.querySelectorAll('[name="' + name + '"]').forEach(function (field) {
            field.classList.add('is-invalid');
        });
    }

    function clearErrors() {
        root.querySelectorAll('[data-error-for]').forEach(function (element) {
            element.textContent = '';
        });

        form.querySelectorAll('.is-invalid').forEach(function (field) {
            field.classList.remove('is-invalid');
        });
    }

    function updateModeBlocks() {
        if (slug !== 'tile') {
            return;
        }

        const mode = getValue('mode') === 'count' ? 'count' : 'area';
        root.querySelectorAll('[data-mode-block]').forEach(function (block) {
            block.hidden = block.getAttribute('data-mode-block') !== mode;
        });
    }

    function updateWasteBadges() {
        root.querySelectorAll('[data-waste-for]').forEach(function (element) {
            const key = element.getAttribute('data-waste-for');
            const value = Number(coefficients[key]);

            if (Number.isFinite(value)) {
                element.textContent = '+' + formatNumber(value, 1) + '%';
            }
        });
    }

    function updatePerimeter() {
        if (slug !== 'wallpaper') {
            return;
        }

        const element = root.querySelector('[data-wallpaper-perimeter]');
        if (!element) {
            return;
        }

        const length = Number(getValue('length').replace(',', '.'));
        const width = Number(getValue('width').replace(',', '.'));

        if (Number.isFinite(length) && Number.isFinite(width) && length > 0 && width > 0) {
            element.textContent = 'Периметр комнаты: ' + formatNumber(2 * (length + width), 2) + ' м';
        } else {
            element.textContent = 'Периметр комнаты: —';
        }
    }

    function validateTile() {
        const errors = {};
        const mode = getValue('mode') === 'count' ? 'count' : 'area';
        const layoutRaw = getValue('layout');
        const layout = ['straight', 'diagonal', 'offset'].indexOf(layoutRaw) >= 0 ? layoutRaw : 'straight';
        const values = { mode: mode, layout: layout };

        values.tile_length = readNumber('tile_length', 'длину плитки', 1, 200, errors);
        values.tile_width = readNumber('tile_width', 'ширину плитки', 1, 200, errors);

        if (mode === 'area') {
            values.length = readNumber('length', 'длину комнаты', 0.1, 100, errors);
            values.width = readNumber('width', 'ширину комнаты', 0.1, 100, errors);
            values.tile_count = null;
        } else {
            values.length = null;
            values.width = null;
            values.tile_count = readNumber('tile_count', 'количество плиток', 1, 100000, errors, { integer: true });
        }

        const packRaw = getValue('pack_coverage');
        if (packRaw === '') {
            values.pack_coverage = null;
        } else {
            values.pack_coverage = readNumber('pack_coverage', 'метраж упаковки', 0.1, 50, errors);
        }

        values.waste = Number(coefficients[layout]);
        if (!Number.isFinite(values.waste)) {
            values.waste = 5;
        }

        if (values.pack_coverage && values.tile_length && values.tile_width) {
            const tileArea = (values.tile_length / 100) * (values.tile_width / 100);
            if (values.pack_coverage < tileArea) {
                errors.pack_coverage = 'Метраж упаковки меньше площади одной плитки.';
            }
        }

        return { values: values, errors: errors };
    }

    function validateLaminate() {
        const errors = {};
        const layoutRaw = getValue('layout');
        const layout = ['straight', 'diagonal', 'herringbone'].indexOf(layoutRaw) >= 0 ? layoutRaw : 'straight';
        const values = { layout: layout };

        values.length = readNumber('length', 'длину комнаты', 0.1, 100, errors);
        values.width = readNumber('width', 'ширину комнаты', 0.1, 100, errors);
        values.board_length = readNumber('board_length', 'длину доски', 300, 3000, errors, { integer: true });
        values.board_width = readNumber('board_width', 'ширину доски', 50, 500, errors, { integer: true });
        values.boards_per_pack = readNumber('boards_per_pack', 'количество досок в упаковке', 1, 30, errors, { integer: true });
        values.waste = Number(coefficients[layout]);
        if (!Number.isFinite(values.waste)) {
            values.waste = 5;
        }

        return { values: values, errors: errors };
    }

    function validateWallpaper() {
        const errors = {};
        const values = {};

        values.length = readNumber('length', 'длину комнаты', 0.1, 100, errors);
        values.width = readNumber('width', 'ширину комнаты', 0.1, 100, errors);
        values.height = readNumber('height', 'высоту стен', 0.5, 10, errors);
        values.roll_width = readNumber('roll_width', 'ширину рулона', 10, 300, errors);
        values.roll_length = readNumber('roll_length', 'длину рулона', 1, 100, errors);
        values.doors = readNumber('doors', 'количество дверей', 0, 20, errors, { integer: true, defaultValue: 0 });
        values.windows = readNumber('windows', 'количество окон', 0, 20, errors, { integer: true, defaultValue: 0 });
        values.rapport = readNumber('rapport', 'раппорт', 0, 200, errors, { defaultValue: 0 });

        return { values: values, errors: errors };
    }

    function presentTile(data) {
        const rows = [];
        const breakdown = [];
        const notes = [];

        if (data.room_area !== null) {
            rows.push({ label: 'Площадь помещения', value: formatArea(data.room_area) });
        }
        rows.push({ label: 'Площадь с запасом', value: formatArea(data.order_area) });
        rows.push({ label: 'Плиток с запасом', value: formatInt(data.tiles) + ' шт.' });
        rows.push({ label: 'Запас на подрезку', value: formatNumber(data.waste, 1) + '%', accent: true });

        breakdown.push({ label: 'Площадь одной плитки', value: formatNumber(data.tile_area, 3) + ' м²' });
        breakdown.push({ label: 'Базовое количество', value: formatNumber(data.base_tiles, 1) + ' шт.' });

        if (data.tiles_per_pack) {
            breakdown.push({ label: 'Плиток в упаковке', value: formatInt(data.tiles_per_pack) + ' шт.' });
        }

        breakdown.push({ label: 'Площадь покупки', value: formatArea(data.purchase_area) });

        if (data.packs !== null && data.tiles_to_buy !== null) {
            breakdown.push({ label: 'Остаток плиток', value: formatInt(data.tiles_to_buy - data.tiles) + ' шт.' });
        }

        if (data.packs === null) {
            notes.push('Метраж упаковки не указан — показываем количество плиток.');
        }
        if (data.mode === 'count') {
            notes.push('Расчёт по количеству плиток: площадь комнаты не использовалась.');
        }
        if (data.layout === 'diagonal') {
            notes.push('Для диагональной укладки заложен повышенный запас.');
        }

        return {
            mainValue: data.packs !== null ? data.packs : data.tiles,
            mainUnit: data.packs !== null
                ? plural(data.packs, 'упаковка', 'упаковки', 'упаковок')
                : plural(data.tiles, 'плитка', 'плитки', 'плиток'),
            mainLabel: data.packs !== null
                ? formatInt(data.tiles) + ' ' + plural(data.tiles, 'плитка', 'плитки', 'плиток') + ' с запасом'
                : 'Плиток с запасом',
            rows: rows,
            breakdown: breakdown,
            note: notes.join(' '),
            raw: data
        };
    }

    function presentLaminate(data) {
        const rows = [
            { label: 'Площадь комнаты', value: formatArea(data.room_area) },
            { label: 'Площадь с запасом', value: formatArea(data.order_area) },
            { label: 'Досок с запасом', value: formatInt(data.boards_needed) + ' шт.' },
            { label: 'Запас на подрезку', value: formatNumber(data.waste, 1) + '%', accent: true }
        ];

        const breakdown = [
            { label: 'Площадь одной доски', value: formatNumber(data.board_area, 3) + ' м²' },
            { label: 'Площадь упаковки', value: formatNumber(data.pack_area, 3) + ' м²' },
            { label: 'Досок к покупке', value: formatInt(data.boards_to_buy) + ' шт.' },
            { label: 'Площадь покупки', value: formatArea(data.purchase_area) },
            { label: 'Остаток досок', value: formatInt(data.boards_to_buy - data.boards_needed) + ' шт.' }
        ];

        let note = '';
        if (data.layout === 'herringbone') {
            note = 'Для укладки ёлочкой заложен повышенный запас — проверьте направление рисунка.';
        } else if (data.layout === 'diagonal') {
            note = 'Для диагональной укладки заложен повышенный запас на торцевые подрезки.';
        }

        return {
            mainValue: data.packs,
            mainUnit: plural(data.packs, 'упаковка', 'упаковки', 'упаковок'),
            mainLabel: formatInt(data.boards_to_buy) + ' ' + plural(data.boards_to_buy, 'доска', 'доски', 'досок') + ' в упаковках',
            rows: rows,
            breakdown: breakdown,
            note: note,
            raw: data
        };
    }

    function presentWallpaper(data, values) {
        const rows = [
            { label: 'Периметр комнаты', value: formatNumber(data.perimeter, 2) + ' м' },
            { label: 'Площадь стен без проёмов', value: formatArea(data.wall_area) },
            { label: 'Полотен нужно', value: formatInt(data.strips) + ' шт.' },
            { label: 'Полотен в рулоне', value: formatInt(data.strips_per_roll) + ' шт.', accent: true }
        ];

        const breakdown = [
            { label: 'Длина полотна', value: formatNumber(data.strip_length, 2) + ' м' },
            { label: 'Площадь проёмов', value: formatArea(data.openings_area) },
            { label: 'Площадь покупки', value: formatArea(data.purchase_area) },
            { label: 'Запас по полотнам', value: formatInt(data.strips_per_roll * data.rolls - data.strips) + ' шт.' }
        ];

        const notes = [];
        if (values.rapport > 0) {
            notes.push('Раппорт ' + formatNumber(values.rapport, 1) + ' см учтён в длине каждого полотна.');
        }
        if (data.openings_area > 0) {
            notes.push('Площадь окон и дверей вычтена из площади стен.');
        }
        if (data.strips_per_roll === 1) {
            notes.push('Из одного рулона получается только одно полотно — проверьте высоту и раппорт.');
        }
        if (data.wall_area === 0) {
            notes.push('Проёмы занимают всю площадь стен — проверьте размеры.');
        }

        return {
            mainValue: data.rolls,
            mainUnit: plural(data.rolls, 'рулон', 'рулона', 'рулонов'),
            mainLabel: formatInt(data.strips) + ' ' + plural(data.strips, 'полотно', 'полотна', 'полотен') + ' нужно',
            rows: rows,
            breakdown: breakdown,
            note: notes.join(' '),
            raw: data
        };
    }

    const calculators = {
        tile: {
            validate: validateTile,
            calculate: function (values) {
                return core.calculateTile(values, coefficients);
            },
            present: presentTile
        },
        laminate: {
            validate: validateLaminate,
            calculate: function (values) {
                return core.calculateLaminate(values, coefficients);
            },
            present: presentLaminate
        },
        wallpaper: {
            validate: validateWallpaper,
            calculate: function (values) {
                return core.calculateWallpaper(values, coefficients);
            },
            present: presentWallpaper
        }
    };

    const calculator = calculators[slug];
    if (!calculator) {
        return;
    }

    function clearResult() {
        result.rows.replaceChildren();
        result.breakdown.replaceChildren();
        result.note.hidden = true;
        result.note.textContent = '';
    }

    function renderErrors(errors) {
        clearErrors();
        clearResult();

        let general = '';

        Object.keys(errors).forEach(function (name) {
            if (name === 'general') {
                general = errors[name];
                return;
            }
            setError(name, errors[name]);
        });

        result.status.textContent = 'Проверьте поля';
        result.status.classList.add('is-error');
        result.value.textContent = '—';
        result.unit.textContent = '';
        result.label.textContent = 'Исправьте выделенные поля';
        result.message.textContent = general;
        result.message.classList.toggle('is-error', general !== '');
        result.saveBtn.disabled = true;
        currentValues = null;
        currentRaw = null;
    }

    function renderResult(presentation) {
        result.status.textContent = 'Готово';
        result.status.classList.remove('is-error');
        result.value.textContent = formatInt(presentation.mainValue);
        result.unit.textContent = presentation.mainUnit;
        result.label.textContent = presentation.mainLabel;
        result.message.textContent = '';
        result.message.classList.remove('is-error');
        result.saveBtn.disabled = false;

        result.rows.replaceChildren();
        presentation.rows.forEach(function (item) {
            const row = document.createElement('div');
            row.className = 'result-row' + (item.accent ? ' result-row--accent' : '');

            const label = document.createElement('span');
            label.textContent = item.label;

            const value = document.createElement('strong');
            value.textContent = item.value;

            row.appendChild(label);
            row.appendChild(value);
            result.rows.appendChild(row);
        });

        result.breakdown.replaceChildren();
        presentation.breakdown.forEach(function (item) {
            const row = document.createElement('div');
            const term = document.createElement('dt');
            term.textContent = item.label;
            const description = document.createElement('dd');
            description.textContent = item.value;
            row.appendChild(term);
            row.appendChild(description);
            result.breakdown.appendChild(row);
        });

        result.note.textContent = presentation.note || '';
        result.note.hidden = !presentation.note;

        currentRaw = presentation.raw;
    }

    function recalc(options) {
        const settings = options || {};
        const shouldUpdateUrl = settings.updateUrl !== false;

        clearErrors();
        const validation = calculator.validate();

        if (Object.keys(validation.errors).length > 0) {
            renderErrors(validation.errors);
            return;
        }

        let raw;

        try {
            raw = calculator.calculate(validation.values);
        } catch (error) {
            renderErrors({ general: error.message || 'Не удалось выполнить расчёт.' });
            return;
        }

        const presentation = calculator.present(raw, validation.values);
        renderResult(presentation);

        currentValues = validation.values;
        currentRaw = raw;

        if (shouldUpdateUrl) {
            scheduleUrlUpdate();
        }
    }

    function buildShareUrl() {
        const url = new URL(window.location.href);
        url.search = '';
        url.hash = '';

        Object.keys(paramMap).forEach(function (field) {
            const value = getValue(field);
            if (value !== '') {
                url.searchParams.set(paramMap[field], value);
            }
        });

        return url.toString();
    }

    function scheduleUrlUpdate() {
        window.clearTimeout(urlTimer);
        urlTimer = window.setTimeout(function () {
            window.history.replaceState({}, '', buildShareUrl());
        }, 350);
    }

    function applyParamsFromUrl() {
        const params = new URLSearchParams(window.location.search);

        Object.keys(paramMap).forEach(function (field) {
            const value = params.get(paramMap[field]);
            if (value === null) {
                return;
            }
            setValue(field, value);
        });
    }

    function showMessage(message, isError) {
        result.message.textContent = message;
        result.message.classList.toggle('is-error', Boolean(isError));
    }

    async function saveCalculation() {
        if (!currentValues || !currentRaw) {
            showMessage('Сначала заполните поля без ошибок.', true);
            return;
        }

        result.saveBtn.disabled = true;
        showMessage('Сохраняем расчёт…', false);

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    calculator: slug,
                    inputs: currentValues,
                    results: currentRaw
                })
            });

            const data = await response.json().catch(function () {
                return {};
            });

            if (!response.ok) {
                throw new Error(data.error || 'Не удалось сохранить расчёт.');
            }

            showMessage((data.message || 'Расчёт сохранён.') + (data.id ? ' №' + data.id : ''), false);
        } catch (error) {
            showMessage(error.message || 'Не удалось сохранить расчёт. Попробуйте ещё раз.', true);
        } finally {
            result.saveBtn.disabled = false;
        }
    }

    async function shareCalculation() {
        const url = buildShareUrl();
        window.history.replaceState({}, '', url);

        const copied = window.StroyCalc && typeof window.StroyCalc.copyText === 'function'
            ? await window.StroyCalc.copyText(url)
            : false;

        if (copied) {
            showMessage('Ссылка на расчёт скопирована в буфер обмена.', false);
        } else {
            window.prompt('Скопируйте ссылку на расчёт:', url);
        }
    }

    function resetForm() {
        form.reset();
        updateModeBlocks();
        updateWasteBadges();
        updatePerimeter();
        window.clearTimeout(urlTimer);
        window.history.replaceState({}, '', window.location.pathname);
        recalc({ updateUrl: false });
        showMessage('Форма сброшена к значениям по умолчанию.', false);
    }

    form.addEventListener('input', function (event) {
        if (event.target && event.target.name === 'mode') {
            updateModeBlocks();
        }
        if (event.target && ['length', 'width', 'height'].indexOf(event.target.name) >= 0) {
            updatePerimeter();
        }
        recalc();
    });

    form.addEventListener('change', function (event) {
        if (event.target && event.target.name === 'mode') {
            updateModeBlocks();
        }
        recalc();
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        recalc();
    });

    if (result.saveBtn) {
        result.saveBtn.addEventListener('click', saveCalculation);
    }
    if (result.shareBtn) {
        result.shareBtn.addEventListener('click', shareCalculation);
    }
    if (result.resetBtn) {
        result.resetBtn.addEventListener('click', resetForm);
    }

    window.addEventListener('popstate', function () {
        applyParamsFromUrl();
        updateModeBlocks();
        updatePerimeter();
        recalc({ updateUrl: false });
    });

    applyParamsFromUrl();
    updateModeBlocks();
    updateWasteBadges();
    updatePerimeter();
    recalc({ updateUrl: false });
})();
