<?php
$tileDefaults = $calculator['defaults'];
$tileCoefficients = $calculator['coefficients'];
?>
<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">01</span>
        <div>
            <h3>Помещение</h3>
            <p>Размеры пола или готовое количество плиток</p>
        </div>
    </div>

    <fieldset class="field field--full">
        <legend>Тип расчёта</legend>
        <div class="segmented segmented--2">
            <label class="segment">
                <input type="radio" name="mode" value="area"<?= $tileDefaults['mode'] === 'area' ? ' checked' : '' ?>>
                <span class="segment-body">
                    <strong>По площади пола</strong>
                    <small>Знаю длину и ширину комнаты</small>
                </span>
            </label>
            <label class="segment">
                <input type="radio" name="mode" value="count"<?= $tileDefaults['mode'] === 'count' ? ' checked' : '' ?>>
                <span class="segment-body">
                    <strong>По количеству плиток</strong>
                    <small>Есть готовое число без запаса</small>
                </span>
            </label>
        </div>
    </fieldset>

    <div class="form-grid" data-mode-block="area"<?= $tileDefaults['mode'] === 'count' ? ' hidden' : '' ?>>
        <div class="field">
            <div class="label-row">
                <label for="tile-length">Длина комнаты</label>
            </div>
            <div class="input-wrap">
                <input id="tile-length" name="length" type="number" inputmode="decimal" min="0.1" max="100" step="0.01" value="<?= e($tileDefaults['length']) ?>" autocomplete="off" aria-describedby="tile-length-hint">
                <span class="input-suffix">м</span>
            </div>
            <p class="field-hint" id="tile-length-hint">Например, 4,2</p>
            <p class="field-error" data-error-for="length" aria-live="polite"></p>
        </div>

        <div class="field">
            <div class="label-row">
                <label for="tile-width">Ширина комнаты</label>
            </div>
            <div class="input-wrap">
                <input id="tile-width" name="width" type="number" inputmode="decimal" min="0.1" max="100" step="0.01" value="<?= e($tileDefaults['width']) ?>" autocomplete="off" aria-describedby="tile-width-hint">
                <span class="input-suffix">м</span>
            </div>
            <p class="field-hint" id="tile-width-hint">Ширина пола в метрах</p>
            <p class="field-error" data-error-for="width" aria-live="polite"></p>
        </div>
    </div>

    <div class="form-grid" data-mode-block="count"<?= $tileDefaults['mode'] === 'area' ? ' hidden' : '' ?>>
        <div class="field field--full">
            <div class="label-row">
                <label for="tile-count">Количество плиток без запаса</label>
                <button class="tooltip" type="button" aria-label="Подсказка" data-tip="Введите число плиток, которое получилось при расчёте по площади. Калькулятор добавит запас на подрезку.">?</button>
            </div>
            <div class="input-wrap">
                <input id="tile-count" name="tile_count" type="number" inputmode="numeric" min="1" max="100000" step="1" value="<?= e($tileDefaults['tile_count']) ?>" autocomplete="off" aria-describedby="tile-count-hint">
                <span class="input-suffix">шт.</span>
            </div>
            <p class="field-hint" id="tile-count-hint">Запас на подрезку будет добавлен автоматически</p>
            <p class="field-error" data-error-for="tile_count" aria-live="polite"></p>
        </div>
    </div>
</div>

<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">02</span>
        <div>
            <h3>Плитка</h3>
            <p>Размер одной плитки и упаковка</p>
        </div>
    </div>

    <div class="form-grid">
        <div class="field">
            <div class="label-row">
                <label for="tile-length-size">Длина плитки</label>
            </div>
            <div class="input-wrap">
                <input id="tile-length-size" name="tile_length" type="number" inputmode="decimal" min="1" max="200" step="0.1" value="<?= e($tileDefaults['tile_length']) ?>" autocomplete="off" aria-describedby="tile-size-hint">
                <span class="input-suffix">см</span>
            </div>
            <p class="field-hint" id="tile-size-hint">Например, 30 для плитки 30 × 30</p>
            <p class="field-error" data-error-for="tile_length" aria-live="polite"></p>
        </div>

        <div class="field">
            <div class="label-row">
                <label for="tile-width-size">Ширина плитки</label>
            </div>
            <div class="input-wrap">
                <input id="tile-width-size" name="tile_width" type="number" inputmode="decimal" min="1" max="200" step="0.1" value="<?= e($tileDefaults['tile_width']) ?>" autocomplete="off" aria-describedby="tile-width-size-hint">
                <span class="input-suffix">см</span>
            </div>
            <p class="field-hint" id="tile-width-size-hint">Размер по короткой стороне</p>
            <p class="field-error" data-error-for="tile_width" aria-live="polite"></p>
        </div>

        <div class="field field--full">
            <div class="label-row">
                <label for="tile-pack">Метраж упаковки</label>
                <button class="tooltip" type="button" aria-label="Подсказка" data-tip="Указан на упаковке. Если не знаете — оставьте поле пустым: калькулятор покажет количество плиток без упаковок.">?</button>
            </div>
            <div class="input-wrap">
                <input id="tile-pack" name="pack_coverage" type="number" inputmode="decimal" min="0.1" max="50" step="0.01" value="<?= e($tileDefaults['pack_coverage']) ?>" autocomplete="off" aria-describedby="tile-pack-hint">
                <span class="input-suffix">м²</span>
            </div>
            <p class="field-hint" id="tile-pack-hint">Необязательно. Например, 1,44 м² для упаковки из 16 плиток 30 × 30 см</p>
            <p class="field-error" data-error-for="pack_coverage" aria-live="polite"></p>
        </div>
    </div>
</div>

<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">03</span>
        <div>
            <h3>Способ укладки</h3>
            <p>От схемы зависит запас на подрезку</p>
        </div>
    </div>

    <fieldset class="field field--full">
        <legend class="sr-only">Способ укладки плитки</legend>
        <div class="segmented segmented--3 segmented--layouts">
            <label class="segment">
                <input type="radio" name="layout" value="straight"<?= $tileDefaults['layout'] === 'straight' ? ' checked' : '' ?>>
                <span class="segment-body">
                    <strong>Прямая</strong>
                    <small>Рядами без смещения</small>
                    <em class="waste-badge" data-waste-for="straight">+<?= e(format_number((float) $tileCoefficients['straight'], 0)) ?>%</em>
                </span>
            </label>
            <label class="segment">
                <input type="radio" name="layout" value="offset"<?= $tileDefaults['layout'] === 'offset' ? ' checked' : '' ?>>
                <span class="segment-body">
                    <strong>Со смещением</strong>
                    <small>Шахматный порядок</small>
                    <em class="waste-badge" data-waste-for="offset">+<?= e(format_number((float) $tileCoefficients['offset'], 0)) ?>%</em>
                </span>
            </label>
            <label class="segment">
                <input type="radio" name="layout" value="diagonal"<?= $tileDefaults['layout'] === 'diagonal' ? ' checked' : '' ?>>
                <span class="segment-body">
                    <strong>По диагонали</strong>
                    <small>Под 45 градусов</small>
                    <em class="waste-badge" data-waste-for="diagonal">+<?= e(format_number((float) $tileCoefficients['diagonal'], 0)) ?>%</em>
                </span>
            </label>
        </div>
    </fieldset>
</div>
