.DEFAULT_GOAL := help

BUILD        ?= --build
PROJECT      ?= $(shell basename $(CURDIR) | tr '[:upper:]' '[:lower:]')
COMPOSE_DEV  = docker compose -f compose.yaml -f compose.override.yaml
COMPOSE_TEST = docker compose -p $(PROJECT)-test -f compose.yaml -f compose.test.yaml
COMPOSE_PROD = docker compose -f compose.yaml -f compose.prod.yaml

.PHONY: help
help: ## Show this help
	@grep -hE '^[a-zA-Z_.-]+:.*## ' $(MAKEFILE_LIST) | awk -F ':.*## ' '{printf "  \033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: dev
dev: ## Start the dev stack
	$(COMPOSE_DEV) up -d

.PHONY: shell
shell: ## Open a shell in the dev php container
	$(COMPOSE_DEV) exec php sh

.PHONY: logs
logs: ## Follow the dev logs
	$(COMPOSE_DEV) logs -f

.PHONY: composer
composer: ## Run composer in the dev container, e.g. make composer ARGS="require x"
	$(COMPOSE_DEV) exec php composer $(ARGS)

.PHONY: dev.debug
dev.debug: ## Restart php with Xdebug enabled
	XDEBUG_MODE=debug,develop $(COMPOSE_DEV) up -d --force-recreate php

.PHONY: stop
stop: ## Stop every compose project
	$(COMPOSE_DEV) down --remove-orphans 2>/dev/null || true
	$(COMPOSE_TEST) down --remove-orphans 2>/dev/null || true
	$(COMPOSE_PROD) down --remove-orphans 2>/dev/null || true

.PHONY: test
test: ## Run PHPUnit, the migration check and Behat, then remove the test stack
	@status=0; $(MAKE) phpunit test.migration behat || status=$$?; $(MAKE) test.down; exit $$status

.PHONY: test.down
test.down: ## Remove the test stack
	$(COMPOSE_TEST) down --volumes --remove-orphans

.PHONY: phpunit
phpunit: ## Run PHPUnit
	$(COMPOSE_TEST) run --rm $(BUILD) php php bin/phpunit --configuration=tests/PHPUnit/phpunit.xml

.PHONY: database.test.prepare
database.test.prepare: ## Create and migrate the test database
	$(COMPOSE_TEST) run --rm $(BUILD) php sh -c 'php bin/console doctrine:database:create --if-not-exists --env=test && php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test'

.PHONY: test.migration
test.migration: ## Check migrations apply on an empty database and match the mapping
	$(COMPOSE_TEST) run --rm $(BUILD) php sh -c 'php bin/console doctrine:database:drop --force --if-exists --env=test && php bin/console doctrine:database:create --env=test && php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test && php bin/console doctrine:schema:validate --env=test'

.PHONY: behat
behat: database.test.prepare ## Run Behat
	$(COMPOSE_TEST) run --rm $(BUILD) php vendor/bin/behat --config=tests/Behat/behat.yml

.PHONY: lint
lint: ## Run PHPStan, PHP CS Fixer (dry run), Deptrac and the project rules
	@status=0; $(MAKE) phpstan php-cs-fixer deptrac lint.rules || status=$$?; $(MAKE) test.down; exit $$status

.PHONY: phpstan
phpstan: ## Run PHPStan
	$(COMPOSE_TEST) run --rm $(BUILD) php vendor/bin/phpstan analyse -c lint/phpstan/phpstan.neon --no-progress

.PHONY: php-cs-fixer
php-cs-fixer: ## Run PHP CS Fixer (dry run)
	$(COMPOSE_TEST) run --rm $(BUILD) php vendor/bin/php-cs-fixer fix --config=lint/php-cs-fixer/.php-cs-fixer.php --dry-run --diff

.PHONY: deptrac
deptrac: ## Run Deptrac
	$(COMPOSE_TEST) run --rm $(BUILD) php vendor/bin/deptrac analyse --config-file=lint/deptrac/deptrac.yaml --no-progress


.PHONY: database.reset
database.reset: ## Drop, create and migrate the dev database
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console doctrine:database:drop --force --if-exists
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console doctrine:database:create
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

.PHONY: database.migrate
database.migrate: ## Run the dev migrations
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

.PHONY: database.migration.generate
database.migration.generate: ## Generate a migration from the mapping diff
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console doctrine:migrations:diff --allow-empty-diff

.PHONY: cache.clear
cache.clear:
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console cache:clear

.PHONY: cache.warmup
cache.warmup:
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console cache:warmup

.PHONY: prod
prod: ## Build and start the production stack
	@grep -q '^APP_SECRET=dev-only-secret-change-me' .env && { echo "APP_SECRET is still the dev placeholder, set a real one in .env"; exit 1; } || true
	$(COMPOSE_PROD) up -d --build

.PHONY: prod.offsite
prod.offsite: ## Start production with the off-site backup sidecar
	@test -f docker/rclone/rclone.conf || { echo "Missing docker/rclone/rclone.conf (rclone config)"; exit 1; }
	$(COMPOSE_PROD) --profile offsite up -d --build

.PHONY: dev.build
dev.build: ## Build the dev image
	$(COMPOSE_DEV) build

.PHONY: db.backup
db.backup: ## Dump the production database to ./backups
	@mkdir -p backups
	$(COMPOSE_PROD) exec -T db sh -c 'pg_dump -U "$$POSTGRES_USER" -d "$$POSTGRES_DB" -Fc' > backups/db-$(shell date +%Y%m%d-%H%M%S).dump

.PHONY: db.restore
db.restore: ## Restore FILE=... into the production database
	@test -n "$(FILE)" || (echo "Usage: make db.restore.prod FILE=backups/db-YYYYMMDD-HHMMSS.dump"; exit 1)
	$(COMPOSE_PROD) exec -T db sh -c 'pg_restore -U "$$POSTGRES_USER" -d "$$POSTGRES_DB" --clean --if-exists' < "$(FILE)"

.PHONY: db.restore.test
db.restore.test: ## Prove a dump restores, in a throwaway database
	@file="$(or $(FILE),$(shell ls -t backups/*.dump 2>/dev/null | head -1))"; \
	test -n "$$file" || { echo "No dump found, run make db.backup or pass FILE=..."; exit 1; }; \
	status=0; \
	$(COMPOSE_TEST) up -d --wait db \
	&& $(COMPOSE_TEST) exec -T db sh -c 'pg_restore -U "$$POSTGRES_USER" -d "$$POSTGRES_DB" --no-owner' < "$$file" \
	&& $(COMPOSE_TEST) exec -T db sh -c 'psql -U "$$POSTGRES_USER" -d "$$POSTGRES_DB" -c "\dt"' \
	|| status=$$?; \
	$(MAKE) test.down; exit $$status

.PHONY: importmap.install
importmap.install:
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console importmap:install

.PHONY: importmap.update
importmap.update:
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console importmap:update

.PHONY: importmap.audit
importmap.audit:
	$(COMPOSE_DEV) run --rm $(BUILD) php php bin/console importmap:audit

.PHONY: lint.rules
lint.rules: ## Check the project rules a linter cannot express (migration docblock, translation keys, no ignores)
	$(COMPOSE_TEST) run --rm $(BUILD) php sh lint/rules/check.sh

.PHONY: infection
infection: database.test.prepare ## Run mutation testing on the Domain
	$(COMPOSE_TEST) run --rm $(BUILD) php vendor/bin/infection --configuration=lint/infection/infection.json5 --threads=max --no-progress --show-mutations
