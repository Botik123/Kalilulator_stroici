/**
 * Чистые функции расчёта стройматериалов.
 * Работают и в браузере (window.StroyCalcCore), и в Node.js (module.exports).
 */
(function (root, factory) {
    if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.StroyCalcCore = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    'use strict';

    const DEFAULT_COEFFICIENTS = {
        tile: { straight: 5, diagonal: 15, offset: 10 },
        laminate: { straight: 5, diagonal: 10, herringbone: 15 },
        wallpaper: { trim_allowance: 5, door_area: 1.6, window_area: 1.96 }
    };

    class CalculationError extends Error {
        constructor(field, message) {
            super(message);
            this.name = 'CalculationError';
            this.field = field;
        }
    }

    function number(value, fallback) {
        const parsed = typeof value === 'number' ? value : parseFloat(String(value).replace(',', '.'));
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function ceil(value) {
        return Math.ceil(value - 1e-7);
    }

    function round(value, decimals) {
        const factor = Math.pow(10, decimals);
        return Math.round((value + Number.EPSILON) * factor) / factor;
    }

    function coefficient(coefficients, group, key, fallback) {
        if (coefficients && typeof coefficients === 'object' && coefficients[key] !== undefined) {
            return number(coefficients[key], fallback);
        }

        return number(DEFAULT_COEFFICIENTS[group] && DEFAULT_COEFFICIENTS[group][key], fallback);
    }

    function calculateTile(input, coefficients) {
        const mode = input.mode === 'count' ? 'count' : 'area';
        const layout = ['straight', 'diagonal', 'offset'].indexOf(input.layout) >= 0 ? input.layout : 'straight';
        const waste = coefficient(coefficients, 'tile', layout, 5);

        const tileLength = number(input.tile_length, 0);
        const tileWidth = number(input.tile_width, 0);
        const tileArea = (tileLength / 100) * (tileWidth / 100);

        if (tileArea <= 0) {
            throw new CalculationError('tile_length', 'Размеры плитки должны быть больше нуля.');
        }

        let roomArea = null;
        let baseTiles;

        if (mode === 'count') {
            baseTiles = number(input.tile_count, 0);
        } else {
            roomArea = number(input.length, 0) * number(input.width, 0);
            baseTiles = roomArea / tileArea;
        }

        if (!(baseTiles > 0)) {
            throw new CalculationError(mode === 'count' ? 'tile_count' : 'length', 'Количество плиток должно быть больше нуля.');
        }

        const baseArea = roomArea !== null ? roomArea : baseTiles * tileArea;
        const orderArea = baseArea * (1 + waste / 100);
        const tiles = ceil(baseTiles * (1 + waste / 100));

        let tilesPerPack = null;
        let packs = null;
        let tilesToBuy = null;
        let purchaseArea = tiles * tileArea;

        const packCoverage = number(input.pack_coverage, 0);
        if (packCoverage > 0) {
            tilesPerPack = Math.floor(packCoverage / tileArea + 1e-9);
            if (tilesPerPack < 1) {
                throw new CalculationError('pack_coverage', 'Метраж упаковки меньше площади одной плитки.');
            }
            packs = ceil(tiles / tilesPerPack);
            tilesToBuy = packs * tilesPerPack;
            purchaseArea = tilesToBuy * tileArea;
        }

        return {
            mode: mode,
            layout: layout,
            waste: waste,
            room_area: roomArea !== null ? round(roomArea, 4) : null,
            tile_area: round(tileArea, 6),
            base_tiles: round(baseTiles, 3),
            order_area: round(orderArea, 4),
            tiles: tiles,
            tiles_per_pack: tilesPerPack,
            packs: packs,
            tiles_to_buy: tilesToBuy,
            purchase_area: round(purchaseArea, 4)
        };
    }

    function calculateLaminate(input, coefficients) {
        const layout = ['straight', 'diagonal', 'herringbone'].indexOf(input.layout) >= 0 ? input.layout : 'straight';
        const waste = coefficient(coefficients, 'laminate', layout, 5);

        const roomArea = number(input.length, 0) * number(input.width, 0);
        const boardLength = number(input.board_length, 0) / 1000;
        const boardWidth = number(input.board_width, 0) / 1000;
        const boardsPerPack = number(input.boards_per_pack, 0);
        const boardArea = boardLength * boardWidth;
        const packArea = boardArea * boardsPerPack;

        if (roomArea <= 0) {
            throw new CalculationError('length', 'Площадь комнаты должна быть больше нуля.');
        }
        if (boardArea <= 0) {
            throw new CalculationError('board_length', 'Размеры доски должны быть больше нуля.');
        }
        if (boardsPerPack < 1) {
            throw new CalculationError('boards_per_pack', 'В упаковке должна быть хотя бы одна доска.');
        }

        const baseBoards = roomArea / boardArea;
        const boardsNeeded = ceil(baseBoards * (1 + waste / 100));
        const packs = ceil(boardsNeeded / boardsPerPack);
        const boardsToBuy = packs * boardsPerPack;
        const orderArea = roomArea * (1 + waste / 100);
        const purchaseArea = packs * packArea;

        return {
            layout: layout,
            waste: waste,
            room_area: round(roomArea, 4),
            board_area: round(boardArea, 6),
            pack_area: round(packArea, 6),
            base_boards: round(baseBoards, 3),
            boards_needed: boardsNeeded,
            packs: packs,
            boards_to_buy: boardsToBuy,
            order_area: round(orderArea, 4),
            purchase_area: round(purchaseArea, 4)
        };
    }

    function calculateWallpaper(input, coefficients) {
        const length = number(input.length, 0);
        const width = number(input.width, 0);
        const height = number(input.height, 0);
        const rollWidth = number(input.roll_width, 0) / 100;
        const rollLength = number(input.roll_length, 0);
        const doors = number(input.doors, 0);
        const windows = number(input.windows, 0);
        const rapport = number(input.rapport, 0) / 100;
        const trim = coefficient(coefficients, 'wallpaper', 'trim_allowance', 5) / 100;
        const doorArea = coefficient(coefficients, 'wallpaper', 'door_area', 1.6);
        const windowArea = coefficient(coefficients, 'wallpaper', 'window_area', 1.96);

        if (length <= 0 || width <= 0 || height <= 0) {
            throw new CalculationError('height', 'Размеры комнаты должны быть больше нуля.');
        }
        if (rollWidth <= 0 || rollLength <= 0) {
            throw new CalculationError('roll_width', 'Размеры рулона должны быть больше нуля.');
        }

        const perimeter = 2 * (length + width);
        const openingsArea = doors * doorArea + windows * windowArea;
        const wallArea = Math.max(0, perimeter * height - openingsArea);
        const stripLength = height + rapport + trim;

        if (stripLength > rollLength + 1e-9) {
            throw new CalculationError('roll_length', 'Полотно длиннее рулона. Уменьшите раппорт или выберите рулон длиннее.');
        }

        const stripsPerRoll = Math.floor(rollLength / stripLength + 1e-9);
        if (stripsPerRoll < 1) {
            throw new CalculationError('roll_length', 'Из рулона не получается ни одного полотна.');
        }

        const strips = wallArea > 0 ? ceil(wallArea / (rollWidth * height)) : 0;
        const rolls = strips > 0 ? ceil(strips / stripsPerRoll) : 0;
        const purchaseArea = rolls * rollWidth * rollLength;

        return {
            perimeter: round(perimeter, 4),
            openings_area: round(openingsArea, 4),
            wall_area: round(wallArea, 4),
            strip_length: round(stripLength, 4),
            strips_per_roll: stripsPerRoll,
            strips: strips,
            rolls: rolls,
            purchase_area: round(purchaseArea, 4)
        };
    }

    return {
        DEFAULT_COEFFICIENTS: DEFAULT_COEFFICIENTS,
        CalculationError: CalculationError,
        calculateTile: calculateTile,
        calculateLaminate: calculateLaminate,
        calculateWallpaper: calculateWallpaper,
        ceil: ceil,
        round: round
    };
}));
