<?php
/**
 * Progressive Web App head tags.
 *
 * Included by the admin layout and the standalone public screens (sign-in,
 * register, password reset, lookup). The two colours below are written into
 * the theme-color meta and swapped by js/pwa.js whenever the app palette
 * changes, so the browser/status bar never disagrees with the page.
 *
 * The icons live in public/pwa-icons/ rather than public/icons/ because
 * XAMPP's Apache config reserves the /icons/ URL for its own sample icons.
 */
$pwaThemeDark  = $pwaThemeDark ?? '#0b0f19';
$pwaThemeLight = $pwaThemeLight ?? '#f8fafc';
$pwaCssVersion = is_file(FCPATH . 'css/pwa.css') ? (string) filemtime(FCPATH . 'css/pwa.css') : '1';
?>
<meta name="application-name" content="Traffic System">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Traffic System">
<meta name="theme-color" content="<?= esc($pwaThemeLight) ?>" data-dark="<?= esc($pwaThemeDark) ?>" data-light="<?= esc($pwaThemeLight) ?>" id="pwaThemeColor">
<link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('pwa-icons/apple-touch-icon.png') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('pwa-icons/favicon-32.png') ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= base_url('pwa-icons/icon-192.png') ?>">
<link rel="stylesheet" href="<?= base_url('css/pwa.css') ?>?v=<?= $pwaCssVersion ?>">
