<?php
declare(strict_types=1);

/**
 * Build to Manage (BTM) — L1–L3 Synthetic Readiness & Liveness Probe
 * Stack: LAMP (PHP 8.4 + Apache 2.4 + MySQL 8.4 LTS)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

$traceId = bin2hex(random_bytes(16));
$spanId  = bin2hex(random_bytes(8));
$traceparent = sprintf('00-%s-%s-01', $traceId, $spanId);
header('traceparent: ' . $traceparent);

$startTime = microtime(true);

$dbHost = getenv('DB_HOST') ?: 'mysql';
$dbPort = (int) (getenv('DB_PORT') ?: 3306);
$dbName = getenv('DB_DATABASE') ?: 'app_db';
$dbUser = getenv('DB_USERNAME') ?: 'app_user';
$dbPass = getenv('DB_PASSWORD') ?: '';

$dbStatus  = 'UP';
$dbLatency = 0.0;
$dbVersion = 'mysql:8.4-lts';
$dbError   = null;

$t0 = microtime(true);
try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_TIMEOUT            => 2,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $row = $pdo->query('SELECT VERSION() AS version')->fetch();
    if (is_array($row) && isset($row['version'])) {
        $dbVersion = (string) $row['version'];
    }
    $dbLatency = round((microtime(true) - $t0) * 1000, 2);
} catch (Throwable $e) {
    $dbStatus  = 'DOWN';
    $dbLatency = round((microtime(true) - $t0) * 1000, 2);
    $dbError   = 'Database connection probe failed';
}

$opcacheStatus = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;
$jitEnabled    = is_array($opcacheStatus) && !empty($opcacheStatus['jit']['enabled']);

$overallStatus = ($dbStatus === 'UP') ? 'UP' : 'DEGRADED';
http_response_code($dbStatus === 'UP' ? 200 : 503);

echo json_encode([
    'service'     => 'lamp-docker',
    'status'      => $overallStatus,
    'timestamp'   => (new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get())))->format(DateTimeInterface::ATOM),
    'traceparent' => $traceparent,
    'runtime'     => [
        'php_version' => PHP_VERSION,
        'sapi'        => PHP_SAPI,
        'timezone'    => date_default_timezone_get(),
        'opcache_jit' => $jitEnabled ? 'ENABLED' : 'DISABLED',
    ],
    'checks' => [
        'L1_process'  => ['status' => 'UP'],
        'L2_runtime'  => ['status' => 'UP', 'extensions' => ['pdo_mysql' => extension_loaded('pdo_mysql'), 'intl' => extension_loaded('intl')]],
        'L3_database' => [
            'driver'     => 'mysql',
            'status'     => $dbStatus,
            'version'    => $dbVersion,
            'latency_ms' => $dbLatency,
            'error'      => $dbError,
        ],
    ],
    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
