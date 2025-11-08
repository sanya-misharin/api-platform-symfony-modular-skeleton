# Module Development Guide

This guide explains how to develop modules in the API Platform Symfony Modular Skeleton.

## Module Structure

Each module follows a consistent directory structure:

```
src/YourModule/
├── Entity/              # Doctrine entities
├── Repository/          # Doctrine repositories
├── Service/             # Business logic
├── ApiPlatform/         # State processors, providers, extensions
├── Messenger/           # Message handlers (optional)
├── Serializer/          # Custom normalizers/denormalizers (optional)
├── di.yaml              # Dependency injection configuration
├── doctrine.yaml        # Doctrine-specific configuration (optional)
├── api_platform.yaml    # API Platform configuration (optional)
└── routing.php          # Module routes (optional)
```

## Creating a Module

### Step 1: Create Directory Structure

```bash
mkdir -p src/YourModule/{Entity,Repository,Service,ApiPlatform}
```

### Step 2: Create DI Configuration

Create `src/YourModule/di.yaml`:

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

This configuration:
- Enables autowiring for all services in the module
- Auto-configures services with tags
- Excludes entities from service registration

### Step 3: Create Entity

Create `src/YourModule/Entity/YourEntity.php`:

```php
<?php

declare(strict_types=1);

namespace App\YourModule\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: YourEntityRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
    ]
)]
class YourEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
```

### Step 4: Create Repository

Create `src/YourModule/Repository/YourEntityRepository.php`:

```php
<?php

declare(strict_types=1);

namespace App\YourModule\Repository;

use App\YourModule\Entity\YourEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<YourEntity>
 */
class YourEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YourEntity::class);
    }

    public function save(YourEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(YourEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
```

### Step 5: Create Migration

```bash
docker compose exec php bin/console doctrine:migrations:diff
docker compose exec php bin/console doctrine:migrations:migrate
```

## Advanced Module Features

### Custom State Processors

For complex create/update logic, create a state processor in `src/YourModule/ApiPlatform/`:

```php
<?php

declare(strict_types=1);

namespace App\YourModule\ApiPlatform;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\YourModule\Entity\YourEntity;
use App\YourModule\Service\YourService;

/**
 * @implements ProcessorInterface<YourEntity, YourEntity>
 */
final readonly class YourEntityProcessor implements ProcessorInterface
{
    public function __construct(
        private YourService $service,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): YourEntity
    {
        return $this->service->process($data);
    }
}
```

Register it in your entity:

```php
#[ApiResource(
    operations: [
        new Post(processor: YourEntityProcessor::class),
        new Put(processor: YourEntityProcessor::class),
    ]
)]
```

### Custom State Providers

For custom data retrieval logic:

```php
<?php

declare(strict_types=1);

namespace App\YourModule\ApiPlatform;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

/**
 * @implements ProviderInterface<YourEntity>
 */
final readonly class YourEntityProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Custom logic here
    }
}
```

### Doctrine Extensions

For filtering collections:

```php
<?php

declare(strict_types=1);

namespace App\YourModule\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class YourEntityExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        // Add custom filters
    }
}
```

### Business Logic Services

Create services in `src/YourModule/Service/`:

```php
<?php

declare(strict_types=1);

namespace App\YourModule\Service;

use App\YourModule\Entity\YourEntity;
use App\YourModule\Repository\YourEntityRepository;

final readonly class YourService
{
    public function __construct(
        private YourEntityRepository $repository,
    ) {
    }

    public function process(YourEntity $entity): YourEntity
    {
        // Business logic here
        
        $this->repository->save($entity, flush: true);
        
        return $entity;
    }
}
```

## Module Configuration Files

### doctrine.yaml

Create `src/YourModule/doctrine.yaml` for Doctrine-specific configuration:

```yaml
doctrine:
    orm:
        mappings:
            YourModule:
                type: attribute
                dir: '%kernel.project_dir%/src/YourModule/Entity'
                prefix: 'App\YourModule\Entity'
                alias: YourModule
```

### api_platform.yaml

Create `src/YourModule/api_platform.yaml` for API Platform defaults:

```yaml
api_platform:
    defaults:
        extra_properties:
            module: 'YourModule'
```

### routing.php

Create `src/YourModule/routing.php` for custom routes:

```php
<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('your_module_custom_route', '/api/your-custom-endpoint')
        ->controller('App\YourModule\Controller\YourController::__invoke')
        ->methods(['GET']);
};
```

## Testing Modules

### Unit Tests

Create tests in `tests/Unit/YourModule/`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\YourModule;

use App\YourModule\Service\YourService;
use PHPUnit\Framework\TestCase;

class YourServiceTest extends TestCase
{
    public function testProcess(): void
    {
        // Test your service
    }
}
```

### API Tests

Create API tests in `tests/Api/YourModule/`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Api\YourModule;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class YourEntityTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/api/your_entities');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }
}
```

## Best Practices

1. **Keep modules independent** - Avoid direct dependencies between modules
2. **Use events for communication** - Use Symfony EventDispatcher or Messenger for inter-module communication
3. **Thin repositories** - Keep complex queries in services, not repositories
4. **Type everything** - Use strict types, type hints, and return types
5. **No comments** - Code should be self-documenting; use PHPDoc only for type hints
6. **Validate early** - Use Symfony validation constraints on entities
7. **Use readonly** - Mark services as readonly when possible (PHP 8.2+)

## Module Examples

See the `src/Example/` module for a complete working example. Delete it in production projects.

## Common Patterns

### Read-only API

```php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ]
)]
```

### Custom Operations

```php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(
            uriTemplate: '/your_entities/{id}/custom',
            processor: CustomProcessor::class
        ),
    ]
)]
```

### Pagination

```php
#[ApiResource(
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationMaximumItemsPerPage: 100,
)]
```

## Troubleshooting

### Services not found

Ensure `di.yaml` exists and follows the correct format. Clear cache:

```bash
docker compose exec php bin/console cache:clear
```

### API routes not appearing

Check that:
1. Entity has `#[ApiResource]` attribute
2. Module is in `src/` directory
3. Namespace matches directory structure

### Database schema errors

Regenerate migration:

```bash
docker compose exec php bin/console doctrine:migrations:diff
```

## Resources

- [Symfony Dependency Injection](https://symfony.com/doc/current/service_container.html)
- [API Platform Documentation](https://api-platform.com/docs)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)

