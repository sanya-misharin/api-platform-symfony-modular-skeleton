# Best Practices

This document outlines coding standards and best practices for this project.

## Code Style

### Strict Types

**Always** use strict type declarations in every PHP file:

```php
<?php

declare(strict_types=1);

namespace App\YourModule;
```

### Type Hints

Use type hints everywhere:

```php
// ✅ Good
public function process(YourEntity $entity, int $id): YourEntity
{
    return $entity;
}

// ❌ Bad
public function process($entity, $id)
{
    return $entity;
}
```

### Return Types

Always declare return types:

```php
// ✅ Good
public function getName(): string
{
    return $this->name;
}

// ❌ Bad
public function getName()
{
    return $this->name;
}
```

### Readonly Properties

Use readonly properties when values don't change after construction:

```php
// ✅ Good
final readonly class YourService
{
    public function __construct(
        private YourRepository $repository,
    ) {
    }
}

// ❌ Bad (unless you need to modify dependencies)
final class YourService
{
    private YourRepository $repository;
    
    public function __construct(YourRepository $repository)
    {
        $this->repository = $repository;
    }
}
```

### Constructor Property Promotion

Use constructor property promotion (PHP 8.0+):

```php
// ✅ Good
public function __construct(
    private string $name,
    private int $age,
) {
}

// ❌ Bad
private string $name;
private int $age;

public function __construct(string $name, int $age)
{
    $this->name = $name;
    $this->age = $age;
}
```

## Documentation

### No Comments

**DO NOT** add descriptive comments. Code should be self-documenting through clear naming:

```php
// ❌ Bad
/**
 * This method processes the entity and saves it to database
 */
public function process(Entity $entity): void
{
    // Process the entity
    $entity->setStatus('processed');
    // Save to database
    $this->repository->save($entity);
}

// ✅ Good
public function process(Entity $entity): void
{
    $entity->setStatus('processed');
    $this->repository->save($entity);
}
```

### PHPDoc for Types Only

Use PHPDoc **only** for type hints that PHP cannot express:

```php
// ✅ Good - PHPStan needs this
/**
 * @var array<string, mixed>
 */
private array $config;

/**
 * @return array<int, YourEntity>
 */
public function findAll(): array
{
    return $this->repository->findAll();
}

// ✅ Good - Generics for repositories
/**
 * @extends ServiceEntityRepository<YourEntity>
 */
class YourEntityRepository extends ServiceEntityRepository
{
}
```

## Entity Conventions

### Use Attributes

```php
// ✅ Good
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[ApiResource]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;
}
```

### Use Types Constants

```php
// ✅ Good
use Doctrine\DBAL\Types\Types;

#[ORM\Column(type: Types::STRING, length: 255)]
private string $name;

#[ORM\Column(type: Types::TEXT)]
private string $description;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
private \DateTimeImmutable $createdAt;

// ❌ Bad
#[ORM\Column(type: 'string', length: 255)]
private string $name;
```

### Identity Generation Strategy

For PostgreSQL, use IDENTITY:

```php
// ✅ Good for PostgreSQL
#[ORM\Id]
#[ORM\GeneratedValue(strategy: 'IDENTITY')]
#[ORM\Column(type: Types::INTEGER)]
private ?int $id = null;

// ❌ Bad
#[ORM\Id]
#[ORM\GeneratedValue(strategy: 'AUTO')]
#[ORM\Column(type: 'integer')]
private ?int $id = null;
```

### Validation Constraints

Use Symfony validation constraints:

```php
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Column(type: Types::STRING, length: 255)]
#[Assert\NotBlank]
#[Assert\Length(max: 255)]
private string $name;

#[ORM\Column(type: Types::STRING, length: 255)]
#[Assert\Email]
private string $email;
```

### Immutable Dates

Prefer `DateTimeImmutable` over `DateTime`:

```php
// ✅ Good
#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
private \DateTimeImmutable $createdAt;

// ❌ Bad
#[ORM\Column(type: Types::DATETIME_MUTABLE)]
private \DateTime $createdAt;
```

## API Platform

### Explicit Operations

Be explicit about available operations:

```php
// ✅ Good
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
    ]
)]

// ❌ Bad (implicit operations)
#[ApiResource]
```

### Custom Operations

Use dedicated processors for complex operations:

```php
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/products/{id}/publish',
            processor: PublishProductProcessor::class
        ),
    ]
)]
```

### Use Short Attributes

```php
// ✅ Good
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    operations: [
        new Get(),
        new Post(),
    ]
)]

// ❌ Bad
#[ApiResource(
    operations: [
        new \ApiPlatform\Metadata\Get(),
        new \ApiPlatform\Metadata\Post(),
    ]
)]
```

## Service Layer

### Keep Repositories Thin

Repositories should handle basic CRUD operations only:

```php
// ✅ Good
class ProductRepository extends ServiceEntityRepository
{
    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

// ❌ Bad (complex business logic in repository)
class ProductRepository extends ServiceEntityRepository
{
    public function createProductWithNotification(array $data): Product
    {
        $product = new Product();
        // ... complex logic
        $this->mailer->send(...);
        $this->eventDispatcher->dispatch(...);
        return $product;
    }
}
```

### Business Logic in Services

Put complex logic in services:

```php
// ✅ Good
final readonly class ProductService
{
    public function __construct(
        private ProductRepository $repository,
        private MailerInterface $mailer,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }
    
    public function createProduct(array $data): Product
    {
        $product = new Product();
        // ... set data
        
        $this->repository->save($product, flush: true);
        $this->mailer->send(...);
        $this->eventDispatcher->dispatch(new ProductCreated($product));
        
        return $product;
    }
}
```

### Final Classes

Mark classes as final when they shouldn't be extended:

```php
// ✅ Good for services
final readonly class YourService
{
}

// ✅ Good for value objects
final readonly class Email
{
}

// ⚠️ Don't use final for entities (Doctrine needs proxies)
class YourEntity
{
}
```

## Dependency Injection

### Use Constructor Injection

```php
// ✅ Good
public function __construct(
    private YourRepository $repository,
    private LoggerInterface $logger,
) {
}

// ❌ Bad (setter injection)
private YourRepository $repository;

public function setRepository(YourRepository $repository): void
{
    $this->repository = $repository;
}
```

### Module DI Configuration

```yaml
# src/YourModule/di.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\YourModule\:
        resource: '../'
        exclude:
            - '../Entity/'
```

## Naming Conventions

### Classes

- **Entities**: Singular noun (e.g., `Product`, `User`, `Order`)
- **Repositories**: Entity name + `Repository` (e.g., `ProductRepository`)
- **Services**: Descriptive name + `Service` (e.g., `ProductService`, `EmailSender`)
- **Processors**: Entity + `Processor` (e.g., `ProductProcessor`)
- **Providers**: Entity + `Provider` (e.g., `ProductProvider`)

### Methods

- **Getters**: `getPropertyName()` or `isPropertyName()` for booleans
- **Setters**: `setPropertyName()`
- **Actions**: Verb + noun (e.g., `createProduct()`, `sendEmail()`)

### Variables

- Use descriptive names:

```php
// ✅ Good
$activeProducts = $this->repository->findActiveProducts();
$totalAmount = $order->calculateTotal();

// ❌ Bad
$prods = $this->repository->findActiveProducts();
$total = $order->calculateTotal();
```

## Error Handling

### Use Type System

Leverage PHP's type system instead of defensive checks:

```php
// ✅ Good
public function process(Product $product): void
{
    // Type system ensures $product is correct type
}

// ❌ Bad
public function process($product): void
{
    if (!$product instanceof Product) {
        throw new \InvalidArgumentException('Invalid product');
    }
}
```

### Use Specific Exceptions

```php
// ✅ Good
if (!$user->isActive()) {
    throw new UserNotActiveException();
}

// ❌ Bad
if (!$user->isActive()) {
    throw new \Exception('User not active');
}
```

## Testing

### Test Naming

```php
// ✅ Good
public function testCreateProductWithValidData(): void
public function testThrowsExceptionWhenProductNotFound(): void

// ❌ Bad
public function test1(): void
public function testProduct(): void
```

### Use Type Declarations in Tests

```php
// ✅ Good
public function testSomething(): void
{
    self::assertSame('expected', $actual);
}

// ❌ Bad
public function testSomething()
{
    $this->assertSame('expected', $actual);
}
```

## Database

### Migrations

- Always review generated migrations before running them
- Use descriptive migration names
- Test migrations on a copy of production data

### Indexes

Add indexes for frequently queried columns:

```php
#[ORM\Entity]
#[ORM\Index(columns: ['email'], name: 'user_email_idx')]
#[ORM\Index(columns: ['created_at'], name: 'user_created_at_idx')]
class User
{
}
```

### Foreign Keys

Use proper relations:

```php
#[ORM\ManyToOne(targetEntity: Category::class)]
#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
private Category $category;
```

## Security

### Never Trust User Input

Always validate and sanitize:

```php
#[Assert\NotBlank]
#[Assert\Length(min: 3, max: 255)]
#[Assert\Regex(pattern: '/^[a-zA-Z0-9_-]+$/')]
private string $username;
```

### Use Parameterized Queries

Doctrine does this automatically, but if using raw SQL:

```php
// ✅ Good
$query = $entityManager->createQuery(
    'SELECT u FROM App\Entity\User u WHERE u.email = :email'
);
$query->setParameter('email', $email);

// ❌ Bad
$query = $entityManager->createQuery(
    "SELECT u FROM App\Entity\User u WHERE u.email = '$email'"
);
```

## Performance

### Use Worker Mode in Production

FrankenPHP worker mode is enabled by default in production:

```yaml
# .env
FRANKENPHP_CONFIG="import worker.Caddyfile"
```

### Eager Loading

Avoid N+1 queries:

```php
// ✅ Good
public function findAllWithCategory(): array
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.category', 'c')
        ->addSelect('c')
        ->getQuery()
        ->getResult();
}

// ❌ Bad
public function findAll(): array
{
    return $this->findAll(); // Will cause N+1 when accessing $product->getCategory()
}
```

### Pagination

Always paginate large collections:

```php
#[ApiResource(
    paginationEnabled: true,
    paginationItemsPerPage: 20,
    paginationMaximumItemsPerPage: 100,
)]
```

## Git

### Commit Messages

Follow conventional commits:

```
feat: add product creation endpoint
fix: resolve pagination issue in user list
docs: update installation guide
refactor: extract validation logic to service
test: add tests for product processor
```

### Branch Naming

```
feature/user-authentication
fix/product-validation-error
refactor/extract-email-service
```

## PHPStan

The project uses PHPStan level 6. Fix all issues:

```bash
docker compose exec php vendor/bin/phpstan analyse
```

Common fixes:

```php
// For dynamic arrays, add type hints
/**
 * @var array<string, mixed>
 */
private array $data;

// For entity repositories
/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
```

## Summary

1. ✅ Use strict types everywhere
2. ✅ Type hint all parameters and returns
3. ✅ Use PHP 8.4 features (attributes, readonly, etc.)
4. ✅ No descriptive comments - self-documenting code
5. ✅ PHPDoc only for complex types
6. ✅ Keep repositories thin
7. ✅ Business logic in services
8. ✅ Validate user input
9. ✅ Test your code
10. ✅ Follow PSR-12 coding standards

