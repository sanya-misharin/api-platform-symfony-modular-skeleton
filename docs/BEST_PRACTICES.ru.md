[English](BEST_PRACTICES.md) · [Русский](BEST_PRACTICES.ru.md)

# Лучшие практики

Этот документ описывает стандарты кодирования и лучшие практики проекта.

## Стиль кода

### Строгие типы

**Всегда** используйте объявление строгих типов в каждом PHP-файле:

```php
<?php

declare(strict_types=1);

namespace App\YourModule;
```

### Подсказки типов

Используйте подсказки типов везде:

```php
// ✅ Хорошо
public function process(YourEntity $entity, int $id): YourEntity
{
    return $entity;
}

// ❌ Плохо
public function process($entity, $id)
{
    return $entity;
}
```

### Типы возвращаемых значений

Всегда объявляйте типы возвращаемых значений:

```php
// ✅ Хорошо
public function getName(): string
{
    return $this->name;
}

// ❌ Плохо
public function getName()
{
    return $this->name;
}
```

### Readonly-свойства

Используйте readonly-свойства, когда значения не меняются после конструирования:

```php
// ✅ Хорошо
final readonly class YourService
{
    public function __construct(
        private YourRepository $repository,
    ) {
    }
}

// ❌ Плохо (если только вам не нужно менять зависимости)
final class YourService
{
    private YourRepository $repository;
    
    public function __construct(YourRepository $repository)
    {
        $this->repository = $repository;
    }
}
```

### Продвижение свойств конструктора

Используйте продвижение свойств конструктора (PHP 8.0+):

```php
// ✅ Хорошо
public function __construct(
    private string $name,
    private int $age,
) {
}

// ❌ Плохо
private string $name;
private int $age;

public function __construct(string $name, int $age)
{
    $this->name = $name;
    $this->age = $age;
}
```

## Документация

### Без комментариев

**НЕ** добавляйте описательные комментарии. Код должен быть самодокументируемым за счёт понятных имён:

```php
// ❌ Плохо
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

// ✅ Хорошо
public function process(Entity $entity): void
{
    $entity->setStatus('processed');
    $this->repository->save($entity);
}
```

### PHPDoc только для типов

Используйте PHPDoc **только** для подсказок типов, которые PHP не может выразить:

```php
// ✅ Хорошо — это нужно PHPStan
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

// ✅ Хорошо — дженерики для репозиториев
/**
 * @extends ServiceEntityRepository<YourEntity>
 */
class YourEntityRepository extends ServiceEntityRepository
{
}
```

## Конвенции сущностей

### Используйте атрибуты

```php
// ✅ Хорошо
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

### Используйте константы Types

```php
// ✅ Хорошо
use Doctrine\DBAL\Types\Types;

#[ORM\Column(type: Types::STRING, length: 255)]
private string $name;

#[ORM\Column(type: Types::TEXT)]
private string $description;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
private \DateTimeImmutable $createdAt;

// ❌ Плохо
#[ORM\Column(type: 'string', length: 255)]
private string $name;
```

### Стратегия генерации идентификаторов

Для PostgreSQL используйте IDENTITY:

```php
// ✅ Хорошо для PostgreSQL
#[ORM\Id]
#[ORM\GeneratedValue(strategy: 'IDENTITY')]
#[ORM\Column(type: Types::INTEGER)]
private ?int $id = null;

// ❌ Плохо
#[ORM\Id]
#[ORM\GeneratedValue(strategy: 'AUTO')]
#[ORM\Column(type: 'integer')]
private ?int $id = null;
```

### Ограничения валидации

Используйте ограничения валидации Symfony:

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

### Неизменяемые даты

Предпочитайте `DateTimeImmutable` вместо `DateTime`:

```php
// ✅ Хорошо
#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
private \DateTimeImmutable $createdAt;

// ❌ Плохо
#[ORM\Column(type: Types::DATETIME_MUTABLE)]
private \DateTime $createdAt;
```

## API Platform

### Явные операции

Явно указывайте доступные операции:

```php
// ✅ Хорошо
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
    ]
)]

// ❌ Плохо (неявные операции)
#[ApiResource]
```

### Кастомные операции

Используйте выделенные процессоры для сложных операций:

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

### Используйте короткие атрибуты

```php
// ✅ Хорошо
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    operations: [
        new Get(),
        new Post(),
    ]
)]

// ❌ Плохо
#[ApiResource(
    operations: [
        new \ApiPlatform\Metadata\Get(),
        new \ApiPlatform\Metadata\Post(),
    ]
)]
```

## Сервисный слой

### Держите репозитории тонкими

Репозитории должны обрабатывать только базовые CRUD-операции:

```php
// ✅ Хорошо
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

// ❌ Плохо (сложная бизнес-логика в репозитории)
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

### Бизнес-логика в сервисах

Помещайте сложную логику в сервисы:

```php
// ✅ Хорошо
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

### Final-классы

Помечайте классы как final, когда их не следует расширять:

```php
// ✅ Хорошо для сервисов
final readonly class YourService
{
}

// ✅ Хорошо для объектов-значений
final readonly class Email
{
}

// ⚠️ Не используйте final для сущностей (Doctrine нужны прокси)
class YourEntity
{
}
```

## Внедрение зависимостей

### Используйте внедрение через конструктор

```php
// ✅ Хорошо
public function __construct(
    private YourRepository $repository,
    private LoggerInterface $logger,
) {
}

// ❌ Плохо (внедрение через сеттер)
private YourRepository $repository;

public function setRepository(YourRepository $repository): void
{
    $this->repository = $repository;
}
```

### Конфигурация DI модуля

`src/YourModule/di.php`:

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

## Конвенции именования

### Классы

- **Сущности**: существительное в единственном числе (например, `Product`, `User`, `Order`)
- **Репозитории**: имя сущности + `Repository` (например, `ProductRepository`)
- **Сервисы**: описательное имя + `Service` (например, `ProductService`, `EmailSender`)
- **Процессоры**: сущность + `Processor` (например, `ProductProcessor`)
- **Провайдеры**: сущность + `Provider` (например, `ProductProvider`)

### Методы

- **Геттеры**: `getPropertyName()` или `isPropertyName()` для булевых значений
- **Сеттеры**: `setPropertyName()`
- **Действия**: глагол + существительное (например, `createProduct()`, `sendEmail()`)

### Переменные

- Используйте описательные имена:

```php
// ✅ Хорошо
$activeProducts = $this->repository->findActiveProducts();
$totalAmount = $order->calculateTotal();

// ❌ Плохо
$prods = $this->repository->findActiveProducts();
$total = $order->calculateTotal();
```

## Обработка ошибок

### Используйте систему типов

Полагайтесь на систему типов PHP вместо защитных проверок:

```php
// ✅ Хорошо
public function process(Product $product): void
{
    // Type system ensures $product is correct type
}

// ❌ Плохо
public function process($product): void
{
    if (!$product instanceof Product) {
        throw new \InvalidArgumentException('Invalid product');
    }
}
```

### Используйте специфичные исключения

```php
// ✅ Хорошо
if (!$user->isActive()) {
    throw new UserNotActiveException();
}

// ❌ Плохо
if (!$user->isActive()) {
    throw new \Exception('User not active');
}
```

## Тестирование

### Именование тестов

```php
// ✅ Хорошо
public function testCreateProductWithValidData(): void
public function testThrowsExceptionWhenProductNotFound(): void

// ❌ Плохо
public function test1(): void
public function testProduct(): void
```

### Используйте объявления типов в тестах

```php
// ✅ Хорошо
public function testSomething(): void
{
    self::assertSame('expected', $actual);
}

// ❌ Плохо
public function testSomething()
{
    $this->assertSame('expected', $actual);
}
```

## База данных

### Миграции

- Всегда просматривайте сгенерированные миграции перед запуском
- Используйте описательные имена миграций
- Тестируйте миграции на копии продакшн-данных

### Индексы

Добавляйте индексы для часто запрашиваемых колонок:

```php
#[ORM\Entity]
#[ORM\Index(columns: ['email'], name: 'user_email_idx')]
#[ORM\Index(columns: ['created_at'], name: 'user_created_at_idx')]
class User
{
}
```

### Внешние ключи

Используйте корректные связи:

```php
#[ORM\ManyToOne(targetEntity: Category::class)]
#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
private Category $category;
```

## Безопасность

### Никогда не доверяйте пользовательскому вводу

Всегда валидируйте и очищайте:

```php
#[Assert\NotBlank]
#[Assert\Length(min: 3, max: 255)]
#[Assert\Regex(pattern: '/^[a-zA-Z0-9_-]+$/')]
private string $username;
```

### Используйте параметризованные запросы

Doctrine делает это автоматически, но если используете сырой SQL:

```php
// ✅ Хорошо
$query = $entityManager->createQuery(
    'SELECT u FROM App\Entity\User u WHERE u.email = :email'
);
$query->setParameter('email', $email);

// ❌ Плохо
$query = $entityManager->createQuery(
    "SELECT u FROM App\Entity\User u WHERE u.email = '$email'"
);
```

## Производительность

### Используйте worker-режим в продакшене

Worker-режим FrankenPHP включён по умолчанию в продакшене:

```yaml
# .env
FRANKENPHP_CONFIG="import worker.Caddyfile"
```

### Жадная загрузка

Избегайте N+1 запросов:

```php
// ✅ Хорошо
public function findAllWithCategory(): array
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.category', 'c')
        ->addSelect('c')
        ->getQuery()
        ->getResult();
}

// ❌ Плохо
public function findAll(): array
{
    return $this->findAll(); // Will cause N+1 when accessing $product->getCategory()
}
```

### Пагинация

Всегда пагинируйте большие коллекции:

```php
#[ApiResource(
    paginationEnabled: true,
    paginationItemsPerPage: 20,
    paginationMaximumItemsPerPage: 100,
)]
```

## Git

### Сообщения коммитов

Следуйте conventional commits:

```
feat: add product creation endpoint
fix: resolve pagination issue in user list
docs: update installation guide
refactor: extract validation logic to service
test: add tests for product processor
```

### Именование веток

```
feature/user-authentication
fix/product-validation-error
refactor/extract-email-service
```

## PHPStan

Проект использует PHPStan level 8. Исправляйте все замечания:

```bash
docker compose exec php vendor/bin/phpstan analyse
```

Типичные исправления:

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

## Rate limiting (опционально)

Ограничение частоты запросов не поставляется по умолчанию — правильная политика (какие маршруты, per-user или per-IP) зависит от проекта. Добавляйте, когда нужно:

```bash
docker compose exec -T php composer require symfony/rate-limiter
```

Опишите лимитер в `config/packages/rate_limiter.php`:

```php
<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'rate_limiter' => [
            'api' => ['policy' => 'sliding_window', 'limit' => 60, 'interval' => '1 minute'],
        ],
    ]);
};
```

Применяйте в State Processor или контроллере — Symfony автовайрит фабрику по имени (`RateLimiterFactory $apiLimiter`):

```php
$limit = $this->apiLimiter->create($request->getClientIp())->consume();

if (!$limit->isAccepted()) {
    throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time());
}
```

В проде поставьте `symfony/lock`, чтобы счётчики были общими между воркер-процессами FrankenPHP.

## Итог

1. ✅ Используйте строгие типы везде
2. ✅ Указывайте подсказки типов для всех параметров и возвращаемых значений
3. ✅ Используйте возможности PHP 8.4 (атрибуты, readonly и т. д.)
4. ✅ Никаких описательных комментариев — самодокументируемый код
5. ✅ PHPDoc только для сложных типов
6. ✅ Держите репозитории тонкими
7. ✅ Бизнес-логика в сервисах
8. ✅ Валидируйте пользовательский ввод
9. ✅ Тестируйте свой код
10. ✅ Следуйте стандартам кодирования PSR-12
