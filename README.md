[English](README.md) · [Русский](README.ru.md)

# API Platform + Symfony Modular Skeleton

🚀 Production-ready starter template for building modular REST API backends with **Symfony 7.3** and **API Platform 4.1
**.

## ✨ Features

- **🎯 Modular Architecture** - Each module is isolated with its own DI configuration
- **📦 API Platform** - Automatic REST API generation with OpenAPI documentation
- **🐘 PHP 8.4** - Latest PHP with strict types and modern features
- **🗄️ PostgreSQL 16** - Production-grade database
- **🐳 Docker Ready** - Complete Docker setup with FrankenPHP
- **⚡ FrankenPHP** - Modern PHP server with worker mode for production
- **🛠️ Dev Tools** - PHPStan level 6, PHPUnit, Xdebug, Web Profiler
- **🔒 Security** - Symfony Security component pre-configured
- **📊 Mercure** - Real-time updates support
- **📝 Self-Documenting Code** - Strict typing, no comment clutter

## 🚀 Quick Start

### Prerequisites

- Docker and Docker Compose
- Git

### Installation

1. **Create new project from template:**
   ```bash
   git clone <this-repository>
   cd api-platform-symfony-modular-skeleton
   ```

2. **Configure environment:**
   ```bash
   cp .env .env.local
   # Edit .env.local with your settings
   ```

3. **Start Docker containers:**
   ```bash
   docker compose up -d --build
   ```

4. **Access the application:**
    - API: http://localhost
    - API Docs: http://localhost/docs
    - Web Profiler: http://localhost/_profiler

   Dependencies are installed automatically via the entrypoint script. Migrations run automatically on container start.

## 📁 Project Structure

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
│       ├── di.yaml         # Module DI configuration
│       └── api_platform.yaml # Module API Platform config
├── templates/              # Twig templates
├── tests/                  # Tests
│   └── Unit/               # Unit tests
└── var/                    # Runtime files (cache, logs)
```

## 🏗️ Creating Your First Module

1. **Create module directory structure:**
   ```bash
   mkdir -p src/YourModule/{Entity,Repository,Service,ApiPlatform}
   ```

2. **Create DI configuration** (`src/YourModule/di.yaml`):
   ```yaml
   services:
       _defaults:
           autowire: true
           autoconfigure: true

       App\YourModule\:
           resource: '../'
           exclude:
               - '../Entity/'
   ```

3. **Create an entity:**
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

4. **Generate migration:**
   ```bash
   docker compose exec php bin/console doctrine:migrations:diff
   docker compose exec php bin/console doctrine:migrations:migrate
   ```

5. **Access your API:**
    - List: `GET /your_entities`
    - Get one: `GET /your_entities/{id}`
    - Create: `POST /your_entities`
    - Update: `PUT /your_entities/{id}`
    - Delete: `DELETE /your_entities/{id}`

## 🔧 Common Commands

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

## 🎨 Modular Architecture

The project uses **modular DI configuration** that automatically loads service definitions from each module:

- `src/*/di.yaml` - Service definitions
- `src/*/doctrine.yaml` - Doctrine-specific configuration
- `src/*/api_platform.yaml` - API Platform configuration

Services are automatically registered and configured when you place these files in your module directories.

## 🛠️ Development

### Debugging with Xdebug

Xdebug is pre-configured. For PHPStorm:

1. Configure a server named `api`
2. Set path mapping: `/app` → `<your-project-path>`
3. Start listening for debug connections

### Running Tests

```bash
# All tests
docker compose exec php vendor/bin/simple-phpunit

# Specific test
docker compose exec php vendor/bin/simple-phpunit tests/Unit/YourTest.php

# With coverage
docker compose exec php vendor/bin/simple-phpunit --coverage-html var/coverage
```

### Code Quality

```bash
# PHPStan
docker compose exec php vendor/bin/phpstan analyse

# PHP CS Fixer (if configured)
docker compose exec php vendor/bin/php-cs-fixer fix --dry-run

# Composer validation
docker compose exec php composer validate --strict
```

### Process Management

FrankenPHP is managed by Supervisor for better process control:

- **Dev mode**: Uses `docker/supervisor/supervisor_dev.d/frankenphp.ini` with `--watch` flag for auto-reload
- **Prod mode**: Uses `docker/supervisor/supervisor.d/frankenphp.ini` with worker mode for performance

View Supervisor logs:
```bash
docker compose exec php supervisorctl status
docker compose logs -f php
```

## 🚢 Production Deployment

1. **Build production image:**
   ```bash
   docker build --target frankenphp_prod -t your-app:latest .
   ```

2. **Configure production environment:**
    - Set `APP_ENV=prod`
    - Set strong `APP_SECRET`
    - Configure production database
    - Set up Mercure JWT secrets

3. **Run migrations:**
   ```bash
   docker compose exec php bin/console doctrine:migrations:migrate --no-interaction --env=prod
   ```

4. **Enable opcache preload** (configured in `app.prod.ini`)

## 📚 Documentation

- [Architecture](docs/ARCHITECTURE.md) - Modular architecture details
- [Getting Started](docs/GETTING_STARTED.md) - Step-by-step guide
- [Module Development](docs/MODULE_DEVELOPMENT.md) - Creating modules
- [Best Practices](docs/BEST_PRACTICES.md) - Code style and conventions
- [Best Practices](docs/BEST_PRACTICES.md) - Code style and conventions

## 🔐 Security

- Symfony Security component pre-configured
- JWT authentication support (optional)
- HTTPS enforced in production
- Security headers configured in Caddy

## 📋 Tech Stack

- **PHP 8.4** with strict types
- **Symfony 7.3** - Framework
- **API Platform 4.1** - REST API generation
- **Doctrine ORM 3.5** - Database abstraction
- **PostgreSQL 16** - Database
- **FrankenPHP** - Modern PHP application server
- **Caddy** - Web server with automatic HTTPS
- **Mercure** - Real-time updates
- **PHPStan** - Static analysis (level 6)
- **PHPUnit** - Testing framework

## 🤝 Contributing

This is a template project. Feel free to modify it according to your needs.

## 📄 License

MIT

## 🙋 Support

For issues and questions, please refer to the documentation in the `docs/` directory.

---

**Happy Coding! 🚀**
