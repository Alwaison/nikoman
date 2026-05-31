# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**nikoman** is a REST API for managing Niko-Niko calendars for teams (Management 3.0). A Niko-Niko calendar tracks each team member's daily mood (happy / neutral / sad) to surface morale trends over time.

API-First: no frontend, no Blade views, all responses are JSON.

## Stack

| Layer        | Technology          |
|--------------|---------------------|
| Language     | PHP 8.4             |
| Framework    | Laravel 12          |
| Web server   | nginx 1.26          |
| Database     | PostgreSQL 17       |
| Runtime      | Docker (all services)|
| Tests        | PHPUnit 11          |
| Style linter | Laravel Pint        |
| Static analysis | Larastan (PHPStan level 8) |

## First-time setup

```bash
cp .env.example .env      # adjust ports/passwords if needed
make install              # installs Laravel, quality tools, scaffolds architecture, migrates
```

After that, `make up` / `make down` is all that's needed.

## Development commands

```bash
# Environment
make up                           # start containers
make down                         # stop containers
make bash                         # shell into PHP-FPM container
make logs s=app                   # tail a specific service log

# Laravel
make artisan cmd="route:list"     # any artisan command
make composer cmd="require pkg"   # any composer command
make migrate                      # run pending migrations
make fresh                        # drop + re-migrate + seed

# Testing (TDD workflow)
make test                         # full suite
make test-unit                    # Unit only (no DB, fastest)
make test-feature                 # Feature only
make test-integration             # Integration only
make test-filter f=MoodTest       # run tests matching a name

# Quality (run before every commit)
make quality                      # lint + static analysis
make lint                         # Pint check (dry run)
make lint-fix                     # Pint auto-fix
make analyse                      # Larastan PHPStan level 8
```

## Architecture

The project follows clean architecture with strict layer separation:

```
src/app/
├── Domain/              # Pure PHP — no framework dependencies
│   ├── Calendar/
│   │   ├── Entities/
│   │   ├── Repositories/    # interfaces only
│   │   └── ValueObjects/    # e.g. Mood enum
│   ├── Team/
│   └── Member/
├── Application/         # Use cases — orchestrates domain objects
│   ├── Calendar/
│   │   ├── Commands/    # writes (CreateMoodEntry, etc.)
│   │   └── Queries/     # reads (GetTeamCalendar, etc.)
│   ├── Team/
│   └── Member/
├── Infrastructure/      # Framework-dependent adapters
│   └── Persistence/
│       ├── Models/          # Eloquent models (infra detail)
│       └── Repositories/    # Eloquent implementations of domain interfaces
└── Http/
    ├── Controllers/Api/V1/  # thin: validate → service → resource
    ├── Requests/Api/V1/     # Form Request validation
    └── Resources/Api/V1/    # API Resource response shaping
```

## OpenAPI specification

The contract lives in `specification/nikoman.yaml` (OpenAPI 3.1). It is the source of truth for every endpoint, schema, and response code.

**After every change to `specification/nikoman.yaml`:**

```bash
make spec-lint
```

The spec must pass with **0 errors and 0 warnings** before the change is committed. Fix every issue the linter reports — do not suppress warnings in `.redocly.yaml` unless the rule is globally inapplicable to the project (e.g. `no-server-example.com` for the intentional localhost dev server).

Lint rules that apply:
- Every operation that returns a single entity must include a `404` response.
- Every authenticated operation must include a `401` response.
- License must carry an SPDX `identifier`.
- All `$ref` targets must resolve.

## Architecture conventions

- **Domain** classes must not import anything from `Illuminate\` or `App\Infrastructure\`.
- **Application** services receive domain objects via constructor injection; never touch Eloquent directly.
- **Controllers** are thin: parse request → call one Application service → return one Resource.
- All new files must have `declare(strict_types=1)` (enforced by Pint).

## TDD workflow

Red → Green → Refactor:

1. Write a failing test in `tests/Unit/Domain/` or `tests/Feature/Api/V1/`.
2. Run `make test-unit` (or `make test-filter f=YourTest`) — confirm red.
3. Write the minimum code to pass.
4. Run `make quality` to pass lint + static analysis.
5. Refactor if needed, keeping tests green.

**Unit tests** (`tests/Unit/`) extend `PHPUnit\Framework\TestCase` directly — no Laravel bootstrap, no DB.  
**Feature tests** (`tests/Feature/`) extend `Tests\TestCase` and use `RefreshDatabase` — DB required.  
**Integration tests** (`tests/Integration/`) test repository implementations against the real PostgreSQL DB.

## Domain model

| Entity       | Description                                              |
|--------------|----------------------------------------------------------|
| `Team`       | A group of people sharing a Niko-Niko calendar.          |
| `Member`     | A user who belongs to one or more teams.                 |
| `MoodEntry`  | One mood record per member per day (happy/neutral/sad).  |
| `Mood`       | Value object / enum — the three possible mood values.    |

## API conventions

- All routes prefixed `/api/v1/`.
- Responses follow JSON:API-inspired structure: `{ data, meta, errors }`.
- HTTP status codes: 200 OK, 201 Created, 204 No Content, 422 Unprocessable Entity, 404 Not Found.
- Validation errors return 422 with an `errors` key listing field-level messages.

## Docker networking

| Service | Internal address | External port |
|---------|-----------------|---------------|
| nginx   | port 80         | `APP_PORT` (default 8080) |
| app     | app:9000        | not exposed   |
| db      | db:5432         | `DB_EXTERNAL_PORT` (default 5432) |

The test database `nikoman_test` is created automatically on first PostgreSQL startup via `docker/postgres/init-test-db.sh`.
