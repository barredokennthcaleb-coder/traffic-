/**
 * Direct Bluetooth printing for the field phones.
 *
 * The regular "Print Ticket" buttons go through the browser/OS print dialog,
 * which on a phone needs a separate print-service app and cannot talk to the
 * cheap 58mm Bluetooth printers directly. This helper skips that whole stack:
 * it renders the on-screen ticket (the exact card the officer sees) into a
 * 1-bit bitmap, wraps it in ESC/POS raster commands and writes the bytes to
 * the printer over Bluetooth LE with the Web Bluetooth API.
 *
 * Works on browsers that expose navigator.bluetooth over a secure context
 * (Chrome/Edge on Android, Bluefy on iOS, desktop Chrome for testing). When
 * the browser or the printer cannot be reached that way the caller falls back
 * to the RawBT app (Android) or to the normal print dialog.
 *
 * The raster route (instead of sending text commands) keeps the printed
 * ticket pixel-identical to the layout in the views: the seal, the ticked
 * violation boxes, the underlines and the signature images all render as
 * drawn. The capture reuses each view's own @media print rules - flattened
 * on the fly - so the bitmap is 1:1 with the "58mm roll" paper layout.
 */
(function (window, document) {
    'use strict';

    // ── ESC/POS knowledge base ────────────────────────────────────────────

    // Dots across a 58mm roll (203dpi printers use 384; 80mm units use 576).
    var DEFAULT_WIDTH = 384;

    // GATT services that grant access when connecting. Chrome only lets the
    // page touch services listed here (or found through the chooser filters),
    // so the usual ESC/POS / serial-profile UUIDs are all listed.
    var PRINTER_SERVICES = [
        '000018f0-0000-1000-8000-00805f9b34fb',
        '0000ff00-0000-1000-8000-00805f9b34fb',
        '0000ff10-0000-1000-8000-00805f9b34fb',
        '0000ff20-0000-1000-8000-00805f9b34fb',
        '0000ff90-0000-1000-8000-00805f9b34fb',
        '0000ffe0-0000-1000-8000-00805f9b34fb',
        '0000ffe5-0000-1000-8000-00805f9b34fb',
        '0000fff0-0000-1000-8000-00805f9b34fb',
        '49535343-fe7d-4ae5-8fa9-9fafd205e455',
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
    ];

    // Characteristics that are known to accept data, most common first. The
    // code falls back to "any writable characteristic" when none match.
    var WRITE_CHARACTERISTICS = [
        '0000ff02-0000-1000-8000-00805f9b34fb',
        '0000ff01-0000-1000-8000-00805f9b34fb',
        '0000ffe1-0000-1000-8000-00805f9b34fb',
        '0000fff1-0000-1000-8000-00805f9b34fb',
        '0000fff2-0000-1000-8000-00805f9b34fb',
        '49535343-8841-43f4-a8d4-ecbe34729bb3',
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
        '00002af1-0000-1000-8000-00805f9b34fb'
    ];

    // Name hints improve the filtered chooser list; brands that do not
    // advertise their service UUID still show up when their name matches.
    var NAME_PREFIXES = [
        'XP-', 'Xprinter', 'GOOJPRT', 'RPP', 'MPT-', 'MTP-', 'PRT', 'POS-',
        'Printer', 'Print', 'Thermal', 'PT-', 'HM-', 'BlueTooth', 'BT-'
    ];

    var STORAGE_KEY = 'traffic.btPrinter';

    // One live GATT session is kept for the whole page so a second ticket
    // prints without reconnecting (and without another chooser).
    var session = { device: null, server: null, characteristic: null };

    // ── Lazy html2canvas ──────────────────────────────────────────────────
    // The renderer is ~200 KB, so it is only fetched the first time a
    // Bluetooth print actually runs. Its location is derived from this
    // script's own URL (public/js/ → public/vendor/…), so the helper keeps
    // working from any base_url the app is installed under.
    var scriptBase = '';
    (function () {
        var current = document.currentScript;
        if (!current) {
            var scripts = document.getElementsByTagName('script');
            current = scripts[scripts.length - 1];
        }
        if (current && current.src) scriptBase = current.src.replace(/[^/]*$/, '');
    })();

    var rendererPromise = null;

    function loadRenderer() {
        if (typeof window.html2canvas === 'function') return Promise.resolve();
        if (rendererPromise) return rendererPromise;

        rendererPromise = new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = scriptBase + '../vendor/html2canvas/html2canvas.min.js';
            script.onload = function () {
                if (typeof window.html2canvas === 'function') resolve();
                else reject(new Error('html2canvas-missing'));
            };
            script.onerror = function () {
                rendererPromise = null;
                reject(new Error('html2canvas-missing'));
            };
            document.head.appendChild(script);
        });
        return rendererPromise;
    }

    // ── Small utilities ───────────────────────────────────────────────────

    function sleep(ms) {
        return new Promise(function (resolve) { window.setTimeout(resolve, ms); });
    }

    function nameOf(device) {
        return (device && device.name) || 'Bluetooth printer';
    }

    function supported() {
        return typeof navigator !== 'undefined' && !!navigator.bluetooth;
    }

    function secureContext() {
        return window.isSecureContext !== false;
    }

    function rawbtSupported() {
        return /Android/i.test(navigator.userAgent || '');
    }

    function savedPrinter() {
        try {
            var raw = window.localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function savePrinter(device) {
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify({
                id: device.id || '',
                name: nameOf(device)
            }));
        } catch (e) { /* private mode — printing still works per session */ }
    }

    function forgetPrinter() {
        try { window.localStorage.removeItem(STORAGE_KEY); } catch (e) { /* ignore */ }
        if (session.device && session.device.gatt && session.device.gatt.connected) {
            try { session.device.gatt.disconnect(); } catch (e) { /* ignore */ }
        }
        session = { device: null, server: null, characteristic: null };
    }

    // ── Print-CSS flattening ──────────────────────────────────────────────
    // The views keep their paper layout inside `@media print` blocks. The
    // capture needs those rules to apply on screen for a moment, so they are
    // copied out of the live stylesheets without their media wrapper. @page
    // rules are skipped: html2canvas draws a bitmap, not a sheet of paper.

    function collectPrintCss() {
        var out = [];
        var sheets = document.styleSheets || [];
        for (var i = 0; i < sheets.length; i++) {
            var rules;
            try {
                rules = sheets[i].cssRules;
            } catch (e) {
                continue; // stylesheet from another origin
            }
            if (!rules) continue;
            for (var j = 0; j < rules.length; j++) {
                var rule = rules[j];
                if (!rule.media || !rule.cssText) continue;
                if (!/\bprint\b/i.test(rule.media.mediaText || '')) continue;
                var inner = rule.cssRules || [];
                for (var k = 0; k < inner.length; k++) {
                    var text = (inner[k].cssText || '').trim();
                    if (!text || /^@page/i.test(text)) continue;
                    out.push(text);
                }
            }
        }
        return out.join('\n');
    }

    // 58mm expressed in CSS pixels (96dpi). The view's paper layout is drawn
    // at exactly this width; the raster capture maps its content onto the
    // 384 dots the printer head can actually reach.
    var NOMINAL_CARD_WIDTH = 219.24;

    function captureOverrideCss() {
        // Appended after the flattened print rules, so these win. The card
        // keeps its true-size width (58mm); the scale-up to printer dots
        // happens through html2canvas' scale option, not through CSS.
        return [
            '.tct-official-card {',
            '  width: ' + NOMINAL_CARD_WIDTH + 'px !important;',
            '  max-width: ' + NOMINAL_CARD_WIDTH + 'px !important;',
            '  zoom: 1 !important;',
            '  transform: none !important;',
            '  margin-left: auto !important;',
            '  margin-right: auto !important;',
            '  box-shadow: none !important;',
            '}',
            'html, body { background: #fff !important; }'
        ].join('\n');
    }

    function guessMode(el) {
        return (el && (el.id === 'tctCard' || (el.classList && el.classList.contains('tct-official-card'))))
            ? 'print58'
            : 'shrink';
    }

    function setImportant(el, prop, value) {
        var previous = {
            value: el.style.getPropertyValue(prop),
            priority: el.style.getPropertyPriority(prop)
        };
        el.style.setProperty(prop, value, 'important');
        return previous;
    }

    function restoreImportant(el, prop, previous) {
        if (previous && previous.value) {
            el.style.setProperty(prop, previous.value, previous.priority);
        } else {
            el.style.removeProperty(prop);
        }
    }

    // ── Capture: the DOM card → canvas bitmap ─────────────────────────────

    function onceCaptured(el, mode, width, extraOptions) {
        return loadRenderer().then(function () {
            return onceCapturedLoaded(el, mode, width, extraOptions);
        });
    }

    function onceCapturedLoaded(el, mode, width, extraOptions) {
        var injected = null;
        var saved = [];

        function pin(prop, value) {
            saved.push([prop, setImportant(el, prop, value)]);
        }

        if (mode === 'print58') {
            // The flattened @media print rules draw the ticket the way the
            // "58mm roll" paper mode does: a nominal 58mm column with ~20px
            // side padding, fonts sized for that width. The raster's job is
            // to put the *content* (not the padding) across the 384 dots the
            // print head can reach, so the capture runs at
            //     scale = rasterWidth / contentWidth
            // and the side padding is cropped off afterwards. That keeps the
            // glyphs at their true-size height (~2.2mm, exactly what the A4
            // squeeze and the 58mm roll modes produce on paper) instead of
            // shrinking 58mm of CSS into 48mm of printable paper.
            injected = document.createElement('style');
            injected.id = 'trafficBtCaptureStyle';
            injected.textContent = collectPrintCss() + '\n' + captureOverrideCss();
            document.head.appendChild(injected);

            pin('width', NOMINAL_CARD_WIDTH + 'px');
            pin('max-width', NOMINAL_CARD_WIDTH + 'px');
            pin('zoom', '1');
            pin('transform', 'none');
            pin('margin', '0 auto');
        } else {
            // Receipts and other sheets are drawn for a full page. Reflow
            // them onto the roll width (their columns are percentage based)
            // so the text stays at a readable size on thermal paper.
            pin('width', width + 'px');
            pin('max-width', width + 'px');
            pin('zoom', '1');
            pin('transform', 'none');
        }

        function restore() {
            if (injected) injected.remove();
            saved.forEach(function (entry) { restoreImportant(el, entry[0], entry[1]); });
        }

        // Measured while the capture styles are active, then reused after
        // the capture so the crop math lines up with what was rendered.
        var crop = { left: 0, width: width };

        // Two frames so the injected rules are laid out before capture.
        return new Promise(function (resolve) {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(resolve);
            });
        }).then(function () {
            var rect = el.getBoundingClientRect();
            var scale = 1;

            if (mode === 'print58') {
                var computed = window.getComputedStyle(el);
                var padLeft = parseFloat(computed.paddingLeft) || 0;
                var padRight = parseFloat(computed.paddingRight) || 0;
                var contentWidth = Math.max(1, rect.width - padLeft - padRight);
                scale = width / contentWidth;
                crop = { left: Math.round(padLeft * scale), width: width };
            }

            // A ticket is much taller than the phone viewport. html2canvas
            // renders the clone in an iframe sized by windowWidth/Height, so
            // both are stretched to the full document — otherwise the bottom
            // half of the citation falls outside the capture and comes out
            // blank.
            var docEl = document.documentElement;
            var fullWidth = Math.max(docEl.scrollWidth, window.innerWidth || 0, Math.ceil(rect.width));
            var fullHeight = Math.max(docEl.scrollHeight, Math.ceil(rect.height + Math.max(rect.top, 0)));

            return window.html2canvas(el, Object.assign({
                scale: scale,
                backgroundColor: '#ffffff',
                useCORS: true,
                logging: false,
                windowWidth: fullWidth,
                windowHeight: fullHeight
            }, extraOptions || {}));
        }).then(function (canvas) {
            if (crop.left > 0) {
                canvas = cropHorizontal(canvas, crop.left, crop.width);
            }
            restore();
            return canvas;
        }, function (error) {
            restore();
            throw error;
        });
    }

    /** Cut `width` dots out of the captured canvas starting at `cropLeft`. */
    function cropHorizontal(canvas, cropLeft, width) {
        var out = document.createElement('canvas');
        out.width = width;
        out.height = canvas.height;
        var ctx = out.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, canvas.height);
        ctx.drawImage(
            canvas,
            Math.max(0, Math.min(cropLeft, canvas.width - width)),
            0, width, canvas.height,
            0, 0, width, canvas.height
        );
        return out;
    }

    function capture(el, mode, width) {
        var attempts = [
            {},                                                          // full-document viewport
            { windowWidth: Math.max(width, 480), windowHeight: 8192 }    // safety net
        ];
        var lastError = null;

        function tryNext(index) {
            if (index >= attempts.length) {
                return Promise.reject(lastError || new Error('capture-failed'));
            }
            return onceCaptured(el, mode, width, attempts[index]).then(function (canvas) {
                if (canvas && canvas.width > 80 && canvas.height > 80) return canvas;
                return tryNext(index + 1);
            }, function (error) {
                lastError = error;
                return tryNext(index + 1);
            });
        }

        return tryNext(0);
    }

    function downscale(canvas, width) {
        if (canvas.width <= width) return canvas;
        var ratio = width / canvas.width;
        var out = document.createElement('canvas');
        out.width = width;
        out.height = Math.max(1, Math.round(canvas.height * ratio));
        var ctx = out.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, out.width, out.height);
        ctx.drawImage(canvas, 0, 0, out.width, out.height);
        return out;
    }

    // ── Canvas → 1-bit raster → ESC/POS bytes ─────────────────────────────

    function canvasToRaster(canvas, threshold) {
        threshold = threshold || 168;
        var ctx = canvas.getContext('2d');
        var w = canvas.width;
        var h = canvas.height;
        var pixels = ctx.getImageData(0, 0, w, h).data;
        var bytesPerRow = Math.ceil(w / 8);
        var packed = new Uint8Array(bytesPerRow * h);

        for (var y = 0; y < h; y++) {
            var rowOffset = y * bytesPerRow;
            var p = y * w * 4;
            for (var x = 0; x < w; x++, p += 4) {
                if (pixels[p + 3] < 40) continue; // transparent = paper
                var luminance = 0.299 * pixels[p] + 0.587 * pixels[p + 1] + 0.114 * pixels[p + 2];
                if (luminance < threshold) {
                    // GS v 0 packs the leftmost dot into the MSB of each byte.
                    packed[rowOffset + (x >> 3)] |= (0x80 >> (x & 7));
                }
            }
        }

        return { width: w, height: h, bytesPerRow: bytesPerRow, packed: packed };
    }

    /** Trim the blank paper at the top and bottom so the feed stays short. */
    function cropRaster(raster, pad) {
        pad = pad == null ? 6 : pad;
        var bpr = raster.bytesPerRow;
        var packed = raster.packed;
        var top = -1;
        var bottom = -1;

        for (var y = 0; y < raster.height; y++) {
            var hasInk = false;
            var offset = y * bpr;
            for (var b = 0; b < bpr; b++) {
                if (packed[offset + b]) { hasInk = true; break; }
            }
            if (hasInk) {
                if (top < 0) top = y;
                bottom = y;
            }
        }

        if (top < 0) return raster; // blank page — keep it so the printer still feeds

        top = Math.max(0, top - pad);
        bottom = Math.min(raster.height - 1, bottom + pad);
        return {
            width: raster.width,
            height: bottom - top + 1,
            bytesPerRow: bpr,
            packed: packed.subarray(top * bpr, (bottom + 1) * bpr)
        };
    }

    function escposCommands(raster, options) {
        options = options || {};
        var height = Math.min(raster.height, 0xFFFF);
        var dataLength = raster.bytesPerRow * height;

        var header = [
            0x1B, 0x40,                                            // ESC @ initialise
            0x1D, 0x76, 0x30, 0x00,                                // GS v 0 (raster bitmap)
            raster.bytesPerRow & 0xFF, (raster.bytesPerRow >> 8) & 0xFF,
            height & 0xFF, (height >> 8) & 0xFF
        ];

        var feedLines = options.feedLines == null ? 4 : options.feedLines;
        var total = header.length + dataLength + feedLines + (options.cut ? 4 : 0);
        var out = new Uint8Array(total);
        var offset = 0;

        out.set(header, offset); offset += header.length;
        out.set(raster.packed.subarray(0, dataLength), offset);
        offset += dataLength;

        for (var i = 0; i < feedLines; i++) out[offset++] = 0x0A;

        // Portable printers rarely have a cutter; only send it when asked.
        if (options.cut) {
            out[offset++] = 0x1D; out[offset++] = 0x56; out[offset++] = 0x42; out[offset++] = 0x00;
        }

        return out;
    }

    function bytesToBase64(bytes) {
        var binary = '';
        var chunk = 0x8000;
        for (var i = 0; i < bytes.length; i += chunk) {
            binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
        }
        return window.btoa(binary);
    }

    // ── Bluetooth session handling ────────────────────────────────────────

    function buildFilters() {
        var filters = PRINTER_SERVICES.map(function (uuid) { return { services: [uuid] }; });
        NAME_PREFIXES.forEach(function (prefix) { filters.push({ namePrefix: prefix }); });
        return filters;
    }

    function requestDevice(acceptAll) {
        var options = acceptAll
            ? { acceptAllDevices: true, optionalServices: PRINTER_SERVICES }
            : { filters: buildFilters(), optionalServices: PRINTER_SERVICES };
        return navigator.bluetooth.requestDevice(options);
    }

    /**
     * The filtered chooser can come up empty for unbranded printers — and it
     * never lists Classic Bluetooth (SPP-only) models at all. Ask what to do
     * instead of silently retrying or giving up.
     * Resolves: 'all' (re-open with every device), 'rawbt' (Android app), null.
     */
    function askShowAllDevices() {
        var question = 'Printer not in the list? This phone can show every nearby Bluetooth device instead.';
        var canRawbt = rawbtSupported();

        if (window.Swal) {
            return window.Swal.fire({
                icon: 'question',
                title: 'Printer not in the list?',
                text: question,
                showConfirmButton: true,
                confirmButtonText: 'Show all devices',
                showDenyButton: canRawbt,
                denyButtonText: 'Try the RawBT app',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0066ff',
                denyButtonColor: '#047857'
            }).then(function (result) {
                if (result.isConfirmed) return 'all';
                if (result.isDenied) return 'rawbt';
                return null;
            });
        }

        if (window.confirm(question)) return Promise.resolve('all');
        if (canRawbt && window.confirm('Try printing through the RawBT app instead? (older SPP-only printers need it)')) {
            return Promise.resolve('rawbt');
        }
        return Promise.resolve(null);
    }

    function pickSaved(granted) {
        var saved = savedPrinter();
        if (!saved) return granted.length === 1 ? granted[0] : null;
        for (var i = 0; i < granted.length; i++) {
            if (saved.id && granted[i].id === saved.id) return granted[i];
        }
        for (var j = 0; j < granted.length; j++) {
            if (saved.name && granted[j].name === saved.name) return granted[j];
        }
        return null;
    }

    function findWritableCharacteristic(server) {
        return server.getPrimaryServices().then(function (services) {
            var fallback = null;

            function scanService(index) {
                if (index >= services.length) return fallback;
                return services[index].getCharacteristics().then(function (characteristics) {
                    for (var i = 0; i < characteristics.length; i++) {
                        var characteristic = characteristics[i];
                        var props = characteristic.properties || {};
                        if (!props.write && !props.writeWithoutResponse) continue;
                        if (WRITE_CHARACTERISTICS.indexOf((characteristic.uuid || '').toLowerCase()) !== -1) {
                            return characteristic;
                        }
                        if (!fallback) fallback = characteristic;
                    }
                    return scanService(index + 1);
                }, function () {
                    return scanService(index + 1);
                });
            }

            return scanService(0).then(function (characteristic) {
                if (!characteristic) throw new Error('no-write-characteristic');
                return characteristic;
            });
        });
    }

    function connect(device) {
        return Promise.resolve(device.gatt.connect()).then(function (server) {
            session.device = device;
            session.server = server;
            device.addEventListener('gattserverdisconnected', function () {
                session = { device: null, server: null, characteristic: null };
            });
            return findWritableCharacteristic(server);
        }).then(function (characteristic) {
            session.characteristic = characteristic;
            return characteristic;
        });
    }

    function ensurePrinter(onStatus) {
        function status(text) { if (typeof onStatus === 'function') onStatus(text); }

        // 1) Live session from earlier on this page.
        if (session.device && session.device.gatt && session.device.gatt.connected && session.characteristic) {
            return Promise.resolve(nameOf(session.device));
        }

        // 2) A printer granted earlier — reconnect without another chooser.
        status('Looking for the saved printer…');
        var lookup = (navigator.bluetooth.getDevices)
            ? navigator.bluetooth.getDevices().catch(function () { return []; })
            : Promise.resolve([]);

        return lookup.then(function (granted) {
            var device = pickSaved(granted || []);
            if (!device) return null;
            status('Connecting to ' + nameOf(device) + '…');
            return connect(device).then(function () {
                return nameOf(device);
            }, function () {
                return null; // moved out of range or powered off — ask again below
            });
        }).then(function (name) {
            if (name) return name;

            // 3) Ask the officer to pick a printer in the browser's own list.
            status('Choose your printer…');
            return requestDevice(false).catch(function (error) {
                if (!(error && error.name === 'NotFoundError')) throw error;
                return askShowAllDevices().then(function (choice) {
                    if (choice === 'all') {
                        status('Showing all nearby devices…');
                        return requestDevice(true);
                    }
                    if (choice === 'rawbt') throw new Error('use-rawbt');
                    throw error;
                });
            }).then(function (device) {
                status('Connecting to ' + nameOf(device) + '…');
                return connect(device).then(function () {
                    savePrinter(device);
                    return nameOf(device);
                });
            });
        });
    }

    function writeAll(characteristic, bytes, onProgress) {
        var props = characteristic.properties || {};
        var canSkipResponse = !!props.writeWithoutResponse &&
            typeof characteristic.writeValueWithoutResponse === 'function';
        var canAcknowledge = !!props.write &&
            typeof characteristic.writeValue === 'function';

        if (!canSkipResponse && !canAcknowledge) {
            return Promise.reject(new Error('no-write-characteristic'));
        }

        // Chunks stay small on purpose: portable printers have tiny buffers,
        // and an oversized write shows up as a garbled line on paper.
        var chunkSize = canSkipResponse ? 256 : 180;
        var pauseMs = canSkipResponse ? 10 : 16;

        function writeChunk(offset) {
            if (offset >= bytes.length) {
                // Let the printer drain its buffer before the connection idles.
                return sleep(300);
            }
            var slice = bytes.slice(offset, offset + chunkSize);
            var write = canSkipResponse
                ? characteristic.writeValueWithoutResponse(slice)
                : characteristic.writeValue(slice);

            return Promise.resolve(write).then(function () {
                if (typeof onProgress === 'function') {
                    onProgress(Math.min(offset + slice.length, bytes.length), bytes.length);
                }
                return sleep(pauseMs).then(function () {
                    return writeChunk(offset + slice.length);
                });
            });
        }

        // A short settle pause after connecting: a few firmwares drop the
        // first bytes while their Bluetooth stack is still coming up.
        return sleep(150).then(function () { return writeChunk(0); });
    }

    // ── Status overlay / dialogs ──────────────────────────────────────────

    function findTarget(el) {
        if (!el) return null;
        if (typeof el === 'string') return document.querySelector(el);
        return el;
    }

    function showOverlay(initialText) {
        var wrap = document.createElement('div');
        wrap.id = 'trafficBtOverlay';
        wrap.style.cssText = 'position:fixed;inset:0;z-index:2147483646;background:rgba(15,23,42,.86);' +
            'display:flex;align-items:center;justify-content:center;padding:24px;';

        var box = document.createElement('div');
        box.style.cssText = 'background:#fff;color:#0f172a;border-radius:18px;padding:26px 30px;max-width:340px;' +
            'width:100%;text-align:center;font-family:Inter,-apple-system,Segoe UI,sans-serif;box-shadow:0 24px 60px rgba(0,0,0,.4);';

        var spinner = document.createElement('div');
        spinner.style.cssText = 'width:46px;height:46px;margin:0 auto 14px;border-radius:50%;border:4px solid #dbeafe;' +
            'border-top-color:#0066ff;animation:trafficBtSpin .8s linear infinite;';

        var statusEl = document.createElement('div');
        statusEl.style.cssText = 'font-size:17px;font-weight:700;';
        statusEl.textContent = initialText || 'Preparing…';

        var hint = document.createElement('div');
        hint.style.cssText = 'font-size:12.5px;color:#64748b;margin-top:6px;';
        hint.textContent = 'Keep the printer switched on and nearby.';

        box.appendChild(spinner);
        box.appendChild(statusEl);
        box.appendChild(hint);
        wrap.appendChild(box);

        var spinStyle = document.createElement('style');
        spinStyle.textContent = '@keyframes trafficBtSpin{to{transform:rotate(360deg)}}';
        wrap.appendChild(spinStyle);

        document.body.appendChild(wrap);

        return {
            status: function (text) { statusEl.textContent = text; },
            done: function (text) {
                statusEl.textContent = text;
                spinner.style.animation = 'none';
                spinner.style.borderColor = '#10b981';
                spinner.style.borderTopColor = '#10b981';
                spinner.style.display = 'flex';
                spinner.style.alignItems = 'center';
                spinner.style.justifyContent = 'center';
                spinner.style.fontSize = '22px';
                spinner.style.lineHeight = '1';
                spinner.style.color = '#10b981';
                spinner.textContent = '✓';
                hint.textContent = '';
            },
            remove: function () { if (wrap.parentNode) wrap.parentNode.removeChild(wrap); }
        };
    }

    function notify(title, icon) {
        if (window.Swal) {
            window.Swal.fire({ toast: true, position: 'top-end', icon: icon || 'info', title: title, showConfirmButton: false, timer: 3600, timerProgressBar: true });
        }
    }

    function explainError(error) {
        var name = (error && error.name) || '';
        var message = (error && error.message) || '';

        if (name === 'NotFoundError' || /user cancelled|chooser/i.test(message)) {
            return {
                cancelled: true,
                text: 'The printer chooser was closed.'
            };
        }
        if (message === 'html2canvas-missing') {
            return { text: 'The ticket renderer did not load. Refresh the page and try again.' };
        }
        if (name === 'SecurityError' || /permission|gesture/i.test(message)) {
            return { text: 'The phone blocked the connection. Tap the Bluetooth Print button again to confirm the printer.' };
        }
        if (name === 'NetworkError' || /GATT|connect/i.test(message)) {
            return { text: 'Could not reach the printer. Wake it up, keep it close, and make sure it is not connected to another phone or app.' };
        }
        if (message === 'no-write-characteristic') {
            return { text: 'The printer connected but does not accept data this way. It may be a Classic Bluetooth (SPP) model — on Android use the RawBT app instead.' };
        }
        if (message === 'use-rawbt') {
            return { useRawbt: true, text: 'Sending the ticket through the RawBT app.' };
        }
        return { text: 'Bluetooth printing failed. ' + (message || 'Unknown error.') };
    }

    function fallbackDialog(error, el) {
        var info = explainError(error);
        if (info.cancelled) return Promise.resolve();

        // The officer picked "Try the RawBT app" in the chooser-recovery
        // dialog: hand the bytes over with no further ceremony.
        if (info.useRawbt && rawbtSupported()) {
            notify(info.text, 'info');
            rawbtPrint(el);
            return Promise.resolve();
        }

        var canRawbt = rawbtSupported();
        var html = '<div style="font-size:14px;line-height:1.5;">' + info.text +
            '<br><br><span style="color:#64748b;font-size:12px;">The normal Print button still works through the phone’s print service.</span></div>';

        function useRawbt() { rawbtPrint(el); }
        function useDialog() { window.print(); }

        if (window.Swal) {
            return window.Swal.fire({
                icon: 'error',
                title: 'Bluetooth printing failed',
                html: html,
                showConfirmButton: true,
                confirmButtonText: 'Use print dialog',
                showCancelButton: true,
                cancelButtonText: 'Close',
                showDenyButton: canRawbt,
                denyButtonText: 'Print with RawBT',
                confirmButtonColor: '#0066ff',
                denyButtonColor: '#047857'
            }).then(function (result) {
                if (result.isConfirmed) useDialog();
                if (result.isDenied) useRawbt();
            });
        }

        if (canRawbt && window.confirm(info.text + '\n\nOK = try the RawBT app, Cancel = use the print dialog.')) {
            useRawbt();
        } else {
            useDialog();
        }
        return Promise.resolve();
    }

    function showHelp(reason) {
        var html = '';

        if (reason === 'insecure') {
            html = '<div style="text-align:left;font-size:13.5px;line-height:1.6;">' +
                '<b>Direct Bluetooth printing needs an https:// address.</b><br>' +
                'Phones only allow pages to reach Bluetooth over a secure connection. Ask whoever runs the server to expose this site over HTTPS ' +
                '(for example a Cloudflare Tunnel / ngrok address), then this button prints without any app.</div>';
        } else if (reason === 'unsupported') {
            html = '<div style="text-align:left;font-size:13.5px;line-height:1.6;">' +
                '<b>This browser cannot talk to Bluetooth printers directly.</b><br>' +
                '• On Android: use <b>Chrome</b>, or install the free <b>RawBT</b> print-service app and use it from the printer chooser.<br>' +
                '• On iPhone: Safari does not support direct Bluetooth printing — use the normal Print button (AirPrint / printer app), ' +
                'or install the free <b>Bluefy</b> browser (it supports Web Bluetooth) and open this page there.</div>';
        } else if (reason === 'rawbt') {
            html = '<div style="text-align:left;font-size:13.5px;line-height:1.6;">' +
                '<b>Printing through the RawBT app…</b><br>The ticket was prepared; RawBT should open and print it. ' +
                'If nothing happened, install <b>RawBT</b> from the Play Store and try again.</div>';
        } else {
            html = '<div style="text-align:left;font-size:13.5px;line-height:1.6;">' +
                '<b>Printing straight to your Bluetooth printer</b><br>' +
                '1. Switch the 58mm thermal printer on.<br>' +
                '2. Tap <b>Bluetooth Print</b> and pick the printer in the list.<br>' +
                '3. Allow the connection. Every next ticket prints with one tap — no chooser, no print dialog.<br><br>' +
                'Printer missing from the list? Make sure it is not connected to another phone, then try again — the app will offer “show all devices”.<br><br>' +
                '<i>On iPhone use the Bluefy browser or the normal Print button.</i></div>';
        }

        if (window.Swal) {
            var saved = savedPrinter();
            return window.Swal.fire({
                icon: 'info',
                title: reason === 'unsupported' ? 'Bluetooth printing not available here' : 'Printing with Bluetooth',
                html: html,
                showConfirmButton: true,
                confirmButtonText: 'Got it',
                showCancelButton: !!saved,
                cancelButtonText: 'Forget saved printer',
                confirmButtonColor: '#0066ff'
            }).then(function (result) {
                if (result && result.dismiss === window.Swal.DismissReason.cancel) forgetPrinter();
            });
        }

        window.alert(html.replace(/<[^>]+>/g, ''));
        return Promise.resolve();
    }

    // ── Public printing flows ─────────────────────────────────────────────

    /**
     * Render + send. Rejects with a descriptive error the caller can map to
     * the fallback flow (RawBT / print dialog).
     */
    function printElement(el, options) {
        options = options || {};
        var target = findTarget(el);
        if (!target) return Promise.reject(new Error('no-print-target'));

        var width = options.width || DEFAULT_WIDTH;
        var onStatus = typeof options.onStatus === 'function' ? options.onStatus : function () {};

        onStatus('Rendering the ticket…');
        return capture(target, guessMode(target), width).then(function (canvas) {
            if (canvas.width > width) canvas = downscale(canvas, width);
            var raster = cropRaster(canvasToRaster(canvas, options.threshold), options.pad);
            var bytes = escposCommands(raster, options);

            return ensurePrinter(onStatus).then(function (deviceName) {
                onStatus('Printing to ' + deviceName + '…');
                return writeAll(session.characteristic, bytes, function (sent, total) {
                    onStatus('Printing… ' + Math.round((sent / total) * 100) + '%');
                }).then(function () {
                    return { deviceName: deviceName, bytes: bytes.length, dots: raster.height };
                });
            });
        });
    }

    /**
     * The button flow: overlay, status, success tick, and a helpful fallback
     * dialog when anything goes wrong. `onDone(result)` runs after success.
     */
    function uiPrint(el, options) {
        options = options || {};
        var target = findTarget(el);

        if (!supported()) {
            showHelp(secureContext() ? 'unsupported' : 'insecure');
            return Promise.resolve();
        }
        if (!target) {
            notify('Nothing to print on this page.', 'error');
            return Promise.resolve();
        }

        var overlay = showOverlay('Preparing…');
        var lastShown = '';

        return printElement(target, {
            onStatus: function (text) {
                if (text !== lastShown) { lastShown = text; overlay.status(text); }
            }
        }).then(function (result) {
            overlay.done('Printed to ' + result.deviceName);
            window.setTimeout(function () {
                overlay.remove();
                if (typeof options.onDone === 'function') options.onDone(result);
            }, 800);
        }, function (error) {
            overlay.remove();
            // Callers that have their own recovery (the field ticket flow
            // shows its tap sheet) take over here; everyone else gets the
            // fallback dialog with RawBT / print-dialog choices.
            if (typeof options.onFailed === 'function') {
                options.onFailed(error);
                return;
            }
            return fallbackDialog(error, target);
        });
    }

    /**
     * Android fallback: hand the same ESC/POS bytes to the RawBT print-service
     * app through its documented intent URL. RawBT talks to both BLE and the
     * Classic Bluetooth (SPP) printers that Web Bluetooth can never reach.
     */
    function rawbtPrint(el, options) {
        options = options || {};
        var target = findTarget(el);
        if (!target) return Promise.resolve();
        if (!rawbtSupported()) return showHelp('unsupported');

        return capture(target, guessMode(target), options.width || DEFAULT_WIDTH).then(function (canvas) {
            if (canvas.width > (options.width || DEFAULT_WIDTH)) canvas = downscale(canvas, options.width || DEFAULT_WIDTH);
            var raster = cropRaster(canvasToRaster(canvas, options.threshold), options.pad);
            var bytes = escposCommands(raster, options);
            // When RawBT is not installed Android follows the fallback URL
            // instead of leaving the officer on an error page.
            var fallbackUrl = encodeURIComponent('https://play.google.com/store/apps/details?id=ru.a402d.rawbtprinter');
            var url = 'intent:base64,' + bytesToBase64(bytes) +
                '#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;S.browser_fallback_url=' + fallbackUrl + ';end;';
            window.location.href = url;
        });
    }

    // ── Export ────────────────────────────────────────────────────────────

    window.TrafficBlePrint = {
        supported: supported,
        secureContext: secureContext,
        hasSavedPrinter: function () { return !!savedPrinter(); },
        savedPrinterName: function () { var saved = savedPrinter(); return saved ? saved.name : ''; },
        forgetPrinter: forgetPrinter,
        rawbtSupported: rawbtSupported,
        printElement: printElement,
        rawbtPrint: rawbtPrint,
        uiPrint: uiPrint,
        showHelp: showHelp,
        // Exposed for tests / debugging: preview the exact bitmap that the
        // printer would receive.
        renderTicketCanvas: function (el, options) {
            options = options || {};
            var target = findTarget(el);
            var width = options.width || DEFAULT_WIDTH;
            return capture(target, guessMode(target), width).then(function (canvas) {
                return canvas.width > width ? downscale(canvas, width) : canvas;
            });
        }
    };

    // Pure byte/bit helpers, exported so the encoder can be verified without
    // a printer (tests/js/bluetooth-print.test.js runs them in Node).
    window.TrafficBlePrint.__internals = {
        canvasToRaster: canvasToRaster,
        cropRaster: cropRaster,
        cropHorizontal: cropHorizontal,
        escposCommands: escposCommands,
        bytesToBase64: bytesToBase64
    };
})(window, document);
