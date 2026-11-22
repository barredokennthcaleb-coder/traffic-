<?php

namespace App\Exceptions;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Exceptions as ExceptionsConfig;
use Throwable;

/**
 * Friendly fallback for database connection failures.
 *
 * If MySQL is stopped or refusing connections, CodeIgniter would normally
 * throw a full DatabaseException with a developer stack trace. This handler
 * turns *connection* failures into a calm "temporarily unavailable" page:
 * - the page says the system is having a database problem and to try again,
 * - the technical detail is written to writable/logs/ instead,
 * - every other exception (including real SQL errors while developing)
 *   still goes through the normal handler.
 *
 * It is wired up in Config\Exceptions::handler().
 */
class DatabaseExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode,
    ): void {
        // Only take over for connection failures. Real query errors keep the
        // normal debug output, which is more useful while developing.
        if (! $exception instanceof DatabaseException
            || ! str_contains($exception->getMessage(), 'Unable to connect to the database')) {
            (new ExceptionHandler(new ExceptionsConfig()))->handle($exception, $request, $response, $statusCode, $exitCode);

            return;
        }

        // Keep the real reason in the log for the admin.
        try {
            log_message('critical', 'Database connection failure handled gracefully: {message}', [
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable) {
            // Never let logging break the fallback page.
        }

        // Hosted platforms (Render, Docker, systemd) capture stderr, not the
        // writable/logs file inside the container. Mirror the reason there so
        // the real connection error is visible in the platform log stream.
        error_log('Database connection failure: ' . $exception->getMessage());

        // Temporary deployment diagnostic: show which connection values the
        // app actually resolved. Contains no password.
        try {
            $db = config('Database');
            error_log('DB resolved config: ' . json_encode([
                'hostname'      => $db->default['hostname'] ?? null,
                'port'          => $db->default['port'] ?? null,
                'username'      => $db->default['username'] ?? null,
                'database'      => $db->default['database'] ?? null,
                'encrypt'       => $db->default['encrypt'] ?? null,
                'env_hostname'  => getenv('database.default.hostname'),
                'env_port'      => getenv('database.default.port'),
                'env_encrypt'   => getenv('database.default.encrypt'),
            ]));
        } catch (Throwable) {
        }

        $statusCode = 503;

        if ($request instanceof IncomingRequest && str_contains($request->getHeaderLine('accept'), 'text/html')) {
            $response->setStatusCode($statusCode);

            if (! headers_sent()) {
                header(sprintf('HTTP/%s 503 Service Unavailable', $request->getProtocolVersion()), true, 503);
            }

            $response->setBody($this->fallbackPage());
            $response->send();

            if (ENVIRONMENT !== 'testing') {
                exit($exitCode);
            }

            return;
        }

        // CLI clients get a short, clean message instead of a stack trace.
        if ($request instanceof CLIRequest) {
            fwrite(STDERR, 'The database is not available right now. Check that MySQL is running, then try again.' . PHP_EOL);

            if (ENVIRONMENT !== 'testing') {
                exit($exitCode);
            }

            return;
        }

        // API / JSON clients.
        $response->setStatusCode($statusCode);
        $response->setJSON([
            'status'  => $statusCode,
            'error'   => 'Service Unavailable',
            'message' => 'The database is not available right now. Please try again in a moment.',
        ]);
        $response->send();

        if (ENVIRONMENT !== 'testing') {
            exit($exitCode);
        }
    }

    /**
     * A small, self-contained page: no framework helpers, styles or assets
     * required, so it works even when nothing else does.
     */
    private function fallbackPage(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Temporarily unavailable</title>
<style>
    :root { color-scheme: light dark; }
    * { box-sizing: border-box; }
    body {
        margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
        background: #f2f4f7; color: #1f2937;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        padding: 24px;
    }
    .card {
        max-width: 480px; width: 100%; background: #ffffff; border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .08); padding: 36px 32px; text-align: center;
    }
    .icon {
        width: 56px; height: 56px; margin: 0 auto 18px; border-radius: 50%;
        background: #fee2e2; display: flex; align-items: center; justify-content: center;
        font-size: 26px;
    }
    h1 { font-size: 20px; margin: 0 0 10px; }
    p { font-size: 15px; line-height: 1.55; color: #4b5563; margin: 0 0 22px; }
    button {
        appearance: none; border: 0; cursor: pointer; font: inherit; font-weight: 600;
        background: #2563eb; color: #fff; padding: 11px 26px; border-radius: 9px;
    }
    button:hover { background: #1d4ed8; }
    .hint { margin-top: 22px; font-size: 12.5px; color: #9ca3af; }
    .retry { margin-top: 14px; font-size: 13px; color: #6b7280; }
    .bar {
        margin: 16px auto 0; height: 4px; width: 180px; border-radius: 999px;
        background: #e5e7eb; overflow: hidden;
    }
    .bar > i {
        display: block; height: 100%; width: 100%; border-radius: 999px;
        background: #2563eb; transform-origin: left center;
        animation: shrink 8s linear forwards;
    }
    @keyframes shrink { from { transform: scaleX(1); } to { transform: scaleX(0); } }
</style>
</head>
<body>
    <div class="card">
        <div class="icon" aria-hidden="true">&#9888;&#65039;</div>
        <h1>Temporarily unavailable</h1>
        <p>
            The system is having a problem talking to its database right now.
            Your data is safe &mdash; this page will bring itself back automatically.
        </p>
        <button onclick="location.reload()">Try again now</button>
        <div class="bar"><i></i></div>
        <div class="retry" id="retry-note">Reconnecting automatically&hellip;</div>
        <div class="hint">If this keeps happening, ask the administrator to check that MySQL is running.</div>
    </div>
    <script>
        (function () {
            // Smooth recovery: quietly reload this exact page every 8 seconds
            // until the database is back. No button mashing needed.
            var wait = 8;
            var note = document.getElementById('retry-note');
            var tick = function () {
                if (note) { note.textContent = 'Reconnecting automatically in ' + wait + 's\u2026'; }
                if (wait-- <= 0) { location.reload(); return; }
                setTimeout(tick, 1000);
            };
            tick();
        })();
    </script>
</body>
</html>
HTML;
    }
}
