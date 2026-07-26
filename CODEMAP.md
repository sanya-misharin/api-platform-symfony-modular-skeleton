# CODEMAP — API Platform + Symfony Modular Skeleton

A "feature → where to look" map — the first entry point before a task. Per-module details live in each `src/<Module>/CLAUDE.md`; this file does not duplicate them but links them and gives a quick index over the codebase.

> The skeleton currently ships one demonstration module. Grow this map as real modules and features appear — do not try to write everything up front.

## Entry points and loading

- **HTTP entry:** `public/index.php` → `src/Kernel.php` (FrankenPHP).
- **API routing:** `config/routes/api_platform.php` (all API Platform resources); other routing — `config/routes/`, `config/routes.php`.
- **Module DI/auto-loading:** `config/services.php` — imports, from **each** `src/**/`, the files `di`, `doctrine`, `api_platform` (`.php/.xml/.yaml/.yml`) plus env variants `{name}_{env}`. The skeleton uses PHP config.
- **Bundles:** `config/bundles.php`. **Packages (global config):** `config/packages/*.php` (doctrine, api_platform, security, mercure, nelmio_cors, monolog, framework, …).

## Module layers (which does what)

| What | Where | When |
|------|-------|------|
| REST resource + operations | `src/<Module>/Entity/*.php` (`#[ApiResource]`) | always — the API declaration point |
| Write business logic | `src/<Module>/ApiPlatform/State/Processor/` | when a write needs logic beyond CRUD |
| Custom reads | `src/<Module>/ApiPlatform/State/Provider/` | filtering/pagination/computed fields |
| Domain logic | `src/<Module>/Service/` | logic not tied to HTTP |
| Input DTO | `src/<Module>/ApiPlatform/Input/` | when the request shape ≠ the entity |
| Data access | `src/<Module>/Repository/` | queries; no business logic |
| Module config | `src/<Module>/{di,doctrine,api_platform}.php` | service registration/mapping/resources |

For simple CRUD with no domain logic, `#[ApiResource]` on the entity + Doctrine is enough — no processor needed (see `Example`).

## Authorization

- **Global rules:** `config/packages/security.php` (`access_control`, firewalls, providers — per project).
- **Per-operation:** a `security` expression on the API Platform operation (`#[Get(security: "...")]`, `#[Post(securityPostDenormalize: "...")]`).
- An ownership check may return **403 or 404** — account for this in tests.

## Data and schema

- **Entities/mapping:** `src/<Module>/Entity/` (`#[ORM\...]` attributes).
- **Migrations:** `migrations/` (generate with `doctrine:migrations:diff`; `migrate` is not run by agents).
- **Global Doctrine config:** `config/packages/doctrine.php` and `doctrine_migrations.php`.

## Real-time

- **Mercure:** `config/packages/mercure.php`; publishing updates — via API Platform (`mercure: true` on a resource) or `HubInterface`.

## Tests

- **Unit:** `tests/Unit/<Module>/`. **Integration (API):** `tests/Integration/<Module>/` (extend `WebTestCase`).
- Bootstrap: `tests/bootstrap.php`; config — `phpunit.xml.dist`. Run with `vendor/bin/simple-phpunit`.
- The test environment is overridden by `src/<Module>/*_test.php` files (env `test`).

## Feature → module

### Health check (liveness)
- **Code:** `src/Health/` — `HealthController` (`GET /health` → `200 {"status":"ok"}`), a plain unauthenticated controller outside `/api`. Module routing via `src/Health/routing.php`. Details — `src/Health/CLAUDE.md`.
- **Purpose:** liveness probe for orchestrators (k8s/Compose/LB). A keeper module (unlike `Example`).

### Example (demonstration, deleted in prod)
- **Code:** `src/Example/` — the `Example` entity (`examples` table, `int` PK, API Platform CRUD operations: `GetCollection/Get/Post/Put/Delete`), `ExampleRepository` (save/remove). No logic beyond default CRUD.
- **Purpose:** shows a minimal module and config auto-loading. When bootstrapping a real project — delete it and replace with your own modules following `docs/MODULE_DEVELOPMENT.md` + `src/Example/CLAUDE.md`.

<!-- Add new modules here as they appear:
### <Feature>
- **Code:** `src/<Module>/...` — key entities/processors/services. Details — `src/<Module>/CLAUDE.md`.
-->
