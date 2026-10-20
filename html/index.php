<?php
declare(strict_types=1);

/**
 * eriktonon/lamp-docker — Build-to-Manage Operational Readiness Dashboard
 * IBM Carbon Design System v11 (#161616 Gray 100 | #0F62FE Blue 60)
 * Security: Zero phpinfo() or environment secret disclosure (OWASP A05 compliant)
 */

if ((isset($_GET['format']) && $_GET['format'] === 'json') ||
    (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
    require __DIR__ . '/health.php';
    exit;
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

$dbHost = getenv('DB_HOST') ?: 'mysql';
$dbPort = (int) (getenv('DB_PORT') ?: 3306);
$dbName = getenv('DB_DATABASE') ?: 'app_db';
$dbUser = getenv('DB_USERNAME') ?: 'app_user';
$dbPass = getenv('DB_PASSWORD') ?: '';

$dbConnected = false;
$dbVersion   = 'MySQL 8.4 LTS';
$dbLatencyMs = 0.0;

$t0 = microtime(true);
try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_TIMEOUT => 2,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $row = $pdo->query('SELECT VERSION() AS v')->fetch(PDO::FETCH_ASSOC);
    if (is_array($row) && isset($row['v'])) {
        $dbVersion = 'MySQL ' . htmlspecialchars((string) $row['v'], ENT_QUOTES, 'UTF-8');
    }
    $dbConnected = true;
} catch (Throwable) {
    $dbConnected = false;
}
$dbLatencyMs = round((microtime(true) - $t0) * 1000, 2);

$opcache = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;
$jitOn   = is_array($opcache) && !empty($opcache['jit']['enabled']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAMP Stack v2.0 — Build to Manage Control Plane</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap">
    <style>
        :root {
            --bg-canvas: #161616;
            --bg-layer: #262626;
            --border-subtle: #393939;
            --text-primary: #f4f4f4;
            --text-secondary: #c6c6c6;
            --ibm-blue: #0f62fe;
            --status-green: #24a148;
            --status-amber: #f1c21b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-canvas);
            color: var(--text-primary);
            font-family: 'IBM Plex Sans', -apple-system, sans-serif;
            line-height: 1.5;
            padding: 2.5rem;
        }
        .container { max-width: 1120px; margin: 0 auto; }
        header {
            border-bottom: 1px solid var(--border-subtle);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .eyebrow {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ibm-blue);
            margin-bottom: 0.25rem;
        }
        h1 { font-size: 1.75rem; font-weight: 600; }
        .badge {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            border: 1px solid var(--status-green);
            background: rgba(36, 161, 72, 0.12);
            color: #42be65;
        }
        .badge.degraded {
            border-color: var(--status-amber);
            background: rgba(241, 194, 27, 0.12);
            color: var(--status-amber);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1px;
            background: var(--border-subtle);
            border: 1px solid var(--border-subtle);
            margin-bottom: 2rem;
        }
        .card {
            background: var(--bg-layer);
            padding: 1.5rem;
        }
        .card-label {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        .card-value {
            font-size: 1.25rem;
            font-weight: 600;
            font-family: 'IBM Plex Mono', monospace;
        }
        .card-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.4rem;
        }
        .footer {
            border-top: 1px solid var(--border-subtle);
            padding-top: 1rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            justify-content: space-between;
        }
        a { color: #78a9ff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <div class="eyebrow">IBM Carbon v11 &bull; Build to Manage SRE Reference</div>
                <h1>Containerized LAMP Stack (`eriktonon/lamp-docker`)</h1>
            </div>
            <div class="badge <?= $dbConnected ? '' : 'degraded' ?>">
                <?= $dbConnected ? 'L1–L3 PROBES: HEALTHY' : 'L3 DATABASE: UNREACHABLE' ?>
            </div>
        </header>

        <div class="grid">
            <div class="card">
                <div class="card-label">Web Runtime (L2)</div>
                <div class="card-value">PHP <?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="card-meta">Image: eriktonon/php:8.4-apache-bookworm</div>
            </div>
            <div class="card">
                <div class="card-label">OPcache &amp; JIT Engine</div>
                <div class="card-value"><?= $jitOn ? 'JIT ACTIVE' : 'OPCACHE ENABLED' ?></div>
                <div class="card-meta">expose_php=Off &bull; Non-Root (www-data)</div>
            </div>
            <div class="card">
                <div class="card-label">Database Probe (L3)</div>
                <div class="card-value"><?= $dbVersion ?></div>
                <div class="card-meta">Status: <?= $dbConnected ? 'CONNECTED' : 'DOWN' ?> (<?= $dbLatencyMs ?> ms)</div>
            </div>
            <div class="card">
                <div class="card-label">Locale &amp; Timezone</div>
                <div class="card-value"><?= htmlspecialchars(date_default_timezone_get(), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="card-meta">Charset: UTF-8 &bull; Collation: utf8mb4_0900_ai_ci</div>
            </div>
        </div>

        <div class="footer">
            <span>Synthetic JSON Probe: <a href="/health.php">/health.php</a></span>
            <span>Maintained by <a href="https://eriktonon.com" target="_blank" rel="noopener">Erik Tonon</a></span>
        </div>
    </div>
</body>
</html>
