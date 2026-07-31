<?php

declare(strict_types=1);

namespace App\Tests\Integration\Health;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HealthTest extends WebTestCase
{
    public function testHealthReturnsOk(): void
    {
        $client = self::createClient();
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/health');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertJsonStringEqualsJsonString(
            '{"status":"ok"}',
            (string) $client->getResponse()->getContent(),
        );
    }
}
