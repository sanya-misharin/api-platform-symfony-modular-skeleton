# CODEMAP — API Platform + Symfony Modular Skeleton

Карта «фича → где искать» — первая точка входа перед задачей. Детали каждого модуля — в его `src/<Module>/CLAUDE.md`; этот файл их не дублирует, а связывает и даёт быстрый индекс по кодовой базе.

> Скелет пока содержит один демонстрационный модуль. Пополняй эту карту по мере появления реальных модулей и фич, а не пытайся выписать всё наперёд.

## Точки входа и загрузка

- **HTTP entry:** `public/index.php` → `src/Kernel.php` (FrankenPHP).
- **Роутинг API:** `config/routes/api_platform.yaml` (все ресурсы API Platform); прочий роутинг — `config/routes/`, `config/routes.php`.
- **DI/автозагрузка модулей:** `config/services.php` — импортирует из **каждого** `src/**/` файлы `di`, `doctrine`, `api_platform` (`.php/.xml/.yaml/.yml`) + env-варианты `{name}_{env}`. Скелет использует YAML.
- **Bundles:** `config/bundles.php`. **Пакеты (глобальный конфиг):** `config/packages/*.yaml` (doctrine, api_platform, security, mercure, nelmio_cors, monolog, framework, …).

## Слои модуля (где какая ответственность)

| Что | Где | Когда |
|-----|-----|-------|
| REST-ресурс + операции | `src/<Module>/Entity/*.php` (`#[ApiResource]`) | всегда — точка объявления API |
| Бизнес-логика записи | `src/<Module>/ApiPlatform/State/Processor/` | когда на запись нужна логика сверх CRUD |
| Кастомное чтение | `src/<Module>/ApiPlatform/State/Provider/` | фильтрация/пагинация/вычисляемые поля |
| Доменная логика | `src/<Module>/Service/` | логика, не привязанная к HTTP |
| Input DTO | `src/<Module>/ApiPlatform/Input/` | когда форма запроса ≠ сущности |
| Доступ к данным | `src/<Module>/Repository/` | запросы; без бизнес-логики |
| Конфиг модуля | `src/<Module>/{di,doctrine,api_platform}.yaml` | регистрация сервисов/маппинг/ресурсы |

Для простого CRUD без доменной логики хватает `#[ApiResource]` на сущности + Doctrine — процессор не нужен (см. `Example`).

## Авторизация

- **Глобальные правила:** `config/packages/security.yaml` (`access_control`, файрволы, провайдеры — под проект).
- **Per-operation:** `security` expression на операции API Platform (`#[Get(security: "...")]`, `#[Post(securityPostDenormalize: "...")]`).
- Ownership-check может вернуть **403 или 404** — учитывай в тестах.

## Данные и схема

- **Сущности/маппинг:** `src/<Module>/Entity/` (атрибуты `#[ORM\...]`).
- **Миграции:** `migrations/` (генерация — `doctrine:migrations:diff`; `migrate` — не агентами).
- **Глобальный Doctrine-конфиг:** `config/packages/doctrine.yaml` и `doctrine_migrations.yaml`.

## Real-time

- **Mercure:** `config/packages/mercure.yaml`; публикация апдейтов — через API Platform (`mercure: true` на ресурсе) либо `HubInterface`.

## Тесты

- **Unit:** `tests/Unit/<Module>/`. **Integration (API):** `tests/Integration/<Module>/` (расширяют `WebTestCase`).
- Bootstrap: `tests/bootstrap.php`; конфиг — `phpunit.xml.dist`. Запуск — `vendor/bin/simple-phpunit`.
- Тестовое окружение переопределяется файлами `src/<Module>/*_test.yaml` (env `test`).

## Фича → модуль

### Example (демонстрационный, удаляется в проде)
- **Код:** `src/Example/` — сущность `Example` (`examples` таблица, `int` PK, CRUD-операции API Platform: `GetCollection/Get/Post/Put/Delete`), `ExampleRepository` (save/remove). Логики сверх дефолтного CRUD нет.
- **Назначение:** показывает минимальный модуль и автозагрузку конфигов. При старте реального проекта — удалить и заменить своими модулями по образцу `docs/MODULE_DEVELOPMENT.md` + `src/Example/CLAUDE.md`.

<!-- Добавляй сюда новые модули по мере их появления:
### <Feature>
- **Код:** `src/<Module>/...` — ключевые сущности/процессоры/сервисы. Подробности — `src/<Module>/CLAUDE.md`.
-->
