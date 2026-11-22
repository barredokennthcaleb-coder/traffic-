/* ============================================================================
   Traffic System — Progressive Web App bootstrap
   ----------------------------------------------------------------------------
   • Registers the service worker that makes the app installable/offline-aware.
   • Wires the "Install App" entry points (sidebar, sign-in, lookup).
   • Shows offline and "new version available" notices.
   • Keeps the browser/status-bar colour in step with the chosen theme.

   The script derives every URL from its own <script src>, so the app keeps
   working from a domain root, a XAMPP subdirectory, or a LAN address.
   ========================================================================== */
(function () {
    'use strict';

    const script = document.currentScript || Array.prototype.find.call(
        document.scripts,
        (node) => /\/js\/pwa\.js(?:\?|$)/.test(node.src || ''),
    );

    if (!script) {
        return;
    }

    const scriptUrl = new URL(script.src, window.location.href);
    const swUrl = new URL('../sw.js', scriptUrl).href;
    const swScope = new URL('../', scriptUrl).href;
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    const isPrintMode = new URLSearchParams(window.location.search).get('print') === '1';

    document.documentElement.classList.toggle('pwa-standalone', isStandalone);

    /* ── Theme / status-bar colour ───────────────────────────────────────── */
    const themeMeta = document.getElementById('pwaThemeColor');

    if (themeMeta) {
        const darkColor = themeMeta.getAttribute('data-dark') || '#0b0f19';
        const lightColor = themeMeta.getAttribute('data-light') || '#f8fafc';
        const darkPreference = window.matchMedia('(prefers-color-scheme: dark)');

        const syncThemeColor = () => {
            const root = document.documentElement;
            const explicit = root.getAttribute('data-theme') || root.getAttribute('data-bs-theme');
            const isDark = explicit === 'dark' || (explicit !== 'light' && darkPreference.matches);
            themeMeta.setAttribute('content', isDark ? darkColor : lightColor);
        };

        syncThemeColor();
        window.addEventListener('themechange', syncThemeColor);
        darkPreference.addEventListener?.('change', syncThemeColor);

        new MutationObserver(syncThemeColor).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme', 'data-bs-theme'],
        });
    }

    /* ── Service worker ──────────────────────────────────────────────────── */
    const hadController = !!navigator.serviceWorker?.controller;

    if ('serviceWorker' in navigator && window.isSecureContext) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(swUrl, { scope: swScope })
                .then((registration) => {
                    let lastCheck = Date.now();

                    // Pick up a new deployment when the app is brought back to
                    // the foreground, at most once an hour.
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible' && Date.now() - lastCheck > 3600000) {
                            lastCheck = Date.now();
                            registration.update().catch(() => null);
                        }
                    });
                })
                .catch(() => null);
        });

        navigator.serviceWorker.addEventListener('controllerchange', () => {
            // The first install claims the page too; only a later replacement
            // means the cached app shell is out of date.
            if (!hadController) {
                return;
            }

            showUpdateBanner();
        });
    }

    /* ── Install entry points ────────────────────────────────────────────── */
    let deferredPrompt = null;

    const setInstallVisible = (visible) => {
        document.querySelectorAll('[data-pwa-install]').forEach((button) => {
            const holder = button.closest('.pwa-install-item');
            (holder || button).hidden = !visible;
        });
    };

    // Only a secure context (HTTPS or localhost) can install a PWA.
    if (!isStandalone && window.isSecureContext) {
        setInstallVisible(true);
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        setInstallVisible(true);
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        setInstallVisible(false);
    });

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const button = target ? target.closest('[data-pwa-install]') : null;

        if (!button) {
            return;
        }

        event.preventDefault();

        if (deferredPrompt) {
            const prompt = deferredPrompt;
            deferredPrompt = null;

            prompt.prompt();
            Promise.resolve(prompt.userChoice)
                .catch(() => null)
                .finally(() => setInstallVisible(!isStandalone));
            return;
        }

        showInstallHelp();
    });

    function showInstallHelp() {
        const userAgent = navigator.userAgent;
        const isIos = /iphone|ipad|ipod/i.test(userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        const isAndroid = /android/i.test(userAgent);

        let steps;

        if (isIos) {
            steps = [
                'Tap the Share button in Safari.',
                'Scroll down and choose “Add to Home Screen”.',
                'Tap “Add” to finish.',
            ];
        } else if (isAndroid) {
            steps = [
                'Open the browser menu (⋮).',
                'Choose “Install app” or “Add to Home screen”.',
                'Confirm to add Traffic System.',
            ];
        } else {
            steps = [
                'Open your browser menu.',
                'Choose “Install app”, “Add to Home screen”, or “Add to Dock”.',
                'Confirm to add Traffic System.',
            ];
        }

        const dialog = document.createElement('dialog');
        dialog.className = 'pwa-dialog';
        dialog.setAttribute('aria-labelledby', 'pwaInstallTitle');

        const card = document.createElement('div');
        card.className = 'pwa-dialog__card';

        const icon = document.createElement('span');
        icon.className = 'pwa-dialog__icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = '<i class="bi bi-download"></i>';

        const title = document.createElement('h2');
        title.className = 'pwa-dialog__title';
        title.id = 'pwaInstallTitle';
        title.textContent = 'Install Traffic System';

        const body = document.createElement('div');
        body.className = 'pwa-dialog__body';
        const intro = document.createElement('p');
        intro.textContent = 'Add the app to your home screen for a full-screen window and faster access.';
        const list = document.createElement('ol');

        steps.forEach((step) => {
            const item = document.createElement('li');
            item.textContent = step;
            list.appendChild(item);
        });

        body.appendChild(intro);
        body.appendChild(list);

        const actions = document.createElement('div');
        actions.className = 'pwa-dialog__actions';

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'pwa-dialog__close';
        close.textContent = 'Got it';
        close.addEventListener('click', () => dialog.close());

        actions.appendChild(close);
        card.appendChild(icon);
        card.appendChild(title);
        card.appendChild(body);
        card.appendChild(actions);
        dialog.appendChild(card);

        // Tapping the dimmed backdrop dismisses the sheet.
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });

        dialog.addEventListener('close', () => dialog.remove());

        document.body.appendChild(dialog);
        dialog.showModal();
        close.focus();
    }

    /* ── Offline / update notices ────────────────────────────────────────── */
    if (isPrintMode) {
        return;
    }

    let offlineBanner = null;
    let updateBanner = null;

    const createBanner = (modifier, iconClass, message) => {
        const banner = document.createElement('div');
        banner.className = `pwa-banner ${modifier}`;
        banner.setAttribute('role', 'status');

        const icon = document.createElement('i');
        icon.className = `bi ${iconClass}`;
        icon.setAttribute('aria-hidden', 'true');

        const text = document.createElement('span');
        text.className = 'pwa-banner__text';
        text.textContent = message;

        banner.appendChild(icon);
        banner.appendChild(text);
        document.body.appendChild(banner);

        return { banner, text };
    };

    const syncConnectionBanner = () => {
        if (navigator.onLine) {
            if (offlineBanner) {
                offlineBanner.hidden = true;
            }
            return;
        }

        if (!offlineBanner) {
            const { banner, text } = createBanner(
                'pwa-banner--offline',
                'bi-wifi-off',
                'You are offline. Some features will not work until you reconnect.',
            );

            const retry = document.createElement('button');
            retry.type = 'button';
            retry.className = 'pwa-banner__action';
            retry.textContent = 'Retry';
            retry.addEventListener('click', () => window.location.reload());

            banner.appendChild(retry);
            offlineBanner = banner;
        }

        offlineBanner.hidden = false;
    };

    function showUpdateBanner() {
        if (updateBanner) {
            updateBanner.hidden = false;
            return;
        }

        const { banner } = createBanner(
            'pwa-banner--update',
            'bi-arrow-repeat',
            'A new version is available.',
        );

        const reload = document.createElement('button');
        reload.type = 'button';
        reload.className = 'pwa-banner__action';
        reload.textContent = 'Reload';
        reload.addEventListener('click', () => window.location.reload());

        const dismiss = document.createElement('button');
        dismiss.type = 'button';
        dismiss.className = 'pwa-banner__close';
        dismiss.setAttribute('aria-label', 'Dismiss update notice');
        dismiss.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
        dismiss.addEventListener('click', () => {
            banner.hidden = true;
        });

        banner.appendChild(reload);
        banner.appendChild(dismiss);
        updateBanner = banner;
    }

    syncConnectionBanner();
    window.addEventListener('online', syncConnectionBanner);
    window.addEventListener('offline', syncConnectionBanner);
})();
