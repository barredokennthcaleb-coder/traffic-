<?php
/**
 * PostHog session replay: configuration for public/js/posthog.js.
 *
 * Include it in any page whose session should be recorded (the app layout and
 * the login screen). Without a project token in .env nothing is printed, so a
 * fresh checkout runs exactly as it did before.
 */
$posthogKey  = trim((string) (env('posthog.apiKey') ?? ''));
$posthogHost = trim((string) (env('posthog.apiHost') ?? ''));
$posthogSdk  = '1.443.0';

if ($posthogKey === '') {
    return;
}

$posthogSession = session();
$posthogJourney = $posthogSession->get('posthog_journey');
$posthogSession->remove('posthog_journey');
$posthogUserId  = $posthogSession->get('id');

$posthogConfig = [
    // The project token is public by design (like a Google Analytics id).
    'apiKey'     => $posthogKey,
    'apiHost'    => $posthogHost !== '' ? $posthogHost : 'https://us.i.posthog.com',
    'distinctId' => $posthogUserId ? 'user-' . $posthogUserId : null,
    'properties' => [
        'role' => (string) ($posthogSession->get('role') ?? 'guest'),
        'page' => uri_string(),
    ],
    // Journey events queued by the controller that handled the previous post.
    'journey'    => array_values(array_filter((array) ($posthogJourney ?? []), 'is_string')),
];

$posthogJson = static fn (array $payload): string => (string) json_encode(
    $payload,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<script type="application/json" id="posthog-config"><?= $posthogJson($posthogConfig) ?></script>
<script type="importmap">
<?= $posthogJson(['imports' => [
    'posthog-js' => 'https://cdn.jsdelivr.net/npm/posthog-js@' . $posthogSdk . '/dist/module.mjs',
]]) ?>
</script>
<script type="module" src="<?= base_url('js/posthog.js') ?>"></script>
