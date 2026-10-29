COMPOSE_DEV  = docker compose -f compose.yaml -f compose.override.yaml
COMPOSE_TEST = docker compose -f compose.yaml -f compose.test.yaml
COMPOSE_PROD = docker compose -f compose.yaml -f compose.prod.yaml

.PHONY: dev 
dev:
	$(COMPOSE_DEV) up -d

.PHONY: stop
stop:
	$(COMPOSE_DEV) down --remove-orphans 2>/dev/null || true
	$(COMPOSE_TEST) down --remove-orphans 2>/dev/null || true
	$(COMPOSE_PROD) down --remove-orphans 2>/dev/null || true

.PHONY: test 
test: phpunit behat

.PHONY: phpunit
phpunit:
	$(COMPOSE_TEST) run --rm --build php php bin/phpunit --configuration=tests/PHPUnit/phpunit.xml

.PHONY: behat
behat:
	$(COMPOSE_TEST) run --rm --build php vendor/bin/behat --config=tests/Behat/behat.yml

.PHONY: lint
lint: phpstan php-cs-fixer deptrac

.PHONY: phpstan
phpstan:
	$(COMPOSE_TEST) run --rm --build php vendor/bin/phpstan analyse -c lint/phpstan/phpstan.neon --no-progress

.PHONY: php-cs-fixer
php-cs-fixer:
	$(COMPOSE_TEST) run --rm --build php vendor/bin/php-cs-fixer fix --config=lint/php-cs-fixer/.php-cs-fixer.php --dry-run --diff

.PHONY: deptrac
deptrac:
	$(COMPOSE_TEST) run --rm --build php vendor/bin/deptrac analyse --config-file=lint/deptrac/deptrac.yaml --no-progress


.PHONY: database.reset
database.reset:
	$(COMPOSE_DEV) run --rm --build php php bin/console doctrine:database:drop --force --if-exists
	$(COMPOSE_DEV) run --rm --build php php bin/console doctrine:database:create
	$(COMPOSE_DEV) run --rm --build php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

.PHONY: database.migrate
database.migrate:
	$(COMPOSE_DEV) run --rm --build php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

.PHONY: database.migration.generate
database.migration.generate:
	$(COMPOSE_DEV) run --rm --build php php bin/console doctrine:migrations:diff --allow-empty-diff

.PHONY: cache.clear
cache.clear:
	$(COMPOSE_DEV) run --rm --build php php bin/console cache:clear

.PHONY: cache.warmup
cache.warmup:
	$(COMPOSE_DEV) run --rm --build php php bin/console cache:warmup

.PHONY: prod
prod:
	$(COMPOSE_PROD) up -d --build

.PHONY: dev.build
dev.build:
	$(COMPOSE_DEV) build
