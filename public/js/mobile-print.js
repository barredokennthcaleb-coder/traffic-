/**
 * Mobile print helper.
 *
 * Phones gate web-initiated printing:
 *  - iOS Safari ignores window.print() calls that are not made from a tap and
 *    shows "This website has been blocked from automatically printing" when a
 *    page tries to print on its own;
 *  - mobile Chrome also blocks scripted dialogs until the reader taps.
 *
 * So every touch device — including iPads in desktop mode, which used to slip
 * past user-agent checks — gets a "Tap to Print" sheet instead of an automatic
 * dialog.
 *
 * After the print tap, NOTHING navigates on its own. iOS reports the print
 * sheet's lifecycle unreliably (it can announce "afterprint" while the officer
 * is still adjusting the paper size), and navigating during that window
 * cancelled the print and dropped the reader back on the list. Instead the
 * sheet becomes two explicit buttons — "Done" (returns to the list when the
 * view provided a backUrl) and "Print again" — so the officer leaves when
 * they choose to.
 *
 * Desktop keeps the original auto-print and afterprint return.
 */
(function () {
    'use strict';

    /**
     * Any finger-driven screen must go through the tap sheet. This is wider
     * than a user-agent test on purpose: iPadOS/iPhone in "desktop mode"
     * reports itself as a Macintosh, so the capability checks (touch points,
     * coarse pointer) are what reliably catch it.
     */
    function detectTouch() {
        try {
            if (navigator.maxTouchPoints > 0) return true;
            if ('ontouchend' in document.documentElement) return true;
            if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) return true;
        } catch (e) { /* fall through to the user-agent checks */ }

        return /Android|iPhone|iPad|iPod|Mobile|Tablet/i.test(navigator.userAgent || '') ||
            (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    var touch = detectTouch();

    /** Views may listen for afterprint; keep that contract working. */
    function fireAfterPrint() {
        try {
            window.dispatchEvent(new Event('afterprint'));
        } catch (e) {
            if (typeof window.onafterprint === 'function') window.onafterprint();
        }
    }

    /**
     * Touch printing builds the print document at exactly the 58mm roll
     * width, at true letter size. Printer apps get handed that document and
     * crop or scale whatever width it has; when it was A4-wide, choosing a
     * smaller size in the print sheet cropped the ticket and its letters
     * went missing. With the document at the roll width, **every** size
     * choice scales the whole ticket uniformly — all letters stay visible.
     *
     * Only ticket pages get this (the same card the desktop A4-squeeze flow
     * draws); receipts and reports keep their current layout, and desktop is
     * untouched because it never runs this helper's touch path. The rules
     * live inside @media print, so the on-screen view looks exactly as
     * before.
     */
    function useRollPrintPage() {
        if (document.getElementById('trafficRollPrintPage')) return;

        var style = document.createElement('style');
        style.id = 'trafficRollPrintPage';
        style.textContent =
            '@page { size: 58mm 297mm; margin: 0; }\n' +
            '@media print {\n' +
            '  html, body { width: 58mm !important; margin: 0 auto !important; background: #fff !important; }\n' +
            '  .tct-official-card {\n' +
            '    width: 58mm !important;\n' +
            '    max-width: 58mm !important;\n' +
            '    zoom: 1 !important;\n' +
            '    transform: none !important;\n' +
            '    margin: 0 auto !important;\n' +
            '  }\n' +
            '}';
        // Appended to the body, after the view's own <style> blocks, so
        // these rules come last in the document and win the cascade.
        document.body.appendChild(style);
    }

    if (touch) {
        var armRollPage = function () {
            if (document.querySelector('.tct-official-card')) {
                useRollPrintPage();
            }
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', armRollPage);
        } else {
            armRollPage();
        }
    }

    function showPrompt(backUrl) {
        if (document.getElementById('trafficPrintPrompt')) {
            return;
        }

        var style = document.createElement('style');
        style.textContent = '@media print { #trafficPrintPrompt { display: none !important; } }';
        document.head.appendChild(style);

        var wrap = document.createElement('div');
        wrap.id = 'trafficPrintPrompt';
        wrap.style.cssText = 'position:fixed;left:50%;transform:translateX(-50%);bottom:18px;' +
            'z-index:2147483000;display:flex;flex-direction:column;align-items:center;gap:7px;max-width:calc(100vw - 24px);';

        var printBtn = document.createElement('button');
        printBtn.type = 'button';
        printBtn.textContent = '🖨️ Tap to Print';
        printBtn.style.cssText = 'border:none;border-radius:999px;background:#0d6efd;color:#fff;' +
            'font-size:17px;font-weight:700;padding:14px 28px;cursor:pointer;' +
            'box-shadow:0 10px 28px rgba(13,110,253,.45);';

        var hint = document.createElement('div');
        hint.textContent = 'If the browser asks, tap “Allow”.';
        hint.style.cssText = 'font-size:12px;padding:4px 12px;border-radius:999px;' +
            'background:rgba(15,23,42,.78);color:#e2e8f0;text-align:center;';

        wrap.appendChild(printBtn);
        wrap.appendChild(hint);
        document.body.appendChild(wrap);

        function closePrompt() {
            if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
            if (style.parentNode) style.parentNode.removeChild(style);
        }

        /**
         * The state after a print tap. The page just sits here: the officer
         * can take as long as needed in the print sheet (paper size, printer,
         * copies) without the page moving underneath them.
         */
        function showDoneState() {
            wrap.innerHTML = '';

            var doneBtn = document.createElement('button');
            doneBtn.type = 'button';
            doneBtn.textContent = backUrl ? '✓ Done — Back to Violations' : '✓ Done';
            doneBtn.style.cssText = 'border:none;border-radius:999px;background:#047857;color:#fff;' +
                'font-size:16px;font-weight:700;padding:13px 26px;cursor:pointer;' +
                'box-shadow:0 10px 28px rgba(4,120,87,.45);max-width:100%;';
            doneBtn.addEventListener('click', function () {
                closePrompt();
                fireAfterPrint();
                if (backUrl) {
                    window.location.href = backUrl;
                }
            });

            var againBtn = document.createElement('button');
            againBtn.type = 'button';
            againBtn.textContent = '🖨️ Print again';
            againBtn.style.cssText = 'border:none;border-radius:999px;background:#0d6efd;color:#fff;' +
                'font-size:14px;font-weight:700;padding:10px 22px;cursor:pointer;' +
                'box-shadow:0 8px 20px rgba(13,110,253,.35);';
            againBtn.addEventListener('click', function () {
                // Another print must also come from a tap.
                window.print();
            });

            var doneHint = document.createElement('div');
            doneHint.textContent = 'Keep this page open while printing.';
            doneHint.style.cssText = 'font-size:12px;padding:4px 12px;border-radius:999px;' +
                'background:rgba(15,23,42,.78);color:#e2e8f0;text-align:center;';

            wrap.appendChild(doneBtn);
            wrap.appendChild(againBtn);
            wrap.appendChild(doneHint);
        }

        var printing = false;
        printBtn.addEventListener('click', function () {
            if (printing) return;
            printing = true;
            printBtn.disabled = true;

            // The one window.print() iOS allows: straight from the tap.
            window.print();

            // Whether the sheet is still open (Chrome-style async dialog) or
            // already closed (Safari blocks until dismissal), the officer now
            // leaves this state only by tapping a button themselves.
            showDoneState();
        });
    }

    window.TrafficPrint = {
        needsTap: touch,
        afterPrint: fireAfterPrint,

        /**
         * Print when the platform allows it; on touch devices show a button
         * the user can tap instead (the tap is what unlocks the print dialog
         * there, and it avoids the "blocked from automatic printing" gate).
         *
         * Options:
         *  - backUrl: page the "Done" button returns to (touch devices only;
         *             desktop keeps its own afterprint return below).
         */
        autoOrPrompt: function (options) {
            options = options || {};
            var backUrl = options.backUrl || '';

            if (!touch) {
                if (backUrl) {
                    window.addEventListener('afterprint', function () {
                        window.location.href = backUrl;
                    }, { once: true });
                }
                window.print();
                return;
            }

            showPrompt(backUrl);
        }
    };
})();
