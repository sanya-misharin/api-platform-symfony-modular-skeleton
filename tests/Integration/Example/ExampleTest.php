<?php

declare(strict_types=1);

namespace App\Tests\Integration\Example;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Tests\ApiTestCase;

final class ExampleTest extends ApiTestCase
{
    public function testGetCollectionIsEmptyInitially(): void
    {
        static::createClient()->request('GET', '/api/examples');

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['totalItems' => 0]);
    }

    public function testCreateExample(): void
    {
        static::createClient()->request('POST', '/api/examples', [
            'json' => [
                'name' => 'First example',
                'description' => 'A demonstration resource',
            ],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            'name' => 'First example',
            'description' => 'A demonstration resource',
        ]);
    }

    public function testCreateFailsWithBlankName(): void
    {
        static::createClient()->request('POST', '/api/examples', [
            'json' => ['name' => ''],
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateFailsWithTooShortName(): void
    {
        static::createClient()->request('POST', '/api/examples', [
            'json' => ['name' => 'ab'],
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testGetItem(): void
    {
        $client = static::createClient();
        $iri = $this->createExample($client, 'Readable example');

        $client->request('GET', $iri);

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['name' => 'Readable example']);
    }

    public function testUpdateExample(): void
    {
        $client = static::createClient();
        $iri = $this->createExample($client, 'Before update');

        $client->request('PUT', $iri, [
            'json' => ['name' => 'After update'],
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['name' => 'After update']);
    }

    public function testDeleteExample(): void
    {
        $client = static::createClient();
        $iri = $this->createExample($client, 'Disposable example');

        $client->request('DELETE', $iri);
        self::assertResponseStatusCodeSame(204);

        $client->request('GET', $iri);
        self::assertResponseStatusCodeSame(404);
    }

    private function createExample(Client $client, string $name): string
    {
        $response = $client->request('POST', '/api/examples', [
            'json' => ['name' => $name],
        ]);

        return (string) $response->toArray()['@id'];
    }
}
