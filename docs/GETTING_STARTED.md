# Getting Started

This guide will help you create your first module and API endpoint.

## Prerequisites

- Docker and Docker Compose installed
- Basic understanding of Symfony and API Platform
- PHP 8.4 knowledge

## Step 1: Start the Application

```bash
# Clone the repository
git clone <repository-url>
cd api-platform-symfony-modular-skeleton

# Start Docker containers
docker compose up -d --build

# Verify it's running
curl http://localhost/docs
```

You should see the API Platform documentation page.

## Step 2: Create Your First Module

Let's create a `Product` module as an example.

### 2.1 Create Directory Structure

```bash
mkdir -p src/Product/{Entity,Repository,Service}
```

### 2.2 Create the Entity

Create `src/Product/Entity/Product.php`:

```php
<?php

declare(strict_types=1);

namespace App\Product\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Product\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete(),
    ],
)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private string $name;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private string $price;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
```

### 2.3 Create the Repository

Create `src/Product/Repository/ProductRepository.php`:

```php
<?php

declare(strict_types=1);

namespace App\Product\Repository;

use App\Product\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
}
```

### 2.4 Create DI Configuration

Create `src/Product/di.yaml`:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\Product\:
        resource: '../'
        exclude:
            - '../Entity/'
```

## Step 3: Generate and Run Migration

```bash
# Generate migration
docker compose exec php bin/console doctrine:migrations:diff

# Review the generated migration in migrations/

# Run migration
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

## Step 4: Test Your API

### List Products (should be empty)
```bash
curl http://localhost/products
```

### Create a Product
```bash
curl -X POST http://localhost/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop",
    "price": "999.99",
    "description": "High-performance laptop"
  }'
```

### Get the Product
```bash
curl http://localhost/products/1
```

### Update the Product
```bash
curl -X PUT http://localhost/products/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Gaming Laptop",
    "price": "1299.99",
    "description": "High-performance gaming laptop"
  }'
```

### Delete the Product
```bash
curl -X DELETE http://localhost/products/1
```

## Step 5: View API Documentation

Open in browser: http://localhost/docs

You'll see interactive Swagger documentation for your Product API.

## Next Steps

1. **Add Business Logic**: Create services in `src/Product/Service/`
2. **Add Validation**: Use Symfony validation constraints
3. **Add Tests**: Create unit tests in `tests/Unit/Product/`
4. **Customize API**: Use API Platform state processors for complex operations
5. **Add Relationships**: Link Product to other entities (e.g., Category)

## Common Issues

### Migration Failed
```bash
# Check database connection
docker compose exec php bin/console dbal:run-sql "SELECT 1"

# Check migration status
docker compose exec php bin/console doctrine:migrations:status
```

### API Returns 500 Error
```bash
# Check logs
docker compose logs php

# Clear cache
docker compose exec php bin/console cache:clear
```

### Entity Not Found in API
- Verify `#[ApiResource]` attribute is present
- Clear cache: `docker compose exec php bin/console cache:clear`
- Check that DI configuration is correct

## Useful Commands

```bash
# List all routes
docker compose exec php bin/console debug:router

# List all services
docker compose exec php bin/console debug:container

# Validate schema
docker compose exec php bin/console doctrine:schema:validate
```

## Resources

- [API Platform Documentation](https://api-platform.com/docs/)
- [Doctrine ORM Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [Symfony Documentation](https://symfony.com/doc/current/index.html)
