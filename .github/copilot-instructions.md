# Instructions for AI Copilot

## Project Overview
- This is a **modular API backend skeleton** built with Symfony 7.3 and API Platform 4.1
- The architecture is based on **isolated, feature-based modules** for better scalability and maintainability
- Each module is self-contained with its own entities, services, and DI configuration
- Infrastructure and core loading are in `src/Infrastructure/`

## Key Patterns and Conventions

### Code Style
- **DO NOT add comments** to code (no descriptive PHPDoc blocks like "This method does...")
- **Use PHPDoc ONLY** for type hints needed by PHPStan: `@var`, `@param`, `@return`, `@implements`, `@extends`
- Code should be self-documenting through clear class, method, and variable names
- **Use strict typing**: `declare(strict_types=1)`, type hints, return types in ALL PHP files
- PHP 8.4 features: attributes, readonly properties, constructor property promotion

### Module Structure
- Modules live in `src/<ModuleName>/`
- Each module MUST have `di.yaml` (or `di.php`) for service configuration
- Module can optionally have `doctrine.yaml`, `api_platform.yaml`, `routing.php`
- Typical module structure:
  ```
  src/YourModule/
  ├── Entity/              # Doctrine entities
  ├── Repository/          # Doctrine repositories
  ├── Service/             # Business logic
  ├── ApiPlatform/         # State processors, extensions
  ├── Messenger/           # Message handlers (if needed)
  ├── Serializer/          # Custom normalizers (if needed)
  └── di.yaml              # DI configuration
  ```

### Configuration
- **Bundles**: Registered in `config/bundles.php`
- **Services**: Auto-loaded from `src/**/di.yaml` via `config/services.php`
- **Routes**: Auto-loaded from `src/**/routing.php` via `config/routes.php`
- **Package configs**: In `config/packages/`

### Entity Conventions
- Use `#[ORM\Entity]` attributes
- Always include `#[ApiResource]` for API exposure
- Use IDENTITY generation strategy for PostgreSQL: `#[ORM\GeneratedValue(strategy: 'IDENTITY')]`
- Prefer `Types::*` constants over string literals
- Use validation constraints: `#[Assert\NotBlank]`, `#[Assert\Length(...)]`, etc.

### Repository Pattern
- Extend `ServiceEntityRepository`
- Keep repositories thin - complex queries belong in services
- Standard methods: `save()`, `remove()`, `find*()`

### API Platform
- Use operation attributes: `#[Get]`, `#[GetCollection]`, `#[Post]`, `#[Put]`, `#[Delete]`
- For complex operations, create State Processors in `ApiPlatform/` directory
- Use Extensions for filtering, pagination, security

## Developer Workflows

### Build and Run
- **Start**: `docker compose up --build`
- **Entry point**: `public/index.php` (FrankenPHP/Caddy setup in `docker/`)

### Testing
- **Run tests**: `vendor/bin/simple-phpunit`
- **Coverage**: `vendor/bin/simple-phpunit --coverage-html var/coverage`

### Static Analysis
- **PHPStan**: `vendor/bin/phpstan analyse` (level 6)
- **Rector**: `vendor/bin/rector` (for refactoring)

### Database
- **Create migration**: `bin/console doctrine:migrations:diff`
- **Run migrations**: `bin/console doctrine:migrations:migrate`
- **Validate schema**: `bin/console doctrine:schema:validate`

### Cache
- **Clear**: `bin/console cache:clear`

## Integration Points

### API Platform
- REST endpoints auto-generated from entities with `#[ApiResource]`
- Configuration: `config/packages/api_platform.yaml`
- OpenAPI docs: `/docs`

### Doctrine ORM
- PostgreSQL 16 database
- Auto-mapping enabled
- Migrations in `migrations/`

### Mercure (Optional)
- Real-time updates via Mercure Hub
- Configuration: `config/packages/mercure.yaml`

### Security
- Symfony Security component
- Basic configuration in `config/packages/security.yaml`
- Extend for JWT, OAuth, etc. as needed

## Project-Specific Notes

### Adding a New Module
1. Create directory: `mkdir -p src/ModuleName/{Entity,Repository,Service}`
2. Create `src/ModuleName/di.yaml`:
   ```yaml
   services:
       _defaults:
           autowire: true
           autoconfigure: true
       
       App\ModuleName\:
           resource: '../'
           exclude:
               - '../Entity/'
   ```
3. Create entities, repositories, services
4. Generate migration: `bin/console doctrine:migrations:diff`
5. Run migration: `bin/console doctrine:migrations:migrate`

### Environment Configuration
- Use `docker/frankenphp/conf.d/` for PHP INI settings
- `app.ini` - base settings
- `app.dev.ini` - dev-specific (Xdebug)
- `app.prod.ini` - prod-specific (opcache preload)

### Debugging
- Symfony Profiler enabled in dev: `/_profiler`
- Xdebug configured for Docker (set up PHPStorm remote debugging)
- Logs: `var/log/`

### Supervisor
- Manages FrankenPHP process in Docker
- Configurations: `docker/supervisor/`
- Dev vs prod configurations separate

## References

- See `README.md` for high-level overview
- See `docs/ARCHITECTURE.md` for modular architecture details
- See `docs/GETTING_STARTED.md` for step-by-step guide
- See `config/` for all service, routing, and package configs
- See `docker/` for web server and PHP runtime setup

## Template Usage

This is a **skeleton/template project**:
- Delete `src/Example/` module in production projects
- Customize `config/packages/api_platform.yaml` (title, description)
- Update `composer.json` (name, description)
- Configure security as needed
- Add modules for your domain

## Key Principles

1. **Modularity First**: Keep features isolated
2. **Self-Documenting Code**: Avoid comments, use clear names
3. **Strict Types Always**: `declare(strict_types=1)` in every PHP file
4. **Type Hints Everywhere**: Parameters, return types, properties
5. **PHPStan Level 6**: Code must pass static analysis
6. **API First**: Expose APIs via API Platform attributes
7. **Docker Native**: Development and production use Docker
