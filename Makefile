.DEFAULT_GOAL := help
.PHONY: help install up down restart bash artisan composer test migrate fresh logs ps

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

test-filter: ## Run tests matching a filter: make test-filter f=TeamTest
	docker compose exec app php artisan test --filter=$(f)
