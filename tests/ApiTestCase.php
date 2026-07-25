<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Base class for API integration tests.
 *
 * Ensures the test database exists (once per class) and resets the schema
 * before every test so each case runs against a clean database. Boots and
 * shuts the kernel down so the test body can call {@see static::createClient()}.
 */
abstract class ApiTestCase extends BaseApiTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::ensureTestDatabaseExists();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetSchema();
    }

    protected function resetSchema(): void
    {
        self::bootKernel();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);

        if ([] !== $metadata) {
            $schemaTool->createSchema($metadata);
        }

        self::ensureKernelShutdown();
    }

    private static function ensureTestDatabaseExists(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->run(
            new ArrayInput([
                'command' => 'doctrine:database:create',
                '--if-not-exists' => true,
            ]),
            new NullOutput(),
        );

        self::ensureKernelShutdown();
    }
}
