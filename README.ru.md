[English](README.md) · [Русский](README.ru.md)

# API Platform + Symfony Modular Skeleton

🚀 Готовый к продакшену стартовый шаблон для создания модульных REST API бэкендов на **Symfony 7.4** и **API Platform 4.3**.

## ✨ Возможности

- **🎯 Модульная архитектура** — каждый модуль изолирован и имеет собственную конфигурацию DI
- **📦 API Platform** — автоматическая генерация REST API с документацией OpenAPI
- **🐘 PHP 8.4** — новейший PHP со строгой типизацией и современными возможностями
- **🗄️ PostgreSQL 16** — база данных продакшен-уровня
- **🐳 Готов к Docker** — полная настройка Docker с FrankenPHP
- **⚡ FrankenPHP** — современный PHP-сервер с worker-режимом для продакшена
- **🛠️ Инструменты разработки** — PHPStan уровня 6, PHPUnit, Xdebug, Web Profiler
- **🔒 Безопасность** — компонент Symfony Security предустановлен (аутентификация — под проект)
- **📊 Mercure** — поддержка обновлений в реальном времени
- **❤️ Health Check** — liveness-эндпоинт `GET /health` для оркестраторов (k8s/Compose)
- **🌿 Параллельные worktree** — несколько задач одновременно, у каждой свой изолированный стек (`compose.worktree.yaml`)
- **📝 Самодокументируемый код** — строгая типизация, без лишних комментариев

## 🚀 Быстрый старт

### Требования

- Docker и Docker Compose
- Git

### Установка

1. **Создайте новый проект из шаблона:**
   ```bash
   git clone <this-repository>
   cd api-platform-symfony-modular-skeleton
   ```

2. **Настройте окружение:**
   ```bash
   cp .env .env.local
   # Edit .env.local with your settings
   ```

3. **Запустите контейнеры Docker:**
   ```bash
   docker compose up -d --build
   ```

4. **Откройте приложение:**
    - API: http://localhost
    - Документация API: http://localhost/docs
    - Web Profiler: http://localhost/_profiler

   Зависимости устанавливаются автоматически через entrypoint-скрипт. Миграции выполняются автоматически при старте контейнера.

## 📁 Структура проекта

```
├── bin/                    # Console commands
├── config/                 # Configuration
│   ├── packages/           # Bundle configurations
│   ├── routes.php          # Route definitions
│   └── services.php        # Modular DI auto-loader
├── docker/                 # Docker configurations
│   ├── frankenphp/         # FrankenPHP setup
│   │   ├── Caddyfile       # Caddy server config
│   │   ├── docker-entrypoint.sh # Container initialization
│   │   └── conf.d/         # PHP INI configurations
│   └── supervisor/         # Process management
│       ├── supervisord.conf
│       ├── supervisor.d/   # Production supervisor configs
│       └── supervisor_dev.d/ # Dev supervisor configs
├── docs/                   # Documentation
├── migrations/             # Database migrations
├── public/                 # Public directory
│   └── index.php           # Entry point
├── src/                    # Application code
│   ├── Kernel.php          # Application kernel
│   └── Example/            # Example module (delete in production)
│       ├── Entity/         # Doctrine entities
│       ├── Repository/     # Doctrine repositories
│       ├── ApiPlatform/    # API Platform processors/extensions
│       ├── Service/        # Business logic
│       ├── di.php          # Module DI configuration
│       └── api_platform.php # Module API Platform config
├── templates/              # Twig templates
├── tests/                  # Tests
│   ├── ApiTestCase.php     # Base case for API integration tests (schema reset)
│   ├── Integration/        # API integration tests (WebTestCase)
│   └── Unit/               # Unit tests
└── var/                    # Runtime files (cache, logs)
```

## 🏗️ Создание первого модуля

1. **Создайте структуру каталогов модуля:**
   ```bash
   mkdir -p src/YourModule/{Entity,Repository,Service,ApiPlatform}
   ```

2. **Создайте конфигурацию DI** (`src/YourModule/di.php`):
   ```php
   <?php

   declare(strict_types=1);

   use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

   return static function (ContainerConfigurator $container): void {
       $services = $container->services();

       $services->defaults()
           ->autowire()
           ->autoconfigure();

       $services->load('App\\YourModule\\', './')
           ->exclude(['./Entity/', './*.php']);
   };
   ```

3. **Создайте сущность:**
   ```php
   <?php
   
   declare(strict_types=1);
   
   namespace App\YourModule\Entity;
   
   use ApiPlatform\Metadata\ApiResource;
   use Doctrine\DBAL\Types\Types;
   use Doctrine\ORM\Mapping as ORM;
   
   #[ORM\Entity]
   #[ApiResource]
   class YourEntity
   {
       #[ORM\Id]
       #[ORM\GeneratedValue(strategy: 'IDENTITY')]
       #[ORM\Column(type: Types::INTEGER)]
       private ?int $id = null;
       
       // Your fields here
   }
   ```

4. **Сгенерируйте миграцию:**
   ```bash
   docker compose exec php bin/console doctrine:migrations:diff
   docker compose exec php bin/console doctrine:migrations:migrate
   ```

5. **Обратитесь к вашему API:**
    - Список: `GET /your_entities`
    - Получить один: `GET /your_entities/{id}`
    - Создать: `POST /your_entities`
    - Обновить: `PUT /your_entities/{id}`
    - Удалить: `DELETE /your_entities/{id}`

## 🔧 Частые команды

```bash
# Clear cache
docker compose exec php bin/console cache:clear

# Create migration
docker compose exec php bin/console doctrine:migrations:diff

# Run migrations
docker compose exec php bin/console doctrine:migrations:migrate

# Run tests
docker compose exec php vendor/bin/simple-phpunit

# Static analysis
docker compose exec php vendor/bin/phpstan analyse

# View logs
docker compose logs -f php
```

## 🎨 Модульная архитектура

Проект использует **модульную конфигурацию DI**, которая автоматически загружает определения сервисов из каждого модуля:

- `src/*/di.php` — определения сервисов
- `src/*/doctrine.php` — конфигурация, специфичная для Doctrine
- `src/*/api_platform.php` — конфигурация API Platform

Сервисы автоматически регистрируются и настраиваются, когда вы размещаете эти файлы в каталогах своих модулей.

## 🛠️ Разработка

### Отладка с Xdebug

Xdebug предварительно настроен. Для PHPStorm:

1. Настройте сервер с именем `api`
2. Задайте маппинг путей: `/app` → `<your-project-path>`
3. Включите прослушивание отладочных подключений

### Запуск тестов

```bash
# All tests
docker compose exec php vendor/bin/simple-phpunit

# Specific test
docker compose exec php vendor/bin/simple-phpunit tests/Integration/Example/ExampleTest.php

# With coverage
docker compose exec php vendor/bin/simple-phpunit --coverage-html var/coverage
```

### Качество кода

```bash
# PHPStan
docker compose exec php vendor/bin/phpstan analyse

# PHP CS Fixer (if configured)
docker compose exec php vendor/bin/php-cs-fixer fix --dry-run

# Composer validation
docker compose exec php composer validate --strict
```

### Параллельные задачи (git worktree)

Две ветки одновременно на одной машине — по worktree на задачу и по стеку на worktree. `compose.worktree.yaml` параметризует единственный захардкоженный порт (порт БД), а имя compose-проекта берётся из директории worktree, поэтому второй стек полностью изолирован:

```bash
git worktree add ../skeleton-status-endpoint -b feat/status-endpoint
cd ../skeleton-status-endpoint

HTTP_PORT=8080 HTTPS_PORT=8443 HTTP3_PORT=8443 DB_PORT=55432 make up-worktree
```

Все последующие команды из этой директории (`docker compose exec …`, `make test`) попадают в этот стек без дополнительных флагов. Полный рецепт, включая уборку: [CONTRIBUTING.ru.md](CONTRIBUTING.ru.md#параллельные-задачи-git-worktree).

### Управление процессами

FrankenPHP управляется через Supervisor для лучшего контроля над процессами:

- **Dev-режим**: использует `docker/supervisor/supervisor_dev.d/frankenphp.ini` с флагом `--watch` для автоперезагрузки
- **Prod-режим**: использует `docker/supervisor/supervisor.d/frankenphp.ini` с worker-режимом для производительности

Просмотр логов Supervisor:
```bash
docker compose exec php supervisorctl status
docker compose logs -f php
```

## 🚢 Развёртывание в продакшене

1. **Соберите продакшен-образ:**
   ```bash
   docker build --target frankenphp_prod -t your-app:latest .
   ```

2. **Настройте продакшен-окружение:**
    - Установите `APP_ENV=prod`
    - Задайте надёжный `APP_SECRET`
    - Настройте продакшен-базу данных
    - Настройте секреты Mercure JWT

3. **Выполните миграции:**
   ```bash
   docker compose exec php bin/console doctrine:migrations:migrate --no-interaction --env=prod
   ```

4. **Включите предзагрузку opcache** (настроено в `app.prod.ini`)

## 📚 Документация

- [Архитектура](docs/ARCHITECTURE.ru.md) — детали модульной архитектуры
- [Начало работы](docs/GETTING_STARTED.ru.md) — пошаговое руководство
- [Разработка модулей](docs/MODULE_DEVELOPMENT.ru.md) — создание модулей
- [Лучшие практики](docs/BEST_PRACTICES.ru.md) — стиль кода и соглашения
- [Лучшие практики](docs/BEST_PRACTICES.ru.md) — стиль кода и соглашения

## 🔐 Безопасность

- Компонент Symfony Security предустановлен — провайдер/файрвол подключаете под свой проект (модель аутентификации не выбрана за вас; JWT из коробки нет)
- Авторизация — через operation-level `security`-выражения API Platform + `access_control` в `config/packages/security.php`
- Принудительный HTTPS в продакшене
- Заголовки безопасности настроены в Caddy

## 📋 Технологический стек

- **PHP 8.4** со строгой типизацией
- **Symfony 7.4** — фреймворк
- **API Platform 4.3** — генерация REST API
- **Doctrine ORM 3.6** — абстракция базы данных
- **PostgreSQL 16** — база данных
- **FrankenPHP** — современный сервер приложений PHP
- **Caddy** — веб-сервер с автоматическим HTTPS
- **Mercure** — обновления в реальном времени
- **PHPStan** — статический анализ (уровень 8)
- **PHPUnit** — фреймворк для тестирования

## 🤝 Участие в разработке

Это шаблонный проект. Свободно изменяйте его под свои нужды.

## 📄 Лицензия

MIT

## 🙋 Поддержка

По вопросам и проблемам обращайтесь к документации в каталоге `docs/`.

---

**Приятной разработки! 🚀**
