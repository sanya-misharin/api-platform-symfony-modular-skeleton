[English](MODULE_DEVELOPMENT.md) · [Русский](MODULE_DEVELOPMENT.ru.md)

# Руководство по разработке модулей

Это руководство объясняет, как разрабатывать модули в API Platform Symfony Modular Skeleton.

## Структура модуля

Каждый модуль следует единообразной структуре каталогов:

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

## Создание модуля

### Шаг 1: Создание структуры каталогов

```bash
mkdir -p src/YourModule/{Entity,Repository,Service,ApiPlatform}
```

### Шаг 2: Создание конфигурации DI

Создайте `src/YourModule/di.yaml`:

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

Эта конфигурация:
- Включает autowiring для всех сервисов модуля
- Автоматически настраивает сервисы с помощью тегов
- Исключает сущности из регистрации сервисов

### Шаг 3: Создание сущности

Создайте `src/YourModule/Entity/YourEntity.php`:

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

### Шаг 4: Создание Repository

Создайте `src/YourModule/Repository/YourEntityRepository.php`:

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

### Шаг 5: Создание миграции

```bash
docker compose exec php bin/console doctrine:migrations:diff
docker compose exec php bin/console doctrine:migrations:migrate
```

## Продвинутые возможности модулей

### Кастомные State Processor

Для сложной логики создания/обновления создайте state processor в `src/YourModule/ApiPlatform/`:

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

Зарегистрируйте его в своей сущности:

```php
#[ApiResource(
    operations: [
        new Post(processor: YourEntityProcessor::class),
        new Put(processor: YourEntityProcessor::class),
    ]
)]
```

### Кастомные State Provider

Для кастомной логики получения данных:

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

### Расширения Doctrine

Для фильтрации коллекций:

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

### Сервисы бизнес-логики

Создавайте сервисы в `src/YourModule/Service/`:

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

## Конфигурационные файлы модуля

### doctrine.yaml

Создайте `src/YourModule/doctrine.yaml` для конфигурации, специфичной для Doctrine:

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

Создайте `src/YourModule/api_platform.yaml` для значений по умолчанию API Platform:

```yaml
api_platform:
    defaults:
        extra_properties:
            module: 'YourModule'
```

### routing.php

Создайте `src/YourModule/routing.php` для кастомных маршрутов:

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

## Тестирование модулей

### Модульные тесты

Создавайте тесты в `tests/Unit/YourModule/`:

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

### API-тесты

Создавайте API-тесты в `tests/Api/YourModule/`:

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

## Лучшие практики

1. **Держите модули независимыми** — избегайте прямых зависимостей между модулями
2. **Используйте события для коммуникации** — используйте Symfony EventDispatcher или Messenger для межмодульного взаимодействия
3. **Тонкие репозитории** — держите сложные запросы в сервисах, а не в репозиториях
4. **Типизируйте всё** — используйте strict types, type hints и типы возвращаемых значений
5. **Без комментариев** — код должен быть самодокументируемым; используйте PHPDoc только для указания типов
6. **Валидируйте рано** — используйте ограничения валидации Symfony на сущностях
7. **Используйте readonly** — помечайте сервисы как readonly, где это возможно (PHP 8.2+)

## Примеры модулей

См. модуль `src/Example/` для полноценного рабочего примера. Удалите его в продакшн-проектах.

## Типовые паттерны

### Read-only API

```php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ]
)]
```

### Кастомные операции

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

### Пагинация

```php
#[ApiResource(
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationMaximumItemsPerPage: 100,
)]
```

## Устранение неполадок

### Сервисы не найдены

Убедитесь, что `di.yaml` существует и следует правильному формату. Очистите кэш:

```bash
docker compose exec php bin/console cache:clear
```

### API-маршруты не появляются

Проверьте, что:
1. У сущности есть атрибут `#[ApiResource]`
2. Модуль находится в каталоге `src/`
3. Пространство имён соответствует структуре каталогов

### Ошибки схемы базы данных

Перегенерируйте миграцию:

```bash
docker compose exec php bin/console doctrine:migrations:diff
```

## Ресурсы

- [Symfony Dependency Injection](https://symfony.com/doc/current/service_container.html)
- [API Platform Documentation](https://api-platform.com/docs)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
