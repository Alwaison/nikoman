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

# Mutation testing (run after every new feature or fix)
make mutate                       # full mutation run (Domain + Application)
make mutate-diff                  # mutate only files changed vs main (fastest during development)
make mutate-filter f=Member       # mutate only files matching "Member"
```

## Architecture

The project follows clean architecture with strict layer separation:

```
src/app/
├── Domain/              # Pure PHP — no framework dependencies
│   ├── Shared/
│   │   └── ValueObjects/    # e.g. PaginatedResult<T>
│   ├── Calendar/
│   │   ├── Entities/
│   │   ├── Repositories/    # interfaces only
│   │   └── ValueObjects/    # e.g. Mood enum
│   ├── Team/
│   └── Member/
│       ├── Entities/
│       ├── Exceptions/      # pure PHP RuntimeExceptions
│       ├── Repositories/    # interfaces only
│       └── ValueObjects/
├── Application/         # Use cases — orchestrates domain objects
│   ├── Calendar/
│   │   ├── Commands/    # writes (CreateMoodEntry, etc.)
│   │   └── Queries/     # reads (GetTeamCalendar, etc.)
│   ├── Team/
│   └── Member/
│       ├── Commands/    # writes (CreateMember, UpdateMember, DeleteMember)
│       └── Queries/     # reads (GetMember, ListMembers)
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
- **Application handlers** use `CarbonImmutable::now()` instead of `new DateTimeImmutable()` so Laravel's `$this->travel()` helper can control time in tests.
- **Controllers** are thin: parse request → call one Application service → return one Resource.
- **Eloquent models** set `public $timestamps = false` and declare explicit datetime `$casts`; the domain layer controls timestamps, Eloquent never overwrites them. All columns including `created_at`/`updated_at` must be in `$fillable`.
- **Repository methods** must start queries with `Model::query()` rather than calling static methods directly (`Model::find()`, `Model::orderBy()`, …) so PHPStan can resolve the Builder return type without relying on Larastan's mixin coverage.
- **Domain exceptions** extend `\RuntimeException` (no Illuminate imports) and are registered in `bootstrap/app.php` via `$exceptions->render()`. `UniqueConstraintViolationException` from the DB layer must be caught in the repository's `save()` and rethrown as a domain exception — never let it bubble as a 500.
- **Single resources** (`JsonResource`) set `public static $wrap = null`. **Collection resources** return `{ data: [...], meta: { pagination } }` via a dedicated `*CollectionResource` class.
- All new files must have `declare(strict_types=1)` (enforced by Pint).

## TDD workflow

Red → Green → Refactor → Mutate:

1. Write a failing test in `tests/Unit/Domain/` or `tests/Feature/Api/V1/`.
2. Run `make test-unit` (or `make test-filter f=YourTest`) — confirm red.
3. Write the minimum code to pass.
4. Run `make quality` to pass lint + static analysis.
5. Refactor if needed, keeping tests green.
6. Run `make mutate-diff` — mutates only the files changed on this branch vs `main`, so it's fast. Kill escaping mutants by sharpening assertions. After the feature or fix is merged, run `make mutate` to verify the full suite.

**Unit tests** (`tests/Unit/`) extend `PHPUnit\Framework\TestCase` directly — no Laravel bootstrap, no DB.  
**Feature tests** (`tests/Feature/`) extend `Tests\TestCase` and use `RefreshDatabase` — DB required.  
**Integration tests** (`tests/Integration/`) test repository implementations directly against the real PostgreSQL DB; extend `Tests\TestCase` and use `RefreshDatabase`. Use them to verify DB-level constraints (e.g. unique violations) that cannot be triggered through the HTTP layer.

**Every endpoint must have all three test layers.** For each new endpoint create:
- Unit test for the domain entity (`tests/Unit/Domain/`)
- Unit test for the application handler with mocked repository (`tests/Unit/Application/`)
- Feature test for the HTTP contract (`tests/Feature/Api/V1/`)
- Integration test for the repository implementation (`tests/Integration/Repositories/`) — covers persistence, upsert, nullable fields, and any DB-level constraints

For time-dependent tests use `$this->travel(N)->second()` — never `sleep()`.  
To simulate race conditions, insert rows directly with `DB::table()->insert()` to bypass FormRequest validation and exercise the repository's DB-constraint path.

## Domain model

| Entity       | Description                                              |
|--------------|----------------------------------------------------------|
| `Team`       | A group of people sharing a Niko-Niko calendar.          |
| `Member`     | A user who belongs to one or more teams.                 |
| `MoodEntry`  | One mood record per member per day (happy/neutral/sad).  |
| `Mood`       | Value object / enum — the three possible mood values.    |

## API conventions

- All routes prefixed `/api/v1/`.
- **Single-entity** responses are unwrapped (no `data` key). **Collection** responses use `{ data: [...], meta: { total, per_page, current_page, last_page } }`.
- HTTP status codes: 200 OK, 201 Created, 204 No Content, 422 Unprocessable Entity, 404 Not Found.
- Validation errors return 422 with `{ message, errors: { field: [messages] } }`. Domain exceptions that represent constraint violations (e.g. duplicate email) must render with the same shape so clients handle them identically.
- **Emails** are always normalized to lowercase before validation and storage. Use `prepareForValidation()` in FormRequests — not in handlers or controllers.
- **Route parameters** that accept UUIDs must use `->whereUuid('param')` so non-UUID values return 404 without reaching the handler or the database.

## Database conventions

- **Timestamps** are stored as `timestamp(0)` (second precision). Always add a secondary `orderBy('id')` tiebreaker when ordering by `created_at` to guarantee stable pagination when rows share the same second.
- **Pagination** defaults: `page=1`, `per_page=15`, max `per_page=100`. Use `PaginatedResult<T>` from `Domain/Shared/ValueObjects/` as the return type for paginated repository methods.
- **Concurrent indexes** — migrations that create or drop indexes must use `CONCURRENTLY` and set `public $withinTransaction = false` (untyped, to match the untyped parent property) to avoid blocking writes during deployment.
- **ILIKE searches** — escape user input with `addcslashes($value, '%_\\')` before interpolating into a LIKE/ILIKE pattern. Use a GIN trigram index (`gin_trgm_ops`, requires `pg_trgm` extension) for full partial-match performance on large tables.
- **Index documentation** — state what query each index covers in the migration commit message.

## Docker networking

| Service | Internal address | External port |
|---------|-----------------|---------------|
| nginx   | port 80         | `APP_PORT` (default 8080) |
| app     | app:9000        | not exposed   |
| db      | db:5432         | `DB_EXTERNAL_PORT` (default 5432) |

The test database `nikoman_test` is created automatically on first PostgreSQL startup via `docker/postgres/init-test-db.sh`.
