/**
 * Mobile print helper.
 *
 * Phones gate web-initiated printing:
 *  - iOS Safari ignores window.print() calls that are not made from a tap;
 *  - the mobile browsers (especially Chrome) also show a "This website has
 *    been blocked from automatically printing" prompt when a page tries to
 *    print on its own.
 * So on touch devices this helper shows a "Tap to Print" button instead of
 * auto-printing (a tap is what unlocks the dialog), plus a small hint for
 * the browser's Allow prompt. Desktop keeps the original auto-print. The
 * prompt never appears on paper.
 */
(function () {
    'use strict';

    function isMobile() {
        return /Android|iPhone|iPad|iPod|Mobile|Tablet/i.test(navigator.userAgent || '') ||
            (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    window.TrafficPrint = {
        needsTap: isMobile(),

        /**
         * Print when the platform allows it; on phones show a button the
         * user can tap instead (the tap is what unlocks the print dialog
         * there, and it avoids the "blocked from automatic printing" gate).
         */
        autoOrPrompt: function () {
            if (!isMobile()) {
                window.print();
                return;
            }
            if (document.getElementById('trafficPrintPrompt')) {
                return;
            }

            var style = document.createElement('style');
            style.textContent = '@media print { #trafficPrintPrompt { display: none !important; } }';
            document.head.appendChild(style);

            var wrap = document.createElement('div');
            wrap.id = 'trafficPrintPrompt';
            wrap.style.cssText = 'position:fixed;left:50%;transform:translateX(-50%);bottom:18px;' +
                'z-index:2147483000;display:flex;flex-direction:column;align-items:center;gap:6px;';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = '🖨️ Tap to Print';
            btn.style.cssText = 'border:none;border-radius:999px;background:#0d6efd;color:#fff;' +
                'font-size:17px;font-weight:700;padding:14px 28px;cursor:pointer;' +
                'box-shadow:0 10px 28px rgba(13,110,253,.45);';
            btn.addEventListener('click', function () {
                window.print();
            });

            var hint = document.createElement('div');
            hint.textContent = 'If the browser asks, tap “Allow”.';
            hint.style.cssText = 'font-size:12px;padding:4px 12px;border-radius:999px;' +
                'background:rgba(15,23,42,.78);color:#e2e8f0;';

            wrap.appendChild(btn);
            wrap.appendChild(hint);
            document.body.appendChild(wrap);
        }
    };
})();
