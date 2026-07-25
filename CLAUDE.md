# CLAUDE.md

Guidance for Claude Code and the agent system when working in this repository.

## Language policy

- **Everything Claude-facing is English only:** this file, `CODEMAP.md`, every `src/<Module>/CLAUDE.md`, and all `.claude/agents/*.md`. Code, identifiers, PHPDoc, commit messages, and agent reports are English too.
- **Project documentation is bilingual and lives inside the repository:** `README.md` and everything under `docs/` are written in English **and** duplicated in Russian (a parallel `*.ru.md` file next to each English document). `agent-docs` maintains both in sync. Never write documentation outside the project tree.

## Project

**API Platform + Symfony Modular Skeleton** — a production-ready starter template for modular REST API backends. It is not a finished application but a skeleton: configured infrastructure (Docker/FrankenPHP/PostgreSQL/Mercure) plus one demonstration module `src/Example/`, which is deleted in a real project and replaced with your own.

New services are bootstrapped from this skeleton. The agent system exists to drive development of new modules and features in this repository (and repositories derived from it) under one set of team conventions.

## Stack

- **PHP 8.4** (strict types), **Symfony 7.3**, **API Platform 4.1** (REST/JSON + OpenAPI)
- **Doctrine ORM 3.5** + PostgreSQL 16
- **Web server:** FrankenPHP (Caddy, worker mode in prod) + **Mercure** (real-time)
- **Auth:** Symfony Security component (pre-installed but not wired to a concrete provider — authentication is chosen per project). Roles/ownership are expressed via API Platform operation-level `security` + `config/packages/security.php`.
- **Tests:** **PHPUnit** via `vendor/bin/simple-phpunit` (`symfony/phpunit-bridge`) · **Static:** PHPStan level 6 · **Lint:** php-cs-fixer ^3 (`@Symfony`) + Rector ^1
- **Runtime:** dependencies and migrations are applied automatically by the entrypoint script on container start.

### What the skeleton does NOT have (important — do not assume it exists)

The packages below are **not installed**. If a feature needs one, it is a new dependency that `agent-architect` must explicitly justify and add to the plan (`composer require`), never treat as already present:

- **Symfony Messenger** (no async processing out of the box)
- **Symfony Workflow** (no state machine; statuses are plain named methods on the entity)
- **JWT** (LexikJWT/refresh tokens), Gedmo Extensions (SoftDeleteable/Timestampable), Flysystem/S3, Symfony Mailer/Telegram notifier, Symfony Scheduler

## Modular architecture

Code is split into independent modules under `src/`. Each module is self-contained — entities, repositories, API layer, services, configuration.

```
src/
├── Example/    # Demonstration module (delete in a real project)
│   ├── Entity/
│   ├── Repository/
│   ├── Service/
│   ├── ApiPlatform/
│   ├── di.php
│   └── api_platform.php
└── Kernel.php
```

Every substantive module must have its own **`src/<Module>/CLAUDE.md`** with the details (see `src/Example/CLAUDE.md` as the reference/template).

### Module config files (auto-loading)

`config/services.php` automatically imports, from **each** module, the files `di`, `doctrine`, `api_platform` in `.php/.xml/.yaml/.yml`, plus their env variants `{name}_{env}` (e.g. `di_test.php`). The skeleton uses **PHP** config.

| File                          | Purpose                                        |
|-------------------------------|------------------------------------------------|
| `src/<Module>/di.php`        | Module service registration (autowire/autoconfigure, exclude `Entity/`) |
| `src/<Module>/doctrine.php`  | Module ORM mapping (when an explicit one is needed) |
| `src/<Module>/api_platform.php` | Resource mapping paths for the module          |
| `src/<Module>/*_test.php`    | Overrides in the test environment              |

> **Only `di` / `doctrine` / `api_platform` are auto-loaded per module.** Security, routing, and everything else are global (`config/packages/`, `config/routes/`). There is no per-module `messenger.php`/`workflow.php`/`security.php` (unlike our larger projects — those subsystems do not exist here).

## Navigation (where to look)

- **`CODEMAP.md`** (root) — feature → where to look; first entry point
- **`docs/ARCHITECTURE.md`** — modular architecture
- **`docs/BEST_PRACTICES.md`** — coding standards, examples
- **`docs/MODULE_DEVELOPMENT.md`** — how to create a module
- **`docs/GETTING_STARTED.md`** — running the project
- **`docs/specs/<slug>/spec.md`** + **`plan.md`** — task artifacts (created by agents)
- **`src/<Module>/CLAUDE.md`** — specifics of a concrete module

## Architecture — core rules

### Where logic lives

- **State Processor** (`src/<Module>/ApiPlatform/State/Processor/`) — entry point for HTTP requests with business logic. Takes Input/entity, calls a Service, persists, returns the result.
- **State Provider** (`src/<Module>/ApiPlatform/State/Provider/`) — custom reads (filtering, pagination, computed fields).
- **Service** (`src/<Module>/Service/`) — domain logic not tied to the HTTP layer. Processors are thin orchestrators over services.
- **Controllers** — only for atypical non-API-Platform endpoints. Do not put domain logic there.
- For simple CRUD with no domain logic, the default API Platform operations on the entity + Doctrine are enough (as in `Example`) — do not add a processor without need.

### Inter-module communication

Modules communicate via **events** (EventDispatcher + `#[AsEventListener]`), not via direct cross-module service dependencies. The allowed exception is referencing another module's shared entity (e.g. an `author`).

### Entities

- **Mutations with domain meaning go through named methods** (`publish()`, `approve()`, `rename()`), not bare setters. Plain data-holder fields of a starter stub may have setters (see `Example` — a generated stub).
- **Primary keys:** the demo module `Example` uses `int IDENTITY` (auto-increment). For new modules, **UUID v7** (`Symfony\Component\Uid\UuidV7`, generated in the application constructor — `symfony/uid` is installed) is preferred where an unguessable/distributed identifier is needed; the choice is recorded by the architect in the plan.
- `DateTimeImmutable` for timestamp fields (`Types::DATETIME_IMMUTABLE`), UTC.
- Validation via `Symfony\Component\Validator\Constraints` attributes on fields/DTOs.

## Authorization

- Symfony Security is pre-installed, but the provider/firewall for a concrete project is not wired — the concrete authentication model is chosen when bootstrapping a project from the skeleton.
- **Access rules** are set two ways: an operation-level `security` expression directly on the API Platform operation (`#[Get(security: "...")]`) and/or `access_control` in `config/packages/security.php`.
- **Ownership** — an expression like `object.getOwner() == user` on a mutating operation. A security check may return **403 or 404** — in tests assert `assertContains($code, [403, 404])`.

## Conventions

- `declare(strict_types=1);` in every PHP file; full type hints on arguments and return types.
- **PHP 8.4:** native enums, attributes, `readonly`, constructor property promotion, `match`, named args, union types. Use them all.
- `final readonly class` for services, processors, providers, value objects. Entities are **not** `final` (Doctrine proxies).
- **API Platform Input DTO** (`src/<Module>/ApiPlatform/Input/*.php`) — a `final readonly class` with promoted private properties (validation attributes and `#[Groups]` on them), public getters; the Serializer denormalizes through the constructor.
- PHPDoc **only** for types PHP cannot express: `@var array<string, mixed>`, `@return list<string>`, `@extends ServiceEntityRepository<Example>`.
- Intent-revealing method names: `publish()`, `approve()`, `rename()` — not `process()`/`handle()`/`doWork()`.
- PSR-12 / `@Symfony` formatting. No descriptive comments — the code is self-documenting.
- **Data handed off somewhere (a JSON payload, a Mercure update, a log line) is a typed DTO (`final readonly class`), not a bare associative array.** Serialize via `Symfony\Component\Serializer\SerializerInterface::serialize()`, not hand-rolled `json_encode()`.

## Database

- **ORM mapping is global and automatic:** `config/packages/doctrine.php` maps `App` → `src` (attribute driver), so any `#[ORM\Entity]` under `src/<Module>/Entity` is registered — a new module needs no Doctrine config for its entities. (A module adds its own `doctrine.php` only for special cases: custom types, an explicit non-standard mapping.)
- PostgreSQL 16, UTC. `snake_case` tables/columns.
- `DateTimeImmutable` for timestamp fields.
- Indexes on columns in `WHERE`/`ORDER BY`, on FKs. Unique indexes where uniqueness is an invariant.
- All schema changes go through **Doctrine migrations** in `migrations/`. Generate with `bin/console doctrine:migrations:diff`. **Agents do not run migrations** (`migrate`) — they create the file and hand it off for review (in prod the entrypoint applies migrations on deploy).
- Doctrine 3: `$em->getRepository()` is deprecated — inject the repository via DI.

## Commands

```bash
# Tests
docker compose exec -T php vendor/bin/simple-phpunit
docker compose exec -T php vendor/bin/simple-phpunit tests/Integration/Example/ExampleTest.php
docker compose exec -T php vendor/bin/simple-phpunit --filter testName

# Static analysis (level 6; analyses tests/ too — on a fresh checkout run
# `vendor/bin/simple-phpunit install` once first so PHPUnit classes are present)
docker compose exec -T php vendor/bin/phpstan analyse

# Lint (agents run dry-run; fix only on request)
docker compose exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff
docker compose exec -T php vendor/bin/rector process --dry-run

# Changed files only (agent loop)
FILES=$(git diff --name-only HEAD -- '*.php' | tr '\n' ' ')
[ -n "$FILES" ] && docker compose exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff $FILES || true

# Migrations (generate only — migrate is run by the entrypoint/user)
docker compose exec -T php bin/console doctrine:migrations:diff
docker compose exec -T php bin/console doctrine:schema:validate

# Debug / cache
docker compose exec -T php bin/console cache:clear
docker compose exec -T php bin/console debug:router
```

## Git workflow

- Branch from `main` as `<type>/<slug>` (kebab-case slug). Types: `feat`, `fix`, `refactor`, `perf`, `docs`, `test`, `build`, `ci`, `chore`, `revert`.
- Commit messages follow **Conventional Commits**: `<type>(<scope>): <subject>` (e.g. `feat(example): add status endpoint`). Full rules — `CONTRIBUTING.md`.
- Commit/push only when the user asks.
- **Remote-agnostic.** This is a template: a derived repo may have a GitHub remote, a GitLab remote, or none. Do not hardcode a GitLab board/labels. If a remote exists and the user asks, open a PR (`gh` for GitHub); otherwise leave the branch local and hand off the diff + summary.
- Before handoff: PHPStan clean, cs-fixer clean, tests green, migration generated (if the schema changed), documentation synced (both language copies).

## Specification Driven Development

Before implementing a new feature — spec first, then code.

1. Find/create `docs/specs/<slug>/spec.md`
2. Clarify acceptance criteria
3. Write a test for the behavior from the spec
4. Implement code until the test passes
5. Update the spec if anything changed

## Agent system (`.claude/agents/`)

A multi-agent cycle drives a task from statement to a verified result:

`orchestrator` → `spec` → `architect` → (`architecture-critic` on a high-stakes plan) → `coder` → parallel review (`tester` + `mr-reviewer` + `quality-reviewer`, plus `database`/`security` per the plan flag) → (`redteam` at the top of the risk scale) → `validator` (the only one that issues PASS/FAIL/BLOCKED) → `docs` (after PASS, non-blocking).

- `spec`/`architect` save artifacts to `docs/specs/<slug>/spec.md` and `plan.md`.
- `architecture-critic` and `redteam` are **fresh adversarial instances**, used only on high-stakes work (ownership/authorization, data integrity, mutation idempotency, schema changes). They do not issue PASS/FAIL.
- `coder` is the only one that changes production code; `tester` writes PHPUnit tests.
- `logs` investigates errors from stack traces/logs.
- `docs` is the final step after `Status: PASS`: it reconciles the change and updates the **repository's own documentation** (`README.md` + `docs/`, both English and the Russian duplicate) the open-source way — editing in place, not keeping a changelog. Purely technical changes with no documentation delta are skipped. It does not block handoff.

Each agent's model/effort is set in its frontmatter. All agent files and reports are in English; project documentation is bilingual (English + Russian) per the Language policy above.
