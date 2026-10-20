# Security Policy & Hardening Baseline (`eriktonon/lamp-docker`)

## Build-to-Manage & Shift-Left AppSec Controls

This repository enforces **IBM Build-to-Manage (BTM)**, **OWASP Top 10**, and **CIS Docker Benchmark** controls across all services:

1. **Modern LTS Hardened Base Images**:
   - Web Runtime: [`eriktonon/php:8.4-apache-bookworm`](https://hub.docker.com/r/eriktonon/php) (`php:8.4.5-apache-bookworm`, non-root `www-data`, OPcache JIT, Composer 2.8.6, `America/Sao_Paulo`, `pt_BR.UTF-8`).
   - Database Engine: [`eriktonon/mysql:8.4-lts`](https://hub.docker.com/r/eriktonon/mysql) (`mysql:8.4.4`, non-root `mysql`, `utf8mb4` / `utf8mb4_0900_ai_ci`, `America/Sao_Paulo`).
   - Database Administration (Optional Profile `tools`): [`eriktonon/phpmyadmin:5.2`](https://hub.docker.com/r/eriktonon/phpmyadmin) (`phpmyadmin:5.2.2-apache`, non-root `www-data`).
2. **Loopback Interface Binding (`CWE-284` / `OWASP A05`)**:
   - All published ports bind strictly to `127.0.0.1` (`127.0.0.1:8080:80`, `127.0.0.1:3306:3306`, `127.0.0.1:8081:80`) to prevent accidental LAN/WAN database exposure.
3. **Zero Information Disclosure (`CWE-200` / `OWASP A05`)**:
   - Removed legacy `phpinfo()` root exposure.
   - Enforced `expose_php = Off`, `display_errors = Off`, `ServerTokens Prod`, `ServerSignature Off`, `TraceEnable Off`, and `Options -Indexes`.
   - Enforced HTTP defensive headers (`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`).
4. **Container Privilege & Resource Governance (`CWE-250`)**:
   - `security_opt: ["no-new-privileges:true"]` on every service.
   - Read-only bind mounts (`:ro`) for application source, `php.ini`, and Apache VirtualHost configurations.
   - Explicit CPU and memory `requests` and `limits` to prevent host resource exhaustion.
5. **Deterministic L1–L3 Readiness Probes**:
   - `/health.php` validates L1 process liveness, L2 PHP 8.4 / OPcache JIT runtime, and L3 MySQL 8.4 LTS PDO query latency with `W3C TraceContext` (`traceparent`) propagation.

## Reporting a Vulnerability

Please report security issues via [eriktonon.com](https://eriktonon.com) or by opening a security advisory on GitHub.
