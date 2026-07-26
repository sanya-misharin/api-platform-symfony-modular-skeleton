[English](CONTRIBUTING.md) · [Русский](CONTRIBUTING.ru.md)

# Contributing

Thanks for contributing to the **API Platform + Symfony Modular Skeleton**. This document covers branches, commits, pull requests, issues, and the checks to run before opening a PR. For running the project see [docs/GETTING_STARTED.md](docs/GETTING_STARTED.md); for coding standards see [docs/BEST_PRACTICES.md](docs/BEST_PRACTICES.md).

## Branches

Branch off `main`. Name branches `<type>/<slug>`, where `<slug>` is kebab-case:

```
feat/status-endpoint      fix/null-owner        refactor/example-provider
docs/module-guide         test/reaction-limit   chore/bump-deps
perf/feed-query           ci/cache-vendor       build/frankenphp-image
```

Allowed types: `feat`, `fix`, `refactor`, `perf`, `docs`, `test`, `build`, `ci`, `chore`, `revert`.

## Parallel tasks (git worktree)

Working on two branches at once? Give each one a **git worktree** instead of switching branches in a single checkout — uncommitted work and the current branch stay separate, and both stacks can run side by side on one machine.

```bash
git worktree add ../skeleton-status-endpoint -b feat/status-endpoint
cd ../skeleton-status-endpoint
```

The stack is not started for you. The database port is the only hardcoded one (`5432:5432` in `compose.override.yaml`), so `compose.worktree.yaml` re-exposes it as `DB_PORT`; the HTTP ports are already parametrized. Pick free ports (`docker compose ls`, `ss -tlnp` — nothing is allocated automatically) and start:

```bash
HTTP_PORT=8080 HTTPS_PORT=8443 HTTP3_PORT=8443 DB_PORT=55432 \
  docker compose -f compose.yaml -f compose.override.yaml -f compose.worktree.yaml up -d --build

# or, identically:
HTTP_PORT=8080 HTTPS_PORT=8443 HTTP3_PORT=8443 DB_PORT=55432 make up-worktree
```

The compose project name defaults to the worktree's directory name, so containers and volumes are isolated from the main checkout's — every later command from that directory (`docker compose exec -T php …`, `make test`) needs no extra flags. Each worktree has its own database volume and its own `vendor/`, installed by the entrypoint on the first start (a couple of minutes; it is deliberately not shared, a symlink to the main checkout's `vendor/` would dangle inside the container).

Tearing down — `vendor/` and the contents of `var/` are created by root inside the container, so clear them from there first (`var/` itself is an anonymous volume mount and cannot be removed from inside):

```bash
docker compose exec -T php sh -c 'rm -rf vendor var/*'
docker compose down -v
git worktree remove ../skeleton-status-endpoint
```

> Using the agent pipeline? `claude --worktree <slug>` creates the worktree under `.claude/worktrees/<slug>/` (gitignored) and runs the session there; the stack steps above are the same.

## Commits

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<optional scope>)<optional !>: <subject>
```

- **type** — one of the branch types above.
- **scope** — the affected module in lower case (e.g. `example`, `core`), optional.
- **`!`** — marks a breaking change (or add a `BREAKING CHANGE:` footer).
- **subject** — imperative mood, no trailing period, ≤ 72 chars.

```
feat(example): add status endpoint
fix(core): return 404 for a non-owner instead of 500
refactor!: drop the legacy state provider
docs: document the module CLAUDE.md template
```

### Enable the local hook

A `commit-msg` hook validates the format. Enable it once per clone:

```bash
git config core.hooksPath .githooks
```

The same rules are enforced on pull requests by the **Conventions** GitHub Action (branch name + commit messages).

## Before you open a PR

Run inside the container:

```bash
docker compose exec -T php vendor/bin/phpstan analyse                 # static analysis, level 8
docker compose exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff   # @Symfony code style
docker compose exec -T php vendor/bin/deptrac analyse                 # module boundaries
docker compose exec -T php vendor/bin/simple-phpunit                  # tests
```

If the schema changed, generate a migration (do **not** apply it — the container entrypoint runs `migrate` on start):

```bash
docker compose exec -T php bin/console doctrine:migrations:diff
```

## Pull requests

- Open the PR against `main`; the title follows Conventional Commits.
- Fill in the [PR template](.github/PULL_REQUEST_TEMPLATE.md) and link the issue with `Closes #<n>`.
- Keep the PR focused — one logical change.
- Green checks: the **Quality** workflow (PHPStan, php-cs-fixer, Deptrac, PHPUnit against a Postgres service) and the **Conventions** workflow (branch name + commit messages), both run on every PR.

## Issues

Use the templates — [bug report](.github/ISSUE_TEMPLATE/bug_report.md) or [feature request](.github/ISSUE_TEMPLATE/feature_request.md). For a feature, state testable acceptance criteria. Note the skeleton ships **without** Messenger, Workflow, JWT, Gedmo, or S3 — a feature needing one of those introduces a new dependency, so call it out.

## Documentation & language policy

- **Project documentation is bilingual and lives inside the repository.** `README.md`, everything under `docs/`, and this file are written in English **and** duplicated in Russian as a parallel `*.ru.md` file. Keep both copies in sync in the same PR.
- **Claude-facing files are English only:** `CLAUDE.md`, `CODEMAP.md`, every `src/<Module>/CLAUDE.md`, and `.claude/agents/*.md`. Code, identifiers, PHPDoc, and commit messages are English too.

## Module conventions (quick reference)

- Each module is self-contained under `src/<Module>/` with its own `di.php` / `api_platform.php` and a `src/<Module>/CLAUDE.md`.
- Business logic lives in a **Service**; a **State Processor** is the thin HTTP entry point; custom reads go in a **State Provider**.
- `declare(strict_types=1);` everywhere; `final readonly class` for services/processors/providers; entities are not `final`.
- Full details: [docs/MODULE_DEVELOPMENT.md](docs/MODULE_DEVELOPMENT.md) and [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

> This repository also ships an agent pipeline under `.claude/` that drives changes through spec → plan → code → review → validation and keeps the docs in sync. Contributors are not required to use it; the conventions above apply to all changes regardless.
