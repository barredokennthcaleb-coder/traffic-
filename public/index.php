<?php

use CodeIgniter\Boot;
use Config\Paths;

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Serve static assets directly if Apache rewrote them to index.php
if (isset($_SERVER['REQUEST_URI']) && php_sapi_name() !== 'cli') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($requestPath) {
        $relativeAsset = ltrim(preg_replace('#^/traffic-/public/#i', '', urldecode($requestPath)), '/');
        $assetFullPath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativeAsset);
        if ($relativeAsset !== '' && is_file($assetFullPath)) {
            $ext = strtolower(pathinfo($assetFullPath, PATHINFO_EXTENSION));
            $mimes = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'svg'  => 'image/svg+xml',
                'css'  => 'text/css',
                'js'   => 'application/javascript',
                'ico'  => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2'=> 'font/woff2',
                'ttf'  => 'font/ttf',
            ];
            $mime = $mimes[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($assetFullPath) : 'application/octet-stream');
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($assetFullPath));
            readfile($assetFullPath);
            exit;
        }
    }
}

$minPhpVersion = '8.2'; // If you update this, don't forget to update `spark`.

if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

/*
 * Fix XAMPP SCRIPT_NAME URI stripping issue:
 * When Apache serves the site under XAMPP (htdocs), set SCRIPT_NAME to include
 * the subfolder path relative to htdocs so CI4's parseRequestURI() properly
 * strips the subfolder segments from REQUEST_URI.
 */
if (isset($_SERVER['SCRIPT_FILENAME']) && php_sapi_name() !== 'cli') {
    $scriptFn = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: $_SERVER['SCRIPT_FILENAME']);
    $htdocsPos = strpos(strtolower($scriptFn), '/htdocs/');
    if ($htdocsPos !== false) {
        $_SERVER['SCRIPT_NAME'] = substr($scriptFn, $htdocsPos + 7);
    }
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 */

// LOAD OUR PATHS CONFIG FILE
require FCPATH . '../app/Config/Paths.php';

$paths = new Paths();

// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
