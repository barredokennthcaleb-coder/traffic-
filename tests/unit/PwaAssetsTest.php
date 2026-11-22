<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guards the installable-app contract: a valid manifest, correctly sized
 * icons, a service worker with the three lifecycle handlers, the offline
 * fallback, and the head/script includes on every public entry screen.
 *
 * @internal
 */
final class PwaAssetsTest extends CIUnitTestCase
{
    private function publicFile(string $file): string
    {
        return FCPATH . $file;
    }

    public function testManifestIsValidAndInstallable(): void
    {
        $path = $this->publicFile('manifest.webmanifest');

        $this->assertFileExists($path);

        $source   = (string) file_get_contents($path);
        $manifest = json_decode($source, true);

        // XAMPP's Apache reserves /icons/ for its own sample icons, so the
        // manifest must keep using the pwa-icons/ directory.
        $this->assertStringNotContainsString('"icons/', $source, 'PWA icons must not use the Apache-reserved /icons/ path');

        $this->assertIsArray($manifest);
        $this->assertSame('./', $manifest['start_url']);
        $this->assertSame('./', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertNotSame('', $manifest['name']);
        $this->assertNotSame('', $manifest['short_name']);
        $this->assertNotEmpty($manifest['icons']);

        $purposes = [];

        foreach ($manifest['icons'] as $icon) {
            $iconPath = $this->publicFile($icon['src']);

            $this->assertFileExists($iconPath);
            $this->assertSame('image/png', $icon['type']);

            $size = getimagesize($iconPath);

            $this->assertNotFalse($size, $icon['src'] . ' is not a readable image');
            $this->assertSame($icon['sizes'], $size[0] . 'x' . $size[1], $icon['src'] . ' size does not match its manifest entry');

            $purposes[] = $icon['purpose'] ?? 'any';
        }

        $this->assertContains('any', $purposes);
        $this->assertContains('maskable', $purposes);
    }

    public function testServiceWorkerAndOfflinePageExist(): void
    {
        $serviceWorker = $this->publicFile('sw.js');

        $this->assertFileExists($serviceWorker);

        $source = (string) file_get_contents($serviceWorker);

        $this->assertStringContainsString("addEventListener('install'", $source);
        $this->assertStringContainsString("addEventListener('activate'", $source);
        $this->assertStringContainsString("addEventListener('fetch'", $source);
        $this->assertStringContainsString('offline.html', $source);

        // Signed-in screens must never be written to the Cache API.
        $this->assertStringContainsString("request.mode === 'navigate'", $source);
        $this->assertStringContainsString('networkFirstNavigation', $source);

        $offline = $this->publicFile('offline.html');

        $this->assertFileExists($offline);
        $this->assertStringContainsString("You're offline", (string) file_get_contents($offline));
    }

    public function testAdminLayoutAdvertisesTheManifestAndRegistersTheWorker(): void
    {
        $layout = (string) file_get_contents(APPPATH . 'Views/layouts/admin.php');

        $this->assertStringContainsString("include('theme/pwa_head')", $layout);
        $this->assertStringContainsString("include('theme/pwa_scripts')", $layout);

        $head = (string) file_get_contents(APPPATH . 'Views/theme/pwa_head.php');

        $this->assertStringContainsString('rel="manifest"', $head);
        $this->assertStringContainsString('apple-touch-icon', $head);
        $this->assertStringContainsString('theme-color', $head);
    }

    public function testPublicScreensAdvertiseTheManifest(): void
    {
        $views = [
            'auth/login.php',
            'auth/register.php',
            'auth/forgot_password.php',
            'lookup/index.php',
        ];

        foreach ($views as $view) {
            $source = (string) file_get_contents(APPPATH . 'Views/' . $view);

            $this->assertStringContainsString("view('theme/pwa_head'", $source, $view . ' is missing the PWA head tags');
            $this->assertStringContainsString("view('theme/pwa_scripts'", $source, $view . ' is missing the PWA registration script');
        }
    }

    public function testInstallBannerPopsUpAutomatically(): void
    {
        $script = (string) file_get_contents($this->publicFile('js/pwa.js'));

        $this->assertStringContainsString("addEventListener('beforeinstallprompt'", $script);
        $this->assertStringContainsString('scheduleInstallBanner', $script);
        $this->assertStringContainsString('INSTALL_FALLBACK_DELAY', $script);
        $this->assertStringContainsString('pwa-install-banner', $script);
        $this->assertStringContainsString('data-pwa-banner-install', $script);

        $styles = (string) file_get_contents($this->publicFile('css/pwa.css'));

        $this->assertStringContainsString('.pwa-install-banner', $styles);
        $this->assertStringContainsString('.pwa-notice-stack', $styles);
        $this->assertStringContainsString('pwaStackIn', $styles);
    }
}
