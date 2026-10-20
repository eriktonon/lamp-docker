# =============================================================================
# eriktonon/lamp-docker — Build-to-Manage Operational Makefile
# =============================================================================

COMPOSE ?= docker compose

.PHONY: help up up-tools down ps logs health validate

help:
	@echo "Targets:"
	@echo "  make up        - Start LAMP stack (PHP 8.4 Apache Bookworm + MySQL 8.4 LTS)"
	@echo "  make up-tools  - Start LAMP stack including phpMyAdmin 5.2 (--profile tools)"
	@echo "  make down      - Stop and remove containers"
	@echo "  make ps        - Show container status and health probes"
	@echo "  make health    - Query L1-L3 synthetic readiness endpoint (/health.php)"
	@echo "  make validate  - Validate Docker Compose specification and security baseline"

up:
	$(COMPOSE) up -d

up-tools:
	$(COMPOSE) --profile tools up -d

down:
	$(COMPOSE) --profile tools down

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f --tail=100

health:
	curl -fsS http://127.0.0.1:8080/health.php | jq .

validate:
	$(COMPOSE) config -q
	@grep -q "expose_php = Off" .config/php/php.ini
	@grep -q "display_errors = Off" .config/php/php.ini
	@grep -q "ServerTokens Prod" .config/vhosts/default.conf
	@echo "[OK] LAMP stack configuration and security baseline verified."
