# Example Module — CLAUDE.md

Специфика модуля `src/Example/`. Глобальные правила — в корневом `CLAUDE.md`, навигация — в `CODEMAP.md`.

> **Этот модуль — демонстрационная заготовка.** Он показывает минимальную структуру модуля и автозагрузку конфигов, и **удаляется в реальном проекте**. Этот файл заодно служит **шаблоном модульного `CLAUDE.md`**: создавая новый модуль, скопируй структуру разделов ниже и заполни под свою предметную область.

## Ответственность модуля

Демонстрация: одна сущность `Example` с полным CRUD через API Platform, без доменной логики. Служит примером того, как модуль подключается к скелету (конфиги `di.yaml` / `api_platform.yaml`) и как выглядит сущность-ресурс.

## Домен-модель (`src/Example/Entity/`)

| Сущность  | Таблица    | Ключевые особенности |
|-----------|------------|----------------------|
| `Example` | `examples` | `int IDENTITY` PK (auto-increment — стартовый стаб; для новых модулей предпочтителен UUID v7, см. корневой `CLAUDE.md`); `name` (`NotBlank`, длина 3–255), `description` (nullable), `createdAt`/`updatedAt` (`DateTimeImmutable`, `updatedAt` пишется в сеттерах); голые сеттеры `setName()/setDescription()` — сгенерированный стаб, в модулях с доменным смыслом заменяй на именованные методы |

**API-ресурс:** `#[ApiResource]` прямо на `Example` с операциями `GetCollection`, `Get`, `Post`, `Put`, `Delete`, `paginationEnabled: true`. Кастомных State Processor/Provider нет — запись/чтение обслуживает дефолтная Doctrine-стейт API Platform.

## API-слой (`src/Example/ApiPlatform/`)

Пусто (`ApiPlatform/.gitkeep`). Когда появится логика на запись — сюда кладутся `State/Processor/`, чтение с фильтрацией — `State/Provider/`, форма запроса ≠ сущности — `Input/`.

## Сервисы (`src/Example/Service/`)

Пусто (`Service/.gitkeep`). Доменная логика — сюда; процессоры остаются тонкими оркестраторами поверх сервисов.

## Репозиторий (`src/Example/Repository/`)

`ExampleRepository extends ServiceEntityRepository<Example>` — только `save()`/`remove()` (persist/flush-обёртки). Запросы добавляй сюда; бизнес-логику — нет.

## Конфигурация модуля

- `di.yaml` — `App\Example\` с `autowire`/`autoconfigure`, `resource: './'`, `exclude: ['./Entity/']`. Автозагружается `config/services.php`.
- `api_platform.yaml` — регистрирует путь маппинга ресурсов: `%kernel.project_dir%/src/Example/Entity`.
- `doctrine.yaml` — отсутствует (глобального маппинга достаточно); добавляй, если модулю нужен явный ORM-конфиг.
- Тестовые оверрайды — `*_test.yaml` (пока не нужны).

## Тесты

Интеграционных тестов у модуля пока нет (`tests/` содержит только `tests/Unit/.gitkeep`). Для реального модуля: API-тесты — `tests/Integration/Example/` (расширяют `WebTestCase`), unit — `tests/Unit/Example/`. Запуск — `vendor/bin/simple-phpunit`.

## Шаблон разделов для нового модуля

Создавая `src/<Module>/CLAUDE.md`, опиши:
- **Ответственность модуля** — что он делает одним абзацем.
- **Домен-модель** — таблица сущностей с ключевыми инвариантами (что неизменяемо, что уникально, как меняется статус).
- **State Processors / Providers** — endpoint → кто может вызвать → что делает.
- **Сервисы** — назначение каждого.
- **Авторизация** — правила доступа (operation-level `security` / `access_control`), ownership.
- **Критичные gotchas** — нетривиальные грабли, которые агент иначе повторит.
