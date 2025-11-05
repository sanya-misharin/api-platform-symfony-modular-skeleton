# Modular Architecture

This project implements a **modular architecture** where each feature/domain is isolated in its own module.

## What is a Module?

A **module** is a cohesive unit of code organized around a specific business domain or feature. Each module:

- Lives in `src/<ModuleName>/`
- Has its own DI configuration (`di.yaml` or `di.php`)
- Contains related entities, services, repositories, and API resources
- Is automatically discovered and loaded by the framework
- Can be easily moved, renamed, or removed

## Module Structure

```
src/YourModule/
├── Entity/              # Doctrine entities
├── Repository/          # Doctrine repositories
├── Service/             # Business logic
├── ApiPlatform/         # State processors, extensions
├── Messenger/           # Message handlers (optional)
├── Serializer/          # Custom normalizers (optional)
└── di.yaml              # Dependency injection configuration
```

## Module Benefits

### 1. Low Coupling, High Cohesion
- Related code stays together
- Clear module boundaries
- Minimal dependencies between modules

### 2. Scalability
- Team can work on different modules independently
- Easy to understand module scope
- Reduces merge conflicts

### 3. Maintainability
- Easy to locate code
- Safe to refactor within module
- Simple to delete unused features

### 4. Testability
- Test modules in isolation
- Clear dependencies
- Mock external modules easily

## Auto-Loading Configuration

The `config/services.php` automatically imports DI configurations from modules:

```php
$di->import('../src/**/{di}.{php,xml,yaml,yml}');
$di->import('../src/**/{doctrine}.{php,xml,yaml,yml}');
$di->import('../src/**/{api_platform}.{php,xml,yaml,yml}');
```

This means:
- Drop a `di.yaml` in any module → services auto-registered
- Add `doctrine.yaml` → custom types/extensions loaded
- Include `api_platform.yaml` → API configuration applied

## Example Module

The `Example` module demonstrates the pattern:

```yaml
# src/Example/di.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\Example\:
        resource: '../'
        exclude:
            - '../Entity/'
```

This auto-registers all services in the module except entities.

## Module vs Bundle

| Aspect | Module in `src/` | Symfony Bundle |
|--------|------------------|----------------|
| Purpose | Business/domain code | Infrastructure/integration |
| Scope | Project-specific | Universal, reusable |
| Complexity | Simple (just `di.yaml`) | Complex (Bundle + Extension classes) |
| Usage | Feature isolation | Framework extensions |

## Best Practices

### ✅ Do
- Keep modules focused on single domain
- Use clear module names (e.g., `Invoice`, `Payment`, `User`)
- Extract shared code to a separate module or `Infrastructure/`
- Document module boundaries and interactions

### ❌ Don't
- Create modules that depend on many other modules
- Put unrelated features in the same module
- Create circular dependencies between modules
- Mix business and infrastructure code in the same module

## Inter-Module Communication

When modules need to communicate:

1. **Events** - Decouple modules via domain events
2. **Interfaces** - Define contracts in shared namespace
3. **Messenger** - Async communication via message bus
4. **API calls** - For truly independent services

## Migration Strategy

To convert an existing project:

1. Identify feature boundaries
2. Group related code into module directories
3. Create `di.yaml` for each module
4. Remove global service registration
5. Test module isolation
6. Iterate and refine boundaries

## Resources

- Symfony DI Component: https://symfony.com/doc/current/components/dependency_injection.html
- API Platform: https://api-platform.com/docs/
- Domain-Driven Design: Essential for identifying module boundaries
