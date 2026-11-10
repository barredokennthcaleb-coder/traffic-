<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Gzip the response body when the browser asks for it.
 *
 * The app runs on PHP's built-in server (php -S), which has no mod_deflate,
 * so pages were sent to the browser uncompressed — roughly 330 KB of HTML and
 * inline CSS/JS on the heavy screens. Compressing here cuts that by about six
 * times without changing a single byte of the markup the user sees.
 *
 * It stays out of the way when something else already compressed the
 * response (Apache's mod_deflate, for instance), and it never touches binary
 * downloads such as the CSV exports.
 */
class CompressResponse implements FilterInterface
{
    /**
     * Response types worth compressing. Anything else is left alone.
     */
    private const COMPRESSIBLE = '#^(text/|application/(json|javascript|xml|xhtml\+xml|rss\+xml)|image/svg\+xml)#i';

    public function before(RequestInterface $request, $arguments = null)
    {
        // Nothing to do before the controller runs.
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (is_cli() || ENVIRONMENT === 'testing') {
            return null;
        }

        // Only successful, non-empty bodies.
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        // Already encoded by a web server level filter — don't double gzip.
        if ($response->hasHeader('Content-Encoding')) {
            return null;
        }

        if (
            function_exists('apache_get_modules')
            && in_array('mod_deflate', apache_get_modules(), true)
        ) {
            return null;
        }

        if (! function_exists('gzencode')) {
            return null;
        }

        if (strtolower($request->getMethod()) === 'head') {
            return null;
        }

        if (! str_contains(strtolower($request->getHeaderLine('Accept-Encoding')), 'gzip')) {
            return null;
        }

        if (preg_match(self::COMPRESSIBLE, $response->getHeaderLine('Content-Type')) !== 1) {
            return null;
        }

        $body = $response->getBody();

        if (! is_string($body) || $body === '' || strlen($body) < 1024) {
            return null;
        }

        $compressed = gzencode($body, 6);

        // If compression did not actually save anything, keep the original.
        if ($compressed === false || strlen($compressed) >= strlen($body)) {
            return null;
        }

        $response->setBody($compressed);
        $response->setHeader('Content-Encoding', 'gzip');
        $response->removeHeader('Content-Length');
        $response->setHeader('Vary', 'Accept-Encoding');

        return null;
    }
}
