.DEFAULT_GOAL := help
.PHONY: help install up down restart bash artisan composer \
        test test-unit test-feature test-filter \
        lint lint-fix analyse quality \
        migrate fresh logs ps spec-lint

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ─── First-time setup ─────────────────────────────────────────────────────────

install: ## Bootstrap the project (run once after cloning)
	@if [ -f src/composer.json ]; then \
		echo "Project already installed. Run 'make up' instead."; exit 1; \
	fi
	@[ -f .env ] || cp .env.example .env
	@echo "→ Creating Laravel project..."
	rm -f src/.gitkeep
	docker run --rm \
		-v $(PWD)/src:/app \
		-w /app \
		composer:2 create-project laravel/laravel:^12 . --no-interaction --prefer-dist
	@echo "→ Configuring environment..."
	cp docker/laravel.env.example src/.env
	cp docker/config/pint.json src/pint.json
	cp docker/config/phpstan.neon src/phpstan.neon
	cp docker/config/phpunit.xml src/phpunit.xml
	@echo "→ Installing quality tools..."
	docker run --rm \
		-v $(PWD)/src:/app \
		-w /app \
		composer:2 remove --dev pestphp/pest pestphp/pest-plugin-laravel --no-interaction 2>/dev/null; true
	docker run --rm \
		-v $(PWD)/src:/app \
		-w /app \
		composer:2 require --dev "phpunit/phpunit:^11" "larastan/larastan" --no-interaction
	@echo "→ Scaffolding clean architecture..."
	bash docker/scripts/scaffold.sh src
	@echo "→ Starting environment..."
	@$(MAKE) up
	@echo "→ Finalising Laravel setup..."
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate
	@echo ""
	@echo "✓ Project ready at http://localhost:$${APP_PORT:-8080}"

# ─── Environment lifecycle ────────────────────────────────────────────────────

up: ## Start all containers
	docker compose up -d --build

down: ## Stop all containers
	docker compose down

restart: ## Restart all containers
	docker compose restart

ps: ## Show container status
	docker compose ps

logs: ## Tail logs — optionally filter: make logs s=app
	docker compose logs -f $(s)

# ─── Shell access ─────────────────────────────────────────────────────────────

bash: ## Shell into the app container
	docker compose exec app bash

# ─── Laravel shortcuts ────────────────────────────────────────────────────────

artisan: ## Run artisan: make artisan cmd="route:list"
	docker compose exec app php artisan $(cmd)

composer: ## Run composer: make composer cmd="require some/package"
	docker compose exec app composer $(cmd)

# ─── Database ─────────────────────────────────────────────────────────────────

migrate: ## Run pending migrations
	docker compose exec app php artisan migrate

fresh: ## Drop all tables, re-run migrations and seeders
	docker compose exec app php artisan migrate:fresh --seed

# ─── Testing (TDD) ────────────────────────────────────────────────────────────

test: ## Run the full test suite
	docker compose exec app php artisan test

test-unit: ## Run Unit tests only (no DB required)
	docker compose exec app php artisan test --testsuite=Unit

test-feature: ## Run Feature tests only
	docker compose exec app php artisan test --testsuite=Feature

test-integration: ## Run Integration tests only
	docker compose exec app php artisan test --testsuite=Integration

test-filter: ## Run tests matching a name: make test-filter f=MoodTest
	docker compose exec app php artisan test --filter=$(f)

test-coverage: ## Generate HTML coverage report (requires Xdebug)
	docker compose exec app php artisan test --coverage-html=coverage

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
