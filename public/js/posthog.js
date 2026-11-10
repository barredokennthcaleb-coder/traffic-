/**
 * PostHog Session Replay for the Traffic System app.
 *
 * Loaded as a native ES module from app/Views/theme/posthog.php, which also
 * prints the project token (from .env) and the import map that resolves the
 * `posthog-js` specifier to the pinned SDK build.
 *
 * Ground rules implemented here:
 *  - nothing is captured before the visitor answers the consent prompt;
 *  - typing is always masked, so plate numbers, names and passwords never
 *    leave the browser;
 *  - a rejected consent, a revoked consent or a stored choice from an older
 *    cookie notice all keep recording off;
 *  - replays are attached to the session's user and to the journey events the
 *    server queues (see AuthController::loginPost()).
 */
import posthog from 'posthog-js';

const CONSENT_KEY = 'posthog_consent';
const GRANTED = 'granted';
const DENIED = 'denied';
const BANNER_ID = 'posthog-consent-banner';
const DEFAULT_HOST = 'https://us.i.posthog.com';

function readConfig() {
    const node = document.getElementById('posthog-config');
    if (!node) return null;

    try {
        return JSON.parse(node.textContent || '{}');
    } catch (error) {
        return null;
    }
}

/**
 * The visitor's decision, from our own key first, then from a consent cookie
 * written by an older notice. `null` means "not asked yet".
 */
function readConsent() {
    try {
        const stored = window.localStorage.getItem(CONSENT_KEY);
        if (stored === GRANTED || stored === DENIED) return stored;
    } catch (error) {
        // Private mode: fall through to the cookie.
    }

    const cookie = document.cookie.match(/(?:^|;\s*)(?:cookie_consent|analytics_consent)=(granted|denied)\b/);
    return cookie ? cookie[1] : null;
}

function writeConsent(value) {
    try {
        window.localStorage.setItem(CONSENT_KEY, value);
    } catch (error) {
        // Without storage the choice only lasts for this page load.
    }
}

/** Identify the signed-in staff member and send the queued journey events. */
function sendIdentity(instance, config) {
    const properties = config.properties || {};

    if (config.distinctId) {
        instance.identify(config.distinctId, properties);
    }

    (config.journey || []).forEach(function (name) {
        if (typeof name === 'string' && name) {
            instance.capture(name, { page: properties.page });
        }
    });
}

function showConsentBanner(onChoice) {
    if (document.getElementById(BANNER_ID)) return;

    const banner = document.createElement('div');
    banner.id = BANNER_ID;
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-label', 'Session recording consent');
    banner.style.cssText = [
        'position:fixed', 'left:16px', 'right:16px', 'bottom:16px', 'z-index:1080',
        'max-width:540px', 'margin:0 auto', 'display:flex', 'flex-wrap:wrap',
        'align-items:center', 'gap:0.75rem', 'padding:0.9rem 1rem',
        'border-radius:14px', 'background:#0f172a', 'color:#f8fafc',
        'box-shadow:0 10px 30px rgba(15,23,42,0.35)',
        'font-family:inherit', 'font-size:0.85rem', 'line-height:1.35',
    ].join(';');

    const text = document.createElement('p');
    text.style.cssText = 'margin:0;flex:1 1 250px;';
    text.textContent = 'Help us improve this system: we record anonymised session replays. What you type is always hidden.';

    const allow = document.createElement('button');
    allow.type = 'button';
    allow.textContent = 'Allow';
    allow.style.cssText = [
        'cursor:pointer', 'border:0', 'border-radius:9px', 'padding:0.45rem 0.9rem',
        'background:#0066ff', 'color:#fff', 'font-weight:600', 'font-size:0.82rem',
        'font-family:inherit',
    ].join(';');

    const decline = document.createElement('button');
    decline.type = 'button';
    decline.textContent = 'No thanks';
    decline.style.cssText = [
        'cursor:pointer', 'border:1px solid rgba(248,250,252,0.35)', 'border-radius:9px',
        'padding:0.45rem 0.9rem', 'background:transparent', 'color:#f8fafc',
        'font-weight:600', 'font-size:0.82rem', 'font-family:inherit',
    ].join(';');

    allow.addEventListener('click', function () {
        writeConsent(GRANTED);
        banner.remove();
        onChoice(GRANTED);
    });

    decline.addEventListener('click', function () {
        writeConsent(DENIED);
        banner.remove();
        onChoice(DENIED);
    });

    banner.append(text, allow, decline);
    document.body.appendChild(banner);
}

function initPostHog(config, instance) {
    let consent = readConsent();

    function applyConsent(choice) {
        consent = choice;

        if (choice === GRANTED) {
            instance.opt_in_capturing();
            sendIdentity(instance, config);
            instance.capture('replay_consent_granted');
            return;
        }

        instance.opt_out_capturing();
        instance.stopSessionRecording();
    }

    if (consent !== null) {
        applyConsent(consent);
        return;
    }

    showConsentBanner(applyConsent);
}

const config = readConfig();

if (config && config.apiKey) {
    /**
     * Session API used by the views:
     *   PostHogTrack.event('violation_recorded', { type: 'parking' })
     *   PostHogTrack.state()   // 'granted' | 'denied' | null
     */
    window.PostHogTrack = {
        state: readConsent,
        event: function (name, properties) {
            if (readConsent() !== GRANTED || typeof name !== 'string' || !name) return;
            posthog.capture(name, properties || {});
        },
    };

    posthog.init(config.apiKey, {
        api_host: config.apiHost || DEFAULT_HOST,
        defaults: '2026-08-30',
        person_profiles: 'identified_only',
        respect_dnt: true,
        capture_pageview: true,
        capture_pageleave: true,
        // A signed-in browser holds one person; the login page clears it so a
        // shared workstation never credits the next visitor to the last one.
        opt_out_capturing_by_default: true,
        session_recording: {
            maskAllInputs: true,
            maskTextSelector: '[data-ph-mask], .ph-mask',
            blockSelector: '.ph-no-capture',
            // Query strings carry search terms and record ids, so only the
            // path is kept for the replay's URL bar and captured requests.
            maskCapturedNetworkRequestFn: function (request) {
                if (request && typeof request.name === 'string') {
                    request.name = request.name.split('?')[0];
                }
                return request;
            },
        },
        loaded: function (instance) {
            initPostHog(config, instance);
        },
    });
}
