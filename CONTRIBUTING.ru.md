[English](CONTRIBUTING.md) · [Русский](CONTRIBUTING.ru.md)

# Как вносить вклад

Спасибо за вклад в **API Platform + Symfony Modular Skeleton**. Этот документ описывает ветки, коммиты, pull request'ы, issue и проверки перед открытием PR. Запуск проекта — [docs/GETTING_STARTED.md](docs/GETTING_STARTED.md); стандарты кода — [docs/BEST_PRACTICES.md](docs/BEST_PRACTICES.md).

## Ветки

Ответвляйтесь от `main`. Именуйте ветки `<type>/<slug>`, где `<slug>` — в kebab-case:

```
feat/status-endpoint      fix/null-owner        refactor/example-provider
docs/module-guide         test/reaction-limit   chore/bump-deps
perf/feed-query           ci/cache-vendor       build/frankenphp-image
```

Допустимые типы: `feat`, `fix`, `refactor`, `perf`, `docs`, `test`, `build`, `ci`, `chore`, `revert`.

## Параллельные задачи (git worktree)

Работаете над двумя ветками одновременно? Заведите на каждую **git worktree**, а не переключайте ветки в одном checkout'е — незакоммиченные правки и текущая ветка не пересекаются, и оба стека поднимаются рядом на одной машине.

```bash
git worktree add ../skeleton-status-endpoint -b feat/status-endpoint
cd ../skeleton-status-endpoint
```

Стек не поднимается автоматически. Единственный захардкоженный порт — порт БД (`5432:5432` в `compose.override.yaml`), поэтому `compose.worktree.yaml` выносит его в `DB_PORT`; HTTP-порты уже параметризованы. Выберите свободные порты (`docker compose ls`, `ss -tlnp` — автоматического выделения нет) и поднимайте:

```bash
HTTP_PORT=8080 HTTPS_PORT=8443 HTTP3_PORT=8443 DB_PORT=55432 \
  docker compose -f compose.yaml -f compose.override.yaml -f compose.worktree.yaml up -d --build

# то же самое:
HTTP_PORT=8080 HTTPS_PORT=8443 HTTP3_PORT=8443 DB_PORT=55432 make up-worktree
```

Имя compose-проекта по умолчанию берётся из имени директории worktree, поэтому контейнеры и тома изолированы от основного checkout'а — все последующие команды из этой директории (`docker compose exec -T php …`, `make test`) не требуют дополнительных флагов. У каждого worktree свой том БД и свой `vendor/`, который entrypoint ставит при первом старте (пара минут; он намеренно не расшарен — симлинк на `vendor/` основного checkout'а внутри контейнера указывал бы в никуда).

Уборка — `vendor/` и содержимое `var/` создаются root'ом внутри контейнера, поэтому сначала очистите их оттуда (сам `var/` — точка монтирования анонимного тома, изнутри его не удалить):

```bash
docker compose exec -T php sh -c 'rm -rf vendor var/*'
docker compose down -v
git worktree remove ../skeleton-status-endpoint
```

> Пользуетесь агентным пайплайном? `claude --worktree <slug>` создаёт worktree в `.claude/worktrees/<slug>/` (в gitignore) и работает в нём; шаги по стеку — те же.

## Коммиты

Используем [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<опциональный scope>)<опциональный !>: <subject>
```

- **type** — один из типов веток выше.
- **scope** — затронутый модуль в нижнем регистре (например, `example`, `core`), опционально.
- **`!`** — помечает ломающее изменение (или добавьте футер `BREAKING CHANGE:`).
- **subject** — в повелительном наклонении, без точки в конце, ≤ 72 символов.

```
feat(example): add status endpoint
fix(core): return 404 for a non-owner instead of 500
refactor!: drop the legacy state provider
docs: document the module CLAUDE.md template
```

### Включите локальный хук

Хук `commit-msg` проверяет формат. Включите его один раз на клон:

```bash
git config core.hooksPath .githooks
```

Те же правила проверяются на pull request'ах GitHub Action **Conventions** (имя ветки + сообщения коммитов).

## Перед открытием PR

Запустите внутри контейнера:

```bash
docker compose exec -T php vendor/bin/phpstan analyse                 # статанализ, level 8
docker compose exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff   # стиль @Symfony
docker compose exec -T php vendor/bin/rector process --dry-run        # идиомы PHP 8.4 / Symfony / Doctrine
docker compose exec -T php vendor/bin/deptrac analyse                 # границы модулей
docker compose exec -T php vendor/bin/simple-phpunit                  # тесты
```

Если изменилась схема — сгенерируйте миграцию (но **не** применяйте: entrypoint контейнера сам запускает `migrate` при старте):

```bash
docker compose exec -T php bin/console doctrine:migrations:diff
```

## Pull request'ы

- Открывайте PR в `main`; заголовок — в формате Conventional Commits.
- Заполните [шаблон PR](.github/PULL_REQUEST_TEMPLATE.md) и свяжите issue через `Closes #<n>`.
- Держите PR сфокусированным — одно логическое изменение.
- Зелёные проверки: workflow **Quality** (PHPStan, php-cs-fixer, Rector, Deptrac, PHPUnit против сервиса Postgres) и workflow **Conventions** (имя ветки + сообщения коммитов) — оба гоняются на каждом PR.

## Issue

Используйте шаблоны — [баг-репорт](.github/ISSUE_TEMPLATE/bug_report.md) или [запрос фичи](.github/ISSUE_TEMPLATE/feature_request.md). Для фичи укажите проверяемые acceptance criteria. Учтите: скелет поставляется **без** Messenger, Workflow, JWT, Gedmo и S3 — фича, которой нужна одна из этих подсистем, вводит новую зависимость, отметьте это явно.

## Документация и языковая политика

- **Документация проекта двуязычна и лежит внутри репозитория.** `README.md`, всё в `docs/` и этот файл написаны на английском **и** продублированы на русском в параллельном файле `*.ru.md`. Держите обе копии синхронными в одном PR.
- **Файлы «для Claude» — только на английском:** `CLAUDE.md`, `CODEMAP.md`, каждый `src/<Module>/CLAUDE.md` и `.claude/agents/*.md`. Код, идентификаторы, PHPDoc и сообщения коммитов — тоже на английском.

## Конвенции модулей (кратко)

- Каждый модуль самодостаточен под `src/<Module>/`, со своими `di.php` / `api_platform.php` и `src/<Module>/CLAUDE.md`.
- Бизнес-логика — в **Service**; **State Processor** — тонкая HTTP-точка входа; кастомное чтение — в **State Provider**.
- `declare(strict_types=1);` везде; `final readonly class` для сервисов/процессоров/провайдеров; сущности — не `final`.
- Подробности: [docs/MODULE_DEVELOPMENT.md](docs/MODULE_DEVELOPMENT.md) и [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

> В репозитории также есть агентный пайплайн в `.claude/`, который ведёт изменения по циклу spec → plan → code → review → validation и держит документацию синхронной. Использовать его необязательно; конвенции выше применяются ко всем изменениям.
