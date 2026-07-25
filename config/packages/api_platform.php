<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('api_platform', [
        'title' => 'API Platform Skeleton',
        'version' => '1.0.0',
        'description' => 'Modular API Platform backend template',
        'defaults' => [
            'stateless' => true,
            'cache_headers' => [
                'vary' => ['Content-Type', 'Authorization', 'Origin'],
            ],
        ],
        'formats' => [
            'jsonld' => ['mime_types' => ['application/ld+json']],
            'json' => ['mime_types' => ['application/json']],
            'html' => ['mime_types' => ['text/html']],
        ],
    ]);
};
