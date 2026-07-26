# Thin wrappers over the Dockerised toolchain. Run `make` (or `make help`) for the list.

DC      = docker compose
PHP     = $(DC) exec php
PHP_NX  = $(DC) exec php php -d xdebug.mode=off
CONSOLE = $(PHP_NX) bin/console

.DEFAULT_GOAL := help
.PHONY: help up down build restart logs sh install test stan cs cs-fix rector cc migration migrate validate deptrac

help: ## List available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-13s\033[0m %s\n", $$1, $$2}'

up: ## Build and start the stack (detached)
	$(DC) up -d --build

down: ## Stop and remove the stack
	$(DC) down

build: ## Rebuild the images
	$(DC) build

restart: down up ## Restart the stack

logs: ## Tail the php container logs
	$(DC) logs -f php

sh: ## Open a shell in the php container
	$(DC) exec php sh

install: ## composer install
	$(PHP) composer install

test: ## Run the test suite
	$(PHP_NX) vendor/bin/simple-phpunit

stan: ## Static analysis (PHPStan)
	$(PHP) vendor/bin/phpstan analyse

cs: ## Code-style check (dry-run)
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Apply code-style fixes
	$(PHP) vendor/bin/php-cs-fixer fix

rector: ## Rector (dry-run)
	$(PHP) vendor/bin/rector process --dry-run

cc: ## Clear the cache
	$(CONSOLE) cache:clear

migration: ## Generate a migration from the mapping diff
	$(CONSOLE) doctrine:migrations:diff

migrate: ## Apply migrations
	$(CONSOLE) doctrine:migrations:migrate

validate: ## Validate composer.json and the Doctrine schema
	$(PHP) composer validate --strict
	$(CONSOLE) doctrine:schema:validate
deptrac: ## Check module boundaries (Deptrac)
	$(PHP) vendor/bin/deptrac analyse
