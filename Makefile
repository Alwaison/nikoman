.DEFAULT_GOAL := help
.PHONY: help install up down restart bash artisan composer \
        test test-unit test-feature test-filter \
        lint lint-fix analyse quality \
        migrate fresh logs ps spec-lint

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

# ─── First-time setup ────────────────────────────────────────────────────────

install: ## Bootstrap the project (run once after cloning)
	@if [ -f src/composer.json ]; then echo "Already installed. Run 'make up' instead."; exit 1; fi
	@[ -f .env ] || cp .env.example .env
	docker run --rm \
		-v $(PWD)/src:/app \
		-w /app \
		composer:2 create-project laravel/laravel:^12 . --no-interaction --prefer-dist
	cp docker/laravel.env.example src/.env
	@$(MAKE) up
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate

# ─── Environment lifecycle ───────────────────────────────────────────────────

up: ## Start all containers
	docker compose up -d --build

down: ## Stop all containers
	docker compose down

restart: ## Restart all containers
	docker compose restart

ps: ## Show container status
	docker compose ps

logs: ## Tail container logs (filter: make logs s=app)
	docker compose logs -f $(s)

# ─── Development ─────────────────────────────────────────────────────────────

bash: ## Shell into the app container
	docker compose exec app bash

artisan: ## Run an artisan command: make artisan cmd="route:list"
	docker compose exec app php artisan $(cmd)

composer: ## Run a composer command: make composer cmd="require laravel/sanctum"
	docker compose exec app composer $(cmd)

# ─── Database ────────────────────────────────────────────────────────────────

migrate: ## Run pending migrations
	docker compose exec app php artisan migrate

fresh: ## Drop all tables, re-run migrations and seeders
	docker compose exec app php artisan migrate:fresh --seed

# ─── Testing ─────────────────────────────────────────────────────────────────

test: ## Run the full test suite
	docker compose exec app php artisan test

test-unit: ## Run Unit tests only (no DB required)
	docker compose exec app php artisan test --testsuite=Unit

test-feature: ## Run Feature tests only
	docker compose exec app php artisan test --testsuite=Feature

test-filter: ## Run tests matching a filter: make test-filter f=TeamTest
	docker compose exec app php artisan test --filter=$(f)

# ─── Code quality ─────────────────────────────────────────────────────────────

lint: ## Check code style without making changes
	docker compose exec app ./vendor/bin/pint --test

lint-fix: ## Fix code style issues
	docker compose exec app ./vendor/bin/pint

analyse: ## Static analysis (Larastan level 8)
	docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M

quality: lint analyse ## Run all quality checks (lint + static analysis)

# ─── Specification ────────────────────────────────────────────────────────────

spec-lint: ## Validate the OpenAPI specification
	docker run --rm -v $(PWD)/specification:/spec -w /spec redocly/cli lint --config .redocly.yaml nikoman.yaml
