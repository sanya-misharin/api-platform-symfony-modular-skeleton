# Example Module — CLAUDE.md

Specifics of the `src/Example/` module. Global rules — in the root `CLAUDE.md`; navigation — in `CODEMAP.md`.

> **This module is a demonstration stub.** It shows the minimal structure of a module and config auto-loading, and is **deleted in a real project**. This file also serves as the **module `CLAUDE.md` template**: when creating a new module, copy the section structure below and fill it in for your own domain.

## Module responsibility

Demonstration: a single `Example` entity with full CRUD via API Platform, no domain logic. Serves as an example of how a module plugs into the skeleton (the `di.yaml` / `api_platform.yaml` configs) and what a resource entity looks like.

## Domain model (`src/Example/Entity/`)

| Entity    | Table      | Key traits |
|-----------|------------|------------|
| `Example` | `examples` | `int IDENTITY` PK (auto-increment — a starter stub; new modules prefer UUID v7, see the root `CLAUDE.md`); `name` (`NotBlank`, length 3–255), `description` (nullable), `createdAt`/`updatedAt` (`DateTimeImmutable`, `updatedAt` written in setters); the bare setters `setName()/setDescription()` are a generated stub — in modules with domain meaning, replace them with named methods |

**API resource:** `#[ApiResource]` directly on `Example` with operations `GetCollection`, `Get`, `Post`, `Put`, `Delete`, `paginationEnabled: true`. No custom State Processor/Provider — writes/reads are served by API Platform's default Doctrine state.

## API layer (`src/Example/ApiPlatform/`)

Empty (`ApiPlatform/.gitkeep`). When write logic appears — `State/Processor/` goes here; reads with filtering — `State/Provider/`; a request shape ≠ the entity — `Input/`.

## Services (`src/Example/Service/`)

Empty (`Service/.gitkeep`). Domain logic goes here; processors stay thin orchestrators over services.

## Repository (`src/Example/Repository/`)

`ExampleRepository extends ServiceEntityRepository<Example>` — only `save()`/`remove()` (persist/flush wrappers). Add queries here; not business logic.

## Module configuration

- `di.yaml` — `App\Example\` with `autowire`/`autoconfigure`, `resource: './'`, `exclude: ['./Entity/']`. Auto-loaded by `config/services.php`.
- `api_platform.yaml` — registers the resource mapping path: `%kernel.project_dir%/src/Example/Entity`.
- `doctrine.yaml` — absent (the global mapping is enough); add it if a module needs an explicit ORM config.
- Test overrides — `*_test.yaml` (not needed yet).

## Tests

The module has no integration tests yet (`tests/` contains only `tests/Unit/.gitkeep`). For a real module: API tests — `tests/Integration/Example/` (extend `WebTestCase`), unit — `tests/Unit/Example/`. Run with `vendor/bin/simple-phpunit`.

## Section template for a new module

When creating `src/<Module>/CLAUDE.md`, describe:
- **Module responsibility** — what it does, in one paragraph.
- **Domain model** — a table of entities with key invariants (what is immutable, what is unique, how status changes).
- **State Processors / Providers** — endpoint → who may call it → what it does.
- **Services** — the purpose of each.
- **Authorization** — access rules (operation-level `security` / `access_control`), ownership.
- **Critical gotchas** — non-obvious pitfalls an agent would otherwise repeat.
