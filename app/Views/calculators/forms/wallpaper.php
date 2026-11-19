<?php
$wallpaperDefaults = $calculator['defaults'];
$wallpaperCoefficients = $calculator['coefficients'];
$doorArea = format_number((float) $wallpaperCoefficients['door_area']);
$windowArea = format_number((float) $wallpaperCoefficients['window_area']);
?>
<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">01</span>
        <div>
            <h3>Комната</h3>
            <p>Размеры помещения и высота стен</p>
        </div>
    </div>

    <div class="form-grid">
        <div class="field">
            <div class="label-row">
                <label for="wallpaper-length">Длина комнаты</label>
            </div>
            <div class="input-wrap">
                <input id="wallpaper-length" name="length" type="number" inputmode="decimal" min="0.1" max="100" step="0.01" value="<?= e($wallpaperDefaults['length']) ?>" autocomplete="off">
                <span class="input-suffix">м</span>
            </div>
            <p class="field-hint">Например, 4</p>
            <p class="field-error" data-error-for="length" aria-live="polite"></p>
        </div>

        <div class="field">
            <div class="label-row">
                <label for="wallpaper-width">Ширина комнаты</label>
            </div>
            <div class="input-wrap">
                <input id="wallpaper-width" name="width" type="number" inputmode="decimal" min="0.1" max="100" step="0.01" value="<?= e($wallpaperDefaults['width']) ?>" autocomplete="off">
                <span class="input-suffix">м</span>
            </div>
            <p class="field-hint">Например, 5</p>
            <p class="field-error" data-error-for="width" aria-live="polite"></p>
        </div>

        <div class="field field--full">
            <div class="label-row">
                <label for="wallpaper-height">Высота стен</label>
            </div>
            <div class="input-wrap">
                <input id="wallpaper-height" name="height" type="number" inputmode="decimal" min="0.5" max="10" step="0.01" value="<?= e($wallpaperDefaults['height']) ?>" autocomplete="off" aria-describedby="wallpaper-height-hint">
                <span class="input-suffix">м</span>
            </div>
            <p class="field-hint" id="wallpaper-height-hint">От пола до потолка, без плинтуса</p>
            <p class="field-error" data-error-for="height" aria-live="polite"></p>
        </div>
    </div>

    <p class="form-live-summary" data-wallpaper-perimeter>Периметр комнаты: 18 м</p>
</div>

<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">02</span>
        <div>
            <h3>Рулон</h3>
            <p>Размеры обоев с этикетки</p>
        </div>
    </div>

    <div class="form-grid">
        <div class="field">
            <div class="label-row">
                <label for="wallpaper-roll-width">Ширина рулона</label>
            </div>
            <div class="input-wrap">
                <input id="wallpaper-roll-width" name="roll_width" type="number" inputmode="decimal" min="10" max="300" step="0.1" value="<?= e($wallpaperDefaults['roll_width']) ?>" autocomplete="off" aria-describedby="wallpaper-roll-width-hint">
                <span class="input-suffix">см</span>
            </div>
            <p class="field-hint" id="wallpaper-roll-width-hint">Стандарт — 53 см, широкие — 106 см</p>
            <p class="field-error" data-error-for="roll_width" aria-live="polite"></p>
        </div>

        <div class="field">
            <div class="label-row">
                <label for="wallpaper-roll-length">Длина рулона</label>
            </div>
            <div class="input-wrap">
                <input id="wallpaper-roll-length" name="roll_length" type="number" inputmode="decimal" min="1" max="100" step="0.1" value="<?= e($wallpaperDefaults['roll_length']) ?>" autocomplete="off" aria-describedby="wallpaper-roll-length-hint">
                <span class="input-suffix">м</span>
            </div>
            <p class="field-hint" id="wallpaper-roll-length-hint">Обычно 10 или 10,05 м</p>
            <p class="field-error" data-error-for="roll_length" aria-live="polite"></p>
        </div>
    </div>
</div>

<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">03</span>
        <div>
            <h3>Проёмы и рисунок</h3>
            <p>Окна, двери и раппорт обоев</p>
        </div>
    </div>

    <div class="form-grid">
        <div class="field">
            <div class="label-row">
                <label for="wallpaper-doors">Двери</label>
                <button class="tooltip" type="button" aria-label="Подсказка" data-tip="Считаем стандартную дверь площадью <?= e($doorArea) ?> м². Если проём нестандартный, измените коэффициент в настройках.">?</button>
            </div>
            <div class="input-wrap">
                <input id="wallpaper-doors" name="doors" type="number" inputmode="numeric" min="0" max="20" step="1" value="<?= e($wallpaperDefaults['doors']) ?>" autocomplete="off" aria-describedby="wallpaper-doors-hint">
                <span class="input-suffix">шт.</span>
            </div>
            <p class="field-hint" id="wallpaper-doors-hint">Площадь двери — <?= e($doorArea) ?> м²</p>
            <p class="field-error" data-error-for="doors" aria-live="polite"></p>
        </div>

        <div class="field">
            <div class="label-row">
                <label for="wallpaper-windows">Окна</label>
                <button class="tooltip" type="button" aria-label="Подсказка" data-tip="Считаем стандартное окно площадью <?= e($windowArea) ?> м². Если проём нестандартный, измените коэффициент в настройках.">?</button>
            </div>
            <div class="input-wrap">
                <input id="wallpaper-windows" name="windows" type="number" inputmode="numeric" min="0" max="20" step="1" value="<?= e($wallpaperDefaults['windows']) ?>" autocomplete="off" aria-describedby="wallpaper-windows-hint">
                <span class="input-suffix">шт.</span>
            </div>
            <p class="field-hint" id="wallpaper-windows-hint">Площадь окна — <?= e($windowArea) ?> м²</p>
            <p class="field-error" data-error-for="windows" aria-live="polite"></p>
        </div>

        <div class="field field--full">
            <div class="label-row">
                <label for="wallpaper-rapport">Раппорт</label>
                <button class="tooltip" type="button" aria-label="Что такое раппорт?" data-tip="Шаг повторяющегося рисунка. Указан на этикетке обоев. Если рисунка нет, оставьте 0.">?</button>
            </div>
            <div class="input-wrap">
                <input id="wallpaper-rapport" name="rapport" type="number" inputmode="decimal" min="0" max="200" step="0.1" value="<?= e($wallpaperDefaults['rapport']) ?>" autocomplete="off" aria-describedby="wallpaper-rapport-hint">
                <span class="input-suffix">см</span>
            </div>
            <p class="field-hint" id="wallpaper-rapport-hint">0 — без рисунка. Например, 32 см для обоев с повтором узора</p>
            <p class="field-error" data-error-for="rapport" aria-live="polite"></p>
        </div>
    </div>
</div>
