<?php
/**
 * Diagnostic only: proves whether the app's own .env credentials can open the
 * database. Prints no secrets - just OK / the server error.
 */

$envFile = __DIR__ . '/../.env';

if (! is_file($envFile)) {
    fwrite(STDERR, ".env not found\n");
    exit(2);
}

$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);
    $key   = trim($key);
    $value = trim($value);
    $value = trim($value, "'\"");

    $env[$key] = $value;
}

$host = $env['database.default.hostname'] ?? '127.0.0.1';
$port = (int) ($env['database.default.port'] ?? 3306);
$user = $env['database.default.username'] ?? '';
$pass = $env['database.default.password'] ?? '';
$db   = $env['database.default.database'] ?? '';

if ($user === '' || $db === '') {
    fwrite(STDERR, "database.default.username / database are missing in .env\n");
    exit(2);
}

mysqli_report(MYSQLI_REPORT_OFF);
$link = @mysqli_connect($host, $user, $pass, $db, $port);

if (! $link) {
    echo 'LOGIN FAILED: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error() . PHP_EOL;
    exit(1);
}

$result = mysqli_query($link, 'SELECT COUNT(*) AS n FROM violations');

if (! $result) {
    echo 'LOGIN OK but query failed: ' . mysqli_error($link) . PHP_EOL;
    exit(1);
}

$row = mysqli_fetch_assoc($result);
echo 'LOGIN OK, violations rows: ' . $row['n'] . PHP_EOL;
