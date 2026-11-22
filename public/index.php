<?php

use CodeIgniter\Boot;
use Config\Paths;

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Serve static assets directly when the request is rewritten to index.php.
//
// The previous version used readfile() for any file it found. That meant
// /traffic-/public/index.php, .htaccess, and even /traffic-/public/../.env
// were returned as plain text (PHP source and secrets included). Only allow
// known static extensions, block dotfiles, and require the resolved path to
// stay inside public/.
//
// Assets also get Cache-Control/ETag/Last-Modified headers. Without them the
// browser re-downloaded every CSS, JS, font, and image on each navigation,
// which is what made moving between screens feel slow. Conditional requests
// now return 304 so a normal navigation only transfers the HTML page.
if (isset($_SERVER['REQUEST_URI']) && php_sapi_name() !== 'cli') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (is_string($requestPath) && $requestPath !== '') {
        $relativeAsset = ltrim(preg_replace('#^/traffic-/public/#i', '', rawurldecode($requestPath)), '/');
        $assetFullPath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativeAsset);
        $publicRoot    = realpath(FCPATH);
        $realAsset     = $relativeAsset !== '' ? realpath($assetFullPath) : false;

        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico',
            'css', 'js', 'map', 'html', 'htm',
            'woff', 'woff2', 'ttf', 'otf', 'eot',
            'txt', 'xml',
        ];

        $extension = $realAsset !== false ? strtolower(pathinfo($realAsset, PATHINFO_EXTENSION)) : '';
        $isDotFile = $realAsset !== false && str_starts_with(basename($realAsset), '.');
        $isInsidePublic = $realAsset !== false
            && $publicRoot !== false
            && str_starts_with(
                strtolower($realAsset),
                strtolower($publicRoot) . DIRECTORY_SEPARATOR
            );

        if ($isInsidePublic && ! $isDotFile && in_array($extension, $allowedExtensions, true)) {
            $mimes = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'svg'  => 'image/svg+xml',
                'css'  => 'text/css; charset=UTF-8',
                'js'   => 'application/javascript; charset=UTF-8',
                'map'  => 'application/json; charset=UTF-8',
                'html' => 'text/html; charset=UTF-8',
                'htm'  => 'text/html; charset=UTF-8',
                'ico'  => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2'=> 'font/woff2',
                'ttf'  => 'font/ttf',
                'otf'  => 'font/otf',
                'eot'  => 'application/vnd.ms-fontobject',
                'txt'  => 'text/plain; charset=UTF-8',
                'xml'  => 'application/xml; charset=UTF-8',
            ];

            $mtime        = filemtime($realAsset);
            $size         = filesize($realAsset);
            $etag         = '"' . dechex($mtime) . '-' . dechex($size) . '"';
            $lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

            // Uploaded photos can be replaced under the same name, so they get
            // a much shorter cache than versioned vendor/app assets. They are
            // also kept out of shared caches because they are personal images.
            $isUpload = str_starts_with(strtolower($relativeAsset), 'uploads/');
            $maxAge   = $isUpload ? 300 : 604800;
            $scope    = $isUpload ? 'private' : 'public';

            header('Cache-Control: ' . $scope . ', max-age=' . $maxAge . ', stale-while-revalidate=86400');
            header('ETag: ' . $etag);
            header('Last-Modified: ' . $lastModified);
            header('Vary: Accept-Encoding');
            header('X-Content-Type-Options: nosniff');

            $ifNoneMatch     = trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''));
            $ifModifiedSince = trim((string) ($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? ''));
            $noneMatchTags   = array_map(
                static fn (string $tag): string => trim(str_replace('W/', '', $tag)),
                explode(',', $ifNoneMatch)
            );

            $notModified = $ifNoneMatch === '*'
                || in_array($etag, $noneMatchTags, true)
                || ($ifNoneMatch === '' && $ifModifiedSince !== '' && strtotime($ifModifiedSince) >= $mtime);

            if ($notModified) {
                http_response_code(304);
                exit;
            }

            $mime = $mimes[$extension]
                ?? (function_exists('mime_content_type') ? mime_content_type($realAsset) : 'application/octet-stream');

            header('Content-Type: ' . $mime);
            header('Content-Length: ' . $size);

            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
                exit;
            }

            readfile($realAsset);
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
