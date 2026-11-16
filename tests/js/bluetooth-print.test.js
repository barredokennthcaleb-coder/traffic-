/**
 * Encoder tests for public/js/bluetooth-print.js.
 *
 * These verify the parts that must be byte-perfect on paper — the 1-bit
 * raster packing (GS v 0), the blank-paper trim, the side-padding crop and
 * the ESC/POS command frame — without needing a real printer or a browser.
 * A tiny fake canvas provides getImageData/drawImage in plain Node.
 *
 * Run:  node tests/js/bluetooth-print.test.js
 */
'use strict';

const assert = require('assert');
const path = require('path');

// ── Fake DOM just big enough to load the module ──────────────────────────

function makeFakeCanvas(initialWidth, initialHeight) {
    const canvas = {
        _w: initialWidth || 300,
        _h: initialHeight || 150,
        _pixels: new Uint8ClampedArray((initialWidth || 300) * (initialHeight || 150) * 4)
    };

    function resize() {
        canvas._pixels = new Uint8ClampedArray(canvas._w * canvas._h * 4);
    }

    // Browsers clear a canvas when width/height are assigned; mirror that.
    Object.defineProperty(canvas, 'width', {
        get: () => canvas._w,
        set: (value) => { if (value !== canvas._w) { canvas._w = value; resize(); } }
    });
    Object.defineProperty(canvas, 'height', {
        get: () => canvas._h,
        set: (value) => { if (value !== canvas._h) { canvas._h = value; resize(); } }
    });

    canvas.getContext = () => ({
        fillStyle: '#ffffff',
        imageSmoothingEnabled: false,
        imageSmoothingQuality: 'low',
        fillRect(x, y, w, h) {
            for (let yy = y; yy < y + h; yy++) {
                for (let xx = x; xx < x + w; xx++) {
                    if (xx < 0 || yy < 0 || xx >= canvas._w || yy >= canvas._h) continue;
                    const i = (yy * canvas._w + xx) * 4;
                    canvas._pixels[i] = 255;
                    canvas._pixels[i + 1] = 255;
                    canvas._pixels[i + 2] = 255;
                    canvas._pixels[i + 3] = 255;
                }
            }
        },
        drawImage(img, sx, sy, sw, sh, dx, dy, dw, dh) {
            sx = sx || 0; sy = sy || 0; dx = dx || 0; dy = dy || 0;
            sw = sw == null ? img.width : sw;
            sh = sh == null ? img.height : sh;
            dw = dw == null ? sw : dw;
            dh = dh == null ? sh : dh;
            for (let y = 0; y < dh; y++) {
                for (let x = 0; x < dw; x++) {
                    // Nearest neighbour: enough for the crop/scale checks.
                    const srcX = Math.min(img.width - 1, Math.floor(sx + (x * sw) / dw));
                    const srcY = Math.min(img.height - 1, Math.floor(sy + (y * sh) / dh));
                    const tx = dx + x;
                    const ty = dy + y;
                    if (tx < 0 || ty < 0 || tx >= canvas._w || ty >= canvas._h) continue;
                    if (srcX < 0 || srcY < 0) continue;
                    const si = (srcY * img.width + srcX) * 4;
                    const di = (ty * canvas._w + tx) * 4;
                    canvas._pixels[di] = img._pixels[si];
                    canvas._pixels[di + 1] = img._pixels[si + 1];
                    canvas._pixels[di + 2] = img._pixels[si + 2];
                    canvas._pixels[di + 3] = img._pixels[si + 3];
                }
            }
        },
        getImageData(x, y, w, h) {
            const data = new Uint8ClampedArray(w * h * 4);
            for (let yy = 0; yy < h; yy++) {
                for (let xx = 0; xx < w; xx++) {
                    const si = ((yy + y) * canvas._w + (xx + x)) * 4;
                    const di = (yy * w + xx) * 4;
                    data[di] = canvas._pixels[si];
                    data[di + 1] = canvas._pixels[si + 1];
                    data[di + 2] = canvas._pixels[si + 2];
                    data[di + 3] = canvas._pixels[si + 3];
                }
            }
            return { data };
        }
    });

    canvas.putPixel = (x, y, r, g, b, a) => {
        const i = (y * canvas._w + x) * 4;
        canvas._pixels[i] = r;
        canvas._pixels[i + 1] = g;
        canvas._pixels[i + 2] = b;
        canvas._pixels[i + 3] = a == null ? 255 : a;
    };

    return canvas;
}

function fill(canvas, r, g, b, a) {
    for (let y = 0; y < canvas.height; y++) {
        for (let x = 0; x < canvas.width; x++) canvas.putPixel(x, y, r, g, b, a);
    }
}

// Minimal window/document for the module's top-level IIFE.
global.window = {
    setTimeout: setTimeout,
    isSecureContext: true,
    innerWidth: 800,
    innerHeight: 600,
    btoa: (binary) => Buffer.from(binary, 'binary').toString('base64'),
    localStorage: {
        _store: {},
        getItem(k) { return Object.prototype.hasOwnProperty.call(this._store, k) ? this._store[k] : null; },
        setItem(k, v) { this._store[k] = String(v); },
        removeItem(k) { delete this._store[k]; }
    },
    requestAnimationFrame: (cb) => setTimeout(cb, 0)
};
global.document = {
    currentScript: null,
    getElementsByTagName: () => [],
    createElement: (tag) => {
        if (tag === 'canvas') return makeFakeCanvas(300, 150);
        return {};
    }
};
// Node ships its own (Bluetooth-less) navigator; that is fine — the tests
// below only exercise the pure byte helpers, not device discovery.
require(path.join(__dirname, '..', '..', 'public', 'js', 'bluetooth-print.js'));

const internals = global.window.TrafficBlePrint && global.window.TrafficBlePrint.__internals;
assert.ok(internals, 'bluetooth-print.js should export __internals');

const { canvasToRaster, cropRaster, cropHorizontal, escposCommands, bytesToBase64 } = internals;

let passed = 0;
function test(name, fn) {
    fn();
    passed += 1;
    console.log('  ✓ ' + name);
}

console.log('bluetooth-print encoder tests');

// ── 1-bit packing (GS v 0 order: MSB = leftmost dot) ─────────────────────

test('packs black pixels MSB-first, one byte per 8 dots', () => {
    const canvas = makeFakeCanvas(8, 2);
    fill(canvas, 255, 255, 255, 255);
    canvas.putPixel(0, 1, 0, 0, 0, 255); // leftmost dot of row 1

    const raster = canvasToRaster(canvas, 168);
    assert.strictEqual(raster.width, 8);
    assert.strictEqual(raster.height, 2);
    assert.strictEqual(raster.bytesPerRow, 1);
    assert.strictEqual(raster.packed[0], 0x00, 'blank row stays clear');
    assert.strictEqual(raster.packed[1], 0x80, 'leftmost dot sets the MSB');
});

test('thresholds greys and skips transparent pixels', () => {
    const canvas = makeFakeCanvas(8, 1);
    fill(canvas, 255, 255, 255, 255);
    canvas.putPixel(1, 0, 100, 100, 100, 255); // dark grey -> ink
    canvas.putPixel(2, 0, 240, 240, 240, 255); // light grey -> paper
    canvas.putPixel(3, 0, 0, 0, 0, 0);         // transparent -> paper

    const raster = canvasToRaster(canvas, 168);
    assert.strictEqual(raster.packed[0], 0x40, 'only the dark grey dot prints');
});

test('packs rows wider than one byte across bytes', () => {
    const canvas = makeFakeCanvas(16, 1);
    fill(canvas, 255, 255, 255, 255);
    canvas.putPixel(9, 0, 0, 0, 0, 255); // second byte, bit 6

    const raster = canvasToRaster(canvas, 168);
    assert.strictEqual(raster.bytesPerRow, 2);
    assert.strictEqual(raster.packed[0], 0x00);
    assert.strictEqual(raster.packed[1], 0x40);
});

// ── Blank-paper trim ──────────────────────────────────────────────────────

test('crops the blank rows above and below the ink (plus pad)', () => {
    const canvas = makeFakeCanvas(8, 6);
    fill(canvas, 255, 255, 255, 255);
    canvas.putPixel(3, 3, 0, 0, 0, 255); // only row 3 has ink

    const raster = canvasToRaster(canvas, 168);
    const trimmed = cropRaster(raster, 1);
    assert.strictEqual(trimmed.height, 3, 'rows 2..4 remain with pad 1');
    assert.strictEqual(trimmed.packed.length, 3);
    assert.strictEqual(trimmed.packed[1], 0x10, 'middle trimmed row keeps its dot');
});

test('keeps a fully blank page intact so the printer still feeds', () => {
    const canvas = makeFakeCanvas(8, 4);
    fill(canvas, 255, 255, 255, 255);
    const trimmed = cropRaster(canvasToRaster(canvas, 168), 1);
    assert.strictEqual(trimmed.height, 4);
});

// ── Side-padding crop (58mm content mapping) ─────────────────────────────

test('crops the side padding at the captured resolution', () => {
    const canvas = makeFakeCanvas(20, 2);
    fill(canvas, 0, 0, 0, 255); // all black so the copy is easy to verify

    const cropped = cropHorizontal(canvas, 5, 8);
    assert.strictEqual(cropped.width, 8);
    assert.strictEqual(cropped.height, 2);
    assert.strictEqual(cropped.getContext('2d').getImageData(0, 0, 1, 1).data[0], 0);
    assert.strictEqual(cropped.getContext('2d').getImageData(0, 0, 1, 1).data[3], 255);
});

test('clamps the crop when rounding overshoots the right edge', () => {
    const canvas = makeFakeCanvas(20, 2);
    fill(canvas, 0, 0, 0, 255);
    const cropped = cropHorizontal(canvas, 15, 8); // 15 + 8 > 20 -> clamp start
    assert.strictEqual(cropped.width, 8);
    assert.strictEqual(cropped.height, 2);
});

// ── ESC/POS frame ─────────────────────────────────────────────────────────

test('builds init + GS v 0 header + rows + feed', () => {
    const raster = { width: 8, height: 2, bytesPerRow: 1, packed: new Uint8Array([0x00, 0x80]) };
    const bytes = escposCommands(raster, { feedLines: 2 });

    assert.deepStrictEqual(Array.from(bytes), [
        0x1B, 0x40,                   // ESC @
        0x1D, 0x76, 0x30, 0x00,       // GS v 0, mode 0
        0x01, 0x00,                   // xL xH = 1 byte per row
        0x02, 0x00,                   // yL yH = 2 rows
        0x00, 0x80,                   // the bitmap itself
        0x0A, 0x0A                    // two feed lines
    ]);
});

test('adds the cut command only when asked', () => {
    const raster = { width: 8, height: 1, bytesPerRow: 1, packed: new Uint8Array([0x00]) };
    const plain = escposCommands(raster, { feedLines: 0 });
    const cut = escposCommands(raster, { feedLines: 0, cut: true });

    assert.strictEqual(plain.length, 11); // 10-byte frame + 1 data byte
    assert.strictEqual(cut.length, 15);   // + GS V B 0
    assert.deepStrictEqual(Array.from(cut.slice(-4)), [0x1D, 0x56, 0x42, 0x00]);
});

test('encodes the command stream as RawBT-compatible base64', () => {
    assert.strictEqual(bytesToBase64(new Uint8Array([0x1B, 0x40])), 'G0A=');
});

// ── Full chain ────────────────────────────────────────────────────────────

test('end to end: canvas -> crop padding -> raster -> ESC/POS', () => {
    // A fake 24-dot strip with 4-dot side paddings; content = 16 dots.
    const canvas = makeFakeCanvas(24, 10);
    fill(canvas, 255, 255, 255, 255);
    for (let y = 2; y <= 7; y++) {
        for (let x = 4; x < 20; x += 2) canvas.putPixel(x, y, 0, 0, 0, 255); // dashed border
    }

    const cropped = cropHorizontal(canvas, 4, 16); // strip the side padding
    assert.strictEqual(cropped.width, 16);

    const raster = cropRaster(canvasToRaster(cropped, 168), 6);
    const bytes = escposCommands(raster, { feedLines: 3 });

    assert.strictEqual(raster.bytesPerRow, 2);
    assert.ok(bytes.length > 9, 'commands contain the bitmap');
    assert.strictEqual(bytes[0], 0x1B);
    assert.strictEqual(bytes[1], 0x40);
    const dataLength = raster.bytesPerRow * raster.height;
    assert.strictEqual(bytes.length, 10 + dataLength + 3);
});

console.log('\nAll ' + passed + ' checks passed.');
