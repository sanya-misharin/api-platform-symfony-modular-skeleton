# CLAUDE.md

Guidance for Claude Code and the agent system when working in this repository.
**Language:** communicate with the user in Russian; keep code, identifiers, and PHPDoc in English.

## Project

**API Platform + Symfony Modular Skeleton** — production-ready стартовый шаблон для модульных REST API-бэкендов. Это не готовое приложение, а «болванка»: настроенная инфраструктура (Docker/FrankenPHP/PostgreSQL/Mercure) + один демонстрационный модуль `src/Example/`, который удаляется в реальном проекте и заменяется своими.

Из этого скелета поднимают новые сервисы. Задача агентной системы — вести разработку новых модулей и фич в этом (и производных от него) репозиториях по единым конвенциям команды.

## Stack

- **PHP 8.4** (strict types), **Symfony 7.3**, **API Platform 4.1** (REST/JSON + OpenAPI)
- **Doctrine ORM 3.5** + PostgreSQL 16
- **Web-сервер:** FrankenPHP (Caddy, worker mode в проде) + **Mercure** (real-time)
- **Auth:** Symfony Security component (предустановлен, но не сконфигурирован под конкретный провайдер — авторизация задаётся per-project). Роли/ownership — через operation-level `security` API Platform + `config/packages/security.yaml`.
- **Tests:** **PHPUnit** через `vendor/bin/simple-phpunit` (`symfony/phpunit-bridge`) · **Static:** PHPStan level 6 · **Lint:** php-cs-fixer ^3 (`@Symfony`) + Rector ^1
- **Runtime:** зависимости и миграции накатываются автоматически entrypoint-скриптом при старте контейнера.

### Чего в скелете НЕТ (важно — не предполагай наличие)

Пакеты ниже **не установлены**. Если фича их требует — это новая зависимость, которую `agent-architect` обязан явно обосновать и добавить в план (composer require), а не считать существующей:

- **Symfony Messenger** (нет асинхронной обработки из коробки)
- **Symfony Workflow** (нет state-machine; статусы — обычными именованными методами сущности)
- **JWT** (LexikJWT/refresh tokens), Gedmo Extensions (SoftDeleteable/Timestampable), Flysystem/S3, Symfony Mailer/Telegram notifier, Symfony Scheduler

## Модульная архитектура

Код разбит на независимые модули в `src/`. Каждый модуль самодостаточен — сущности, репозитории, API-слой, сервисы, конфигурация.

```
src/
├── Example/    # Демонстрационный модуль (удалить в реальном проекте)
│   ├── Entity/
│   ├── Repository/
│   ├── Service/
│   ├── ApiPlatform/
│   ├── di.yaml
│   └── api_platform.yaml
└── Kernel.php
```

Каждый содержательный модуль должен иметь свой **`src/<Module>/CLAUDE.md`** с деталями (см. `src/Example/CLAUDE.md` как образец/шаблон).

### Config-файлы модуля (автозагрузка)

`config/services.php` автоматически подхватывает из **каждого** модуля файлы `di`, `doctrine`, `api_platform` в форматах `.php/.xml/.yaml/.yml`, плюс их env-варианты `{name}_{env}` (например, `di_test.yaml`). Скелет использует **YAML**.

| Файл                         | Назначение                                    |
|------------------------------|-----------------------------------------------|
| `src/<Module>/di.yaml`       | Регистрация сервисов модуля (autowire/autoconfigure, exclude `Entity/`) |
| `src/<Module>/doctrine.yaml` | ORM mapping модуля (если нужен явный)          |
| `src/<Module>/api_platform.yaml` | Пути маппинга ресурсов модуля для API Platform |
| `src/<Module>/*_test.yaml`   | Переопределения в тестовом окружении           |

> **Только `di` / `doctrine` / `api_platform` автозагружаются per-module.** Security, роутинг и прочее — глобальные (`config/packages/`, `config/routes/`). Нет per-module `messenger.php`/`workflow.php`/`security.php` (в отличие от более крупных наших проектов — здесь этих подсистем нет).

## Навигация (где что искать)

- **`CODEMAP.md`** (корень) — карта «фича → где искать», первая точка входа
- **`docs/ARCHITECTURE.md`** — модульная архитектура
- **`docs/BEST_PRACTICES.md`** — coding standards, примеры
- **`docs/MODULE_DEVELOPMENT.md`** — как создавать модуль
- **`docs/GETTING_STARTED.md`** — запуск проекта
- **`docs/specs/<slug>/spec.md`** + **`plan.md`** — артефакты задач (создаются агентами)
- **`src/<Module>/CLAUDE.md`** — специфика конкретного модуля

## Архитектура — основные правила

### Где живёт логика

- **State Processor** (`src/<Module>/ApiPlatform/State/Processor/`) — точка входа для HTTP-запросов с бизнес-логикой. Получает Input/сущность, вызывает Service, персистит, возвращает результат.
- **State Provider** (`src/<Module>/ApiPlatform/State/Provider/`) — кастомные запросы для чтения (фильтрация, пагинация, вычисляемые поля).
- **Service** (`src/<Module>/Service/`) — доменная логика, не привязанная к HTTP-слою. Процессоры — тонкие оркестраторы поверх сервисов.
- **Controllers** — только для нетипичных не-API-Platform эндпоинтов. Доменную логику туда не класть.
- Для простого CRUD, где нет доменной логики, достаточно дефолтных операций API Platform на сущности + Doctrine (как в `Example`) — не плоди процессор без необходимости.

### Межмодульная связь

Модули общаются через **события** (EventDispatcher + `#[AsEventListener]`), а не через прямые cross-module зависимости сервисов. Допустимое исключение — ссылка на shared entity другого модуля (например, `author`).

### Сущности

- **Мутации с доменным смыслом — через именованные методы** (`publish()`, `approve()`, `rename()`), а не через голые сеттеры. Простые data-holder поля стартовой заготовки могут иметь сеттеры (см. `Example` — сгенерированный стаб).
- **Первичные ключи:** демо-модуль `Example` использует `int IDENTITY` (auto-increment). Для новых модулей предпочтителен **UUID v7** (`Symfony\Component\Uid\UuidV7`, генерируется в конструкторе приложения — `symfony/uid` установлен) там, где нужен неугадываемый/распределённый идентификатор; выбор фиксируется архитектором в плане.
- `DateTimeImmutable` для timestamp-полей (`Types::DATETIME_IMMUTABLE`), UTC.
- Валидация — атрибуты `Symfony\Component\Validator\Constraints` на полях/DTO.

## Авторизация

- Symfony Security предустановлен, но провайдер/файрвол под конкретный проект не сконфигурирован — конкретную модель аутентификации выбирают при старте проекта из скелета.
- **Правила доступа** задаются двумя способами: operation-level `security` expression прямо на операции API Platform (`#[Get(security: "...")]`) и/или `access_control` в `config/packages/security.yaml`.
- **Ownership** — expression вида `object.getOwner() == user` на мутирующей операции. Security-check может вернуть **403 или 404** — в тестах проверяй `assertContains($code, [403, 404])`.

## Conventions

- `declare(strict_types=1);` в каждом PHP-файле; полные type hints на аргументах и возвратах.
- **PHP 8.4:** нативные enums, attributes, `readonly`, constructor property promotion, `match`, named args, union types. Используй всё.
- `final readonly class` для сервисов, процессоров, провайдеров, value objects. Entities — **не** `final` (Doctrine прокси).
- **API Platform Input DTO** (`src/<Module>/ApiPlatform/Input/*.php`) — `final readonly class` с промотированными приватными свойствами (валидационные атрибуты и `#[Groups]` — на них), публичные геттеры; Serializer денормализует через конструктор.
- PHPDoc **только** для типов, которые PHP не выражает: `@var array<string, mixed>`, `@return list<string>`, `@extends ServiceEntityRepository<Example>`.
- Имена методов с намерением: `publish()`, `approve()`, `rename()` — не `process()`/`handle()`/`doWork()`.
- PSR-12 / `@Symfony` форматирование. Без описательных комментариев — код самодокументирующий.
- **Данные, которые куда-то передаются (JSON-payload, Mercure-update, лог-строка) — типизированный DTO (`final readonly class`), не «голый» ассоциативный массив.** Сериализация — через `Symfony\Component\Serializer\SerializerInterface::serialize()`, не ручной `json_encode()`.

## Database

- PostgreSQL 16, UTC. `snake_case` таблицы/колонки.
- `DateTimeImmutable` для timestamp-полей.
- Индексы на поля в `WHERE`/`ORDER BY`, на FK. Уникальные индексы — там, где уникальность — инвариант.
- Все изменения схемы — через **Doctrine-миграции** в `migrations/`. Генерация: `bin/console doctrine:migrations:diff`. **Агенты миграции не запускают** (`migrate`) — создают файл и отдают на ревью (в проде миграции накатывает entrypoint при деплое).
- Doctrine 3: `$em->getRepository()` устарел — инжектируй репозиторий через DI.

## Commands

```bash
# Тесты
docker compose exec -T php vendor/bin/simple-phpunit
docker compose exec -T php vendor/bin/simple-phpunit tests/Integration/Example/ExampleTest.php
docker compose exec -T php vendor/bin/simple-phpunit --filter testName

# Статанализ (level 6)
docker compose exec -T php vendor/bin/phpstan analyse

# Линт (агенты — dry-run; fix только по запросу)
docker compose exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff
docker compose exec -T php vendor/bin/rector process --dry-run

# Только изменённые файлы (петля агента)
FILES=$(git diff --name-only HEAD -- '*.php' | tr '\n' ' ')
[ -n "$FILES" ] && docker compose exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff $FILES || true

# Миграции (только генерируем — migrate запускает entrypoint/пользователь)
docker compose exec -T php bin/console doctrine:migrations:diff
docker compose exec -T php bin/console doctrine:schema:validate

# Debug / cache
docker compose exec -T php bin/console cache:clear
docker compose exec -T php bin/console debug:router
```

## Git workflow

- Ветка от `main`: `feature/<slug>` для фич, `fix/<slug>` для багов.
- Commit/push — только когда просит пользователь.
- **Ремоут-агностично.** Это шаблон: у производного репозитория может быть GitHub-ремоут, GitLab, или его вовсе нет. Не хардкодь GitLab-борд/лейблы. Если ремоут есть и пользователь просит — открывай PR (`gh` для GitHub); иначе оставляй ветку локально и отдавай дифф + сводку.
- Перед сдачей: PHPStan чистый, cs-fixer чистый, тесты зелёные, миграция сгенерирована (если менялась схема), документация синхронизирована.

## Specification Driven Development

Перед реализацией новой фичи — сначала спека, потом код.

1. Найди/создай `docs/specs/<slug>/spec.md`
2. Уточни acceptance criteria
3. Напиши тест на поведение из спеки
4. Реализуй код до прохождения теста
5. Обнови спеку, если что-то изменилось

## Agent system (`.claude/agents/`)

Мультиагентный цикл ведёт задачу от формулировки до проверенного результата:

`orchestrator` → `spec` → `architect` → (`architecture-critic` на высокоставочном плане) → `coder` → параллельное ревью (`tester` + `mr-reviewer` + `quality-reviewer`, плюс `database`/`security` по флагу из плана) → (`redteam` на верхнем уровне риска) → `validator` (единственный выдаёт PASS/FAIL/BLOCKED) → `product-docs` (после PASS, не блокирует).

- `spec`/`architect` сохраняют артефакты в `docs/specs/<slug>/spec.md` и `plan.md`.
- `architecture-critic` и `redteam` — **свежие adversarial-инстансы**, только на высокоставочной работе (ownership/авторизация, целостность данных, идемпотентность мутаций, изменения схемы). Не выдают PASS/FAIL.
- `coder` — единственный, кто меняет прод-код; `tester` пишет PHPUnit-тесты.
- `logs` расследует ошибки по стектрейсам/логам.
- `product-docs` — опциональный финальный шаг после `Status: PASS` (путь к документу — env `PRODUCT_DOC_PATH` в `.claude/settings.local.json`; если не задан — тихо пропускается). Сдачу задачи не блокирует.

Модели/усилие каждого агента заданы в его frontmatter; общение с пользователем и отчёты — на русском, код — на английском.
