# Hardened LAMP Stack — Docker Compose v2 & Build-to-Manage Reference (`eriktonon/lamp-docker`)

[![CI/CD Gate](https://github.com/eriktonon/lamp-docker/actions/workflows/ci-cd.yml/badge.svg)](https://github.com/eriktonon/lamp-docker/actions/workflows/ci-cd.yml)
[![Docker Hub PHP Pulls](https://img.shields.io/badge/Docker%20Hub%20%7C%20eriktonon%2Fphp-147k%2B%20pulls-0db7ed?logo=docker&logoColor=white)](https://hub.docker.com/r/eriktonon/php)
[![Security Gate](https://img.shields.io/badge/Security%20Gate-Strix%20SAST%200%20Vulns-198038?logo=securityscorecard&logoColor=white)](./SECURITY.md)
[![Build to Manage](https://img.shields.io/badge/SRE-Build%20to%20Manage%20v2.0-0f62fe)](https://github.com/eriktonon/build-to-manage)
[![License: MIT](https://img.shields.io/badge/License-MIT-0f62fe.svg)](./LICENSE)

Production-aligned **LAMP (Linux, Apache 2.4, MySQL 8.4 LTS, PHP 8.4)** development and microservice testing environment orchestrated with **Docker Compose v2**, powered by the hardened [`eriktonon/*`](https://github.com/eriktonon/docker-images) container catalog (**283,000+ pulls on Docker Hub**).

Designed under **IBM Build-to-Manage (BTM)** and **Shift-Left AppSec** principles: non-root runtime execution, loopback-isolated ports (`127.0.0.1`), deterministic L1–L3 readiness probes (`/health.php`), W3C `traceparent` propagation, and OPcache JIT compilation pre-configured for **Brazilian Enterprise Timezones (`America/Sao_Paulo`)** and **`pt_BR.UTF-8`**.

---

## Container Stack & Image Matrix

| Service | Container Image | Upstream Base | Runtime User | Port Binding (Loopback) | Readiness Probe |
|---|---|---|---|---|---|
| **`apache2`** | [`eriktonon/php:8.4-apache-bookworm`](https://hub.docker.com/r/eriktonon/php) | `php:8.4.5-apache-bookworm` | `www-data` (non-root) | `127.0.0.1:8080 -> 80` | `curl -fsS http://127.0.0.1:80/health.php` |
| **`mysql`** | [`eriktonon/mysql:8.4-lts`](https://hub.docker.com/r/eriktonon/mysql) | `mysql:8.4.4` | `mysql` (non-root) | `127.0.0.1:3306 -> 3306` | `mysqladmin ping -h 127.0.0.1 --silent` |
| **`phpmyadmin`** *(profile: `tools`)* | [`eriktonon/phpmyadmin:5.2`](https://hub.docker.com/r/eriktonon/phpmyadmin) | `phpmyadmin:5.2.2-apache` | `www-data` (non-root) | `127.0.0.1:8081 -> 80` | `curl -fsS http://127.0.0.1:80/` |

---

## Architecture & Build-to-Manage Controls

1. **Shift-Left Operability & L1–L3 Synthetic Probes**:
   - **L1 (Process)**: Apache 2.4 worker liveness with `ServerTokens Prod` and `TraceEnable Off`.
   - **L2 (Runtime)**: PHP 8.4.5 with OPcache JIT (`opcache.jit = 1255`, `64M` buffer), `pdo_mysql`, `intl`, `bcmath`, `gd`, `zip`, and Composer `2.8.6`.
   - **L3 (Database Dependency)**: Live PDO connectivity and query latency probe (`SELECT VERSION()`) against MySQL 8.4 LTS (`utf8mb4` / `utf8mb4_0900_ai_ci`) exposed at `/health.php` with `W3C TraceContext` (`traceparent`) header injection.
2. **Hardened Security Baseline (`OWASP Top 10` & `CIS Docker Benchmark`)**:
   - Zero `phpinfo()` exposure; hardened `.config/php/php.ini` (`expose_php = Off`, `display_errors = Off`, `session.cookie_httponly = 1`, `session.use_strict_mode = 1`, `disable_functions` enforced).
   - Hardened `.config/vhosts/default.conf` (`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Options -Indexes`).
   - `security_opt: ["no-new-privileges:true"]`, read-only configuration mounts (`:ro`), and explicit CPU/Memory resource quotas.

---

## Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/eriktonon/lamp-docker.git
cd lamp-docker

# 2. Initialize environment configuration
cp -n .env.example .env

# 3. Start the hardened LAMP stack (Apache/PHP 8.4 + MySQL 8.4 LTS)
make up
# or: docker compose up -d

# 4. (Optional) Start with phpMyAdmin 5.2 enabled
make up-tools
# or: docker compose --profile tools up -d
```

### Verification & Telemetry Endpoints

- **IBM Carbon v11 Readiness Dashboard**: `http://127.0.0.1:8080/`
- **Synthetic JSON Health Probe (L1–L3)**: `http://127.0.0.1:8080/health.php` (or `curl -fsS http://127.0.0.1:8080/health.php`)
- **phpMyAdmin 5.2** *(when `--profile tools` is active)*: `http://127.0.0.1:8081/`

---

## Build-to-Manage Operational Runbook

| Scenario | Symptom | Verification Command | Remediation |
|---|---|---|---|
| **L3 Database Unreachable** | `/health.php` returns HTTP `503` (`L3_database.status = DOWN`) | `docker compose ps mysql && docker compose logs --tail=50 mysql` | Verify `.env` credentials match existing volume state in `./data/mysql` or re-initialize volume. |
| **Port Conflict on `8080` or `3306`** | Container fails to bind `127.0.0.1:8080` | `docker compose config` | Override `HTTP_PORT` or `MYSQL_PORT` in `.env` and run `make up`. |

---

## Author & Governance

**Erik Tonon** — Head of Platform Engineering & GenAI @ IBM Consulting | Principal SRE & Build-to-Manage Advisor  
- Website: [eriktonon.com](https://eriktonon.com)  
- LinkedIn: [linkedin.com/in/eriktonon](https://www.linkedin.com/in/eriktonon/)  
- Docker Hub: [hub.docker.com/u/eriktonon](https://hub.docker.com/repositories/eriktonon)
