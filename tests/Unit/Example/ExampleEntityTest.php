<?php

declare(strict_types=1);

namespace App\Tests\Unit\Example;

use App\Example\Entity\Example;
use PHPUnit\Framework\TestCase;

final class ExampleEntityTest extends TestCase
{
    public function testConstructorInitializesCreatedAt(): void
    {
        $example = new Example();

        self::assertNull($example->getId());
        self::assertNull($example->getUpdatedAt());
        self::assertEqualsWithDelta(
            new \DateTimeImmutable()->getTimestamp(),
            $example->getCreatedAt()->getTimestamp(),
            5,
        );
    }

    public function testSetNameStoresValueAndTouchesUpdatedAt(): void
    {
        $example = new Example();
        $example->setName('Renamed');

        self::assertSame('Renamed', $example->getName());
        self::assertInstanceOf(\DateTimeImmutable::class, $example->getUpdatedAt());
    }

    public function testSetDescriptionAcceptsNull(): void
    {
        $example = new Example();
        $example->setDescription('A description');
        self::assertSame('A description', $example->getDescription());

        $example->setDescription(null);
        self::assertNull($example->getDescription());
    }
}
