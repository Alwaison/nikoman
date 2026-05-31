# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**nikoman** is a REST API for managing Niko-Niko calendars for teams (Management 3.0). A Niko-Niko calendar tracks each team member's daily mood (happy / neutral / sad) to surface morale trends over time.

The approach is **API-First**: no web frontend, no Blade views, pure JSON responses.

## Stack

| Layer      | Technology            |
|------------|-----------------------|
| Language   | PHP 8.3               |
| Framework  | Laravel 12            |
| Web server | nginx 1.26            |
| Database   | PostgreSQL 17         |
| Runtime    | Docker (all services) |

## First-time setup

```bash
cp .env.example .env      # adjust ports/credentials if needed
make install              # creates Laravel project in src/, runs migrations
```

After that, `make up` / `make down` manages the environment.

## Common commands

```bash
make up                           # start containers
make down                         # stop containers
make bash                         # shell into PHP-FPM container
make artisan cmd="route:list"     # run any artisan command
make composer cmd="require pkg"   # run any composer command
make migrate                      # run pending migrations
make fresh                        # drop + re-migrate + seed
make test                         # full test suite
make test-filter f=TeamTest       # run tests matching a name
make logs s=app                   # tail a specific service log
```

## Project structure

```
docker/
  nginx/default.conf      nginx virtualhost (serves src/public)
  php/Dockerfile          PHP-FPM 8.3-alpine with pdo_pgsql, pcntl, opcache
  php/php.ini             PHP runtime overrides
  laravel.env.example     Template for src/.env (Docker-aware DB host, etc.)
src/                      Laravel 12 application (populated by make install)
docker-compose.yml        Defines app, nginx, db services
Makefile                  Dev workflow shortcuts
.env.example              Docker-level env vars (ports, DB credentials)
```

## Architecture conventions

- All API routes live under `/api/v1/` — version prefix is mandatory from day one.
- Use [API Resources](https://laravel.com/docs/12.x/eloquent-resources) (`app/Http/Resources/`) for all response shaping.
- Request validation goes in [Form Requests](https://laravel.com/docs/12.x/validation#form-request-validation) (`app/Http/Requests/`).
- Business logic belongs in service classes (`app/Services/`), not in controllers.
- Controllers are thin: validate → delegate to service → return resource.

## Domain model (Niko-Niko)

Core entities the API will expose:

- **Team** — a group of people sharing a calendar.
- **Member** — a user who belongs to one or more teams.
- **MoodEntry** — a single daily mood record (happy / neutral / sad) tied to a member and a date.
- **Calendar** — a read-projection that aggregates MoodEntry records for a team over a date range.

## Docker networking

The three services communicate on a private Docker network:
- `app` (PHP-FPM on port 9000) — not exposed externally.
- `nginx` (port 80 inside, `APP_PORT` outside) — only public entry point.
- `db` (PostgreSQL on port 5432 inside, `DB_EXTERNAL_PORT` outside) — the app connects to host `db`.
