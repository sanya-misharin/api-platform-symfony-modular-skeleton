<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Example\Entity\Example;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Example>
 */
final class ExampleFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Example::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->words(3, true),
            'description' => self::faker()->optional()->sentence(),
        ];
    }
}
