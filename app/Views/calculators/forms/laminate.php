<?php
$laminateDefaults = $calculator['defaults'];
$laminateCoefficients = $calculator['coefficients'];
?>
<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">01</span>
        <div>
            <h3>Помещение</h3>
            <p>Длина и ширина комнаты</p>
        </div>
    </div>

    <div class="form-grid">
        <div class="field">
            <div class="label-row">
                <label for="laminate-length">Длина комнаты</label>
            </div>
            <div class="input-wrap">
                <input id="laminate-length" name="length" type="number" inputmode="decimal" min="0.1" max="100" step="0.01" value="<?= e($laminateDefaults['length']) ?>" autocomplete="off" aria-describedby="laminate-length-hint">
                <span class="input-suffix">м</span>
            </div>
            <p class="field-hint" id="laminate-length-hint">Например, 5</p>
            <p class="field-error" data-error-for="length" aria-live="polite"></p>
        </div>

        <div class="field">
            <div class="label-row">
                <label for="laminate-width">Ширина комнаты</label>
            </div>
            <div class="input-wrap">
                <input id="laminate-width" name="width" type="number" inputmode="decimal" min="0.1" max="100" step="0.01" value="<?= e($laminateDefaults['width']) ?>" autocomplete="off" aria-describedby="laminate-width-hint">
                <span class="input-suffix">м</span>
            </div>
            <p class="field-hint" id="laminate-width-hint">Ширина пола в метрах</p>
            <p class="field-error" data-error-for="width" aria-live="polite"></p>
        </div>
    </div>
</div>

<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">02</span>
        <div>
            <h3>Ламинат</h3>
            <p>Размеры доски и наполнение упаковки</p>
        </div>
    </div>

    <div class="form-grid">
        <div class="field">
            <div class="label-row">
                <label for="laminate-board-length">Длина доски</label>
            </div>
            <div class="input-wrap">
                <input id="laminate-board-length" name="board_length" type="number" inputmode="decimal" min="300" max="3000" step="1" value="<?= e($laminateDefaults['board_length']) ?>" autocomplete="off" aria-describedby="laminate-board-length-hint">
                <span class="input-suffix">мм</span>
            </div>
            <p class="field-hint" id="laminate-board-length-hint">Обычно 1200–1400 мм</p>
            <p class="field-error" data-error-for="board_length" aria-live="polite"></p>
        </div>

        <div class="field">
            <div class="label-row">
                <label for="laminate-board-width">Ширина доски</label>
            </div>
            <div class="input-wrap">
                <input id="laminate-board-width" name="board_width" type="number" inputmode="decimal" min="50" max="500" step="1" value="<?= e($laminateDefaults['board_width']) ?>" autocomplete="off" aria-describedby="laminate-board-width-hint">
                <span class="input-suffix">мм</span>
            </div>
            <p class="field-hint" id="laminate-board-width-hint">Обычно 190–200 мм</p>
            <p class="field-error" data-error-for="board_width" aria-live="polite"></p>
        </div>

        <div class="field field--full">
            <div class="label-row">
                <label for="laminate-boards">Досок в упаковке</label>
                <button class="tooltip" type="button" aria-label="Подсказка" data-tip="Количество досок указано на торце упаковки. Чаще всего 8–10 штук.">?</button>
            </div>
            <div class="input-wrap">
                <input id="laminate-boards" name="boards_per_pack" type="number" inputmode="numeric" min="1" max="30" step="1" value="<?= e($laminateDefaults['boards_per_pack']) ?>" autocomplete="off" aria-describedby="laminate-boards-hint">
                <span class="input-suffix">шт.</span>
            </div>
            <p class="field-hint" id="laminate-boards-hint">Проверьте на упаковке — у разных коллекций по-разному</p>
            <p class="field-error" data-error-for="boards_per_pack" aria-live="polite"></p>
        </div>
    </div>
</div>

<div class="form-block">
    <div class="form-block-head">
        <span class="form-step">03</span>
        <div>
            <h3>Схема укладки</h3>
            <p>Ёлочка и диагональ требуют больше запаса</p>
        </div>
    </div>

    <fieldset class="field field--full">
        <legend class="sr-only">Схема укладки ламината</legend>
        <div class="segmented segmented--3 segmented--layouts">
            <label class="segment">
                <input type="radio" name="layout" value="straight"<?= $laminateDefaults['layout'] === 'straight' ? ' checked' : '' ?>>
                <span class="segment-body">
                    <strong>Прямая</strong>
                    <small>Вдоль или поперёк комнаты</small>
                    <em class="waste-badge" data-waste-for="straight">+<?= e(format_number((float) $laminateCoefficients['straight'], 0)) ?>%</em>
                </span>
            </label>
            <label class="segment">
                <input type="radio" name="layout" value="diagonal"<?= $laminateDefaults['layout'] === 'diagonal' ? ' checked' : '' ?>>
                <span class="segment-body">
                    <strong>По диагонали</strong>
                    <small>Под 45 градусов</small>
                    <em class="waste-badge" data-waste-for="diagonal">+<?= e(format_number((float) $laminateCoefficients['diagonal'], 0)) ?>%</em>
                </span>
            </label>
            <label class="segment">
                <input type="radio" name="layout" value="herringbone"<?= $laminateDefaults['layout'] === 'herringbone' ? ' checked' : '' ?>>
                <span class="segment-body">
                    <strong>Ёлочкой</strong>
                    <small>Классический узор</small>
                    <em class="waste-badge" data-waste-for="herringbone">+<?= e(format_number((float) $laminateCoefficients['herringbone'], 0)) ?>%</em>
                </span>
            </label>
        </div>
    </fieldset>
</div>
