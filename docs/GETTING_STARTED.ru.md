[English](GETTING_STARTED.md) · [Русский](GETTING_STARTED.ru.md)

# Начало работы

Это руководство поможет вам создать ваш первый модуль и API-эндпоинт.

## Требования

- Установленные Docker и Docker Compose
- Базовое понимание Symfony и API Platform
- Знание PHP 8.4

## Шаг 1: Запуск приложения

```bash
# Clone the repository
git clone <repository-url>
cd api-platform-symfony-modular-skeleton

# Start Docker containers
docker compose up -d --build

# Verify it's running
curl http://localhost/docs
```

Вы должны увидеть страницу документации API Platform.

## Шаг 2: Создание вашего первого модуля

Создадим для примера модуль `Product`.

### 2.1 Создание структуры каталогов

```bash
mkdir -p src/Product/{Entity,Repository,Service}
```

### 2.2 Создание сущности

Создайте `src/Product/Entity/Product.php`:

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

### 2.3 Создание репозитория

Создайте `src/Product/Repository/ProductRepository.php`:

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

### 2.4 Создание DI-конфигурации

Создайте `src/Product/di.php`:

```php
<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Product\\', './')
        ->exclude(['./Entity/']);
};
```

## Шаг 3: Генерация и запуск миграции

```bash
# Generate migration
docker compose exec php bin/console doctrine:migrations:diff

# Review the generated migration in migrations/

# Run migration
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

## Шаг 4: Тестирование вашего API

### Список товаров (должен быть пустым)
```bash
curl http://localhost/products
```

### Создание товара
```bash
curl -X POST http://localhost/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop",
    "price": "999.99",
    "description": "High-performance laptop"
  }'
```

### Получение товара
```bash
curl http://localhost/products/1
```

### Обновление товара
```bash
curl -X PUT http://localhost/products/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Gaming Laptop",
    "price": "1299.99",
    "description": "High-performance gaming laptop"
  }'
```

### Удаление товара
```bash
curl -X DELETE http://localhost/products/1
```

## Шаг 5: Просмотр документации API

Откройте в браузере: http://localhost/docs

Вы увидите интерактивную Swagger-документацию для вашего Product API.

## Дальнейшие шаги

1. **Добавьте бизнес-логику**: создавайте сервисы в `src/Product/Service/`
2. **Добавьте валидацию**: используйте ограничения валидации Symfony
3. **Добавьте тесты**: создавайте юнит-тесты в `tests/Unit/Product/`
4. **Настройте API**: используйте state processors API Platform для сложных операций
5. **Добавьте связи**: свяжите Product с другими сущностями (например, Category)

## Частые проблемы

### Миграция не выполнилась
```bash
# Check database connection
docker compose exec php bin/console dbal:run-sql "SELECT 1"

# Check migration status
docker compose exec php bin/console doctrine:migrations:status
```

### API возвращает ошибку 500
```bash
# Check logs
docker compose logs php

# Clear cache
docker compose exec php bin/console cache:clear
```

### Сущность не найдена в API
- Убедитесь, что присутствует атрибут `#[ApiResource]`
- Очистите кэш: `docker compose exec php bin/console cache:clear`
- Проверьте, что DI-конфигурация корректна

## Полезные команды

```bash
# List all routes
docker compose exec php bin/console debug:router

# List all services
docker compose exec php bin/console debug:container

# Validate schema
docker compose exec php bin/console doctrine:schema:validate
```

## Ресурсы

- [Документация API Platform](https://api-platform.com/docs/)
- [Документация Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [Документация Symfony](https://symfony.com/doc/current/index.html)
