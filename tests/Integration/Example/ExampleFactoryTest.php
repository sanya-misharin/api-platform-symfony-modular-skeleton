<?php

declare(strict_types=1);

namespace App\Tests\Integration\Example;

use App\Example\Repository\ExampleRepository;
use App\Tests\ApiTestCase;
use App\Tests\Factory\ExampleFactory;
use Zenstruck\Foundry\Test\Factories;

final class ExampleFactoryTest extends ApiTestCase
{
    use Factories;

    public function testFactoryPersistsExamples(): void
    {
        self::bootKernel();

        ExampleFactory::createMany(3);

        $repository = self::getContainer()->get(ExampleRepository::class);
        self::assertCount(3, $repository->findAll());
    }

    public function testFactoryOverridesDefaults(): void
    {
        self::bootKernel();

        $example = ExampleFactory::createOne(['name' => 'Explicit name']);

        self::assertSame('Explicit name', $example->getName());
    }
}
