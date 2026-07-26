<?php

declare(strict_types=1);

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('doctrine', [
        'dbal' => [
            'url' => '%env(resolve:DATABASE_URL)%',

            // IMPORTANT: You MUST configure your server version, either here
            // or in the DATABASE_URL env var (see .env file).
            // 'server_version' => '16',

            'profiling_collect_backtrace' => '%kernel.debug%',
        ],
        'orm' => [
            'validate_xml_mapping' => true,
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'identity_generation_preferences' => [
                PostgreSQLPlatform::class => 'identity',
            ],
            'auto_mapping' => true,
            // Single mapping for the whole modular tree: every module's entities live
            // under src/<Module>/Entity with the shared App\ prefix, so one attribute
            // mapping over src/ registers them all — a new module needs no ORM config.
            'mappings' => [
                'App' => [
                    'type' => 'attribute',
                    'dir' => '%kernel.project_dir%/src',
                    'prefix' => 'App',
                    'is_bundle' => false,
                ],
            ],
        ],
    ]);

    if ('test' === $container->env()) {
        $container->extension('doctrine', [
            'dbal' => [
                // "TEST_TOKEN" is typically set by ParaTest
                'dbname_suffix' => '_test%env(default::TEST_TOKEN)%',
            ],
        ]);
    }

    if ('prod' === $container->env()) {
        $container->extension('doctrine', [
            'orm' => [
                'query_cache_driver' => [
                    'type' => 'pool',
                    'pool' => 'doctrine.system_cache_pool',
                ],
                'result_cache_driver' => [
                    'type' => 'pool',
                    'pool' => 'doctrine.result_cache_pool',
                ],
            ],
        ]);

        $container->extension('framework', [
            'cache' => [
                'pools' => [
                    'doctrine.result_cache_pool' => ['adapter' => 'cache.app'],
                    'doctrine.system_cache_pool' => ['adapter' => 'cache.system'],
                ],
            ],
        ]);
    }
};
