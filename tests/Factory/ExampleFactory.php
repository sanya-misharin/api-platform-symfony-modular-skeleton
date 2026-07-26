<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Example\Entity\Example;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Example>
 */
final class ExampleFactory extends PersistentProxyObjectFactory
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
