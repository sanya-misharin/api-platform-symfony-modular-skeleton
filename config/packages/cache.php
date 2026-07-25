<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'cache' => [
            // Unique name of your app: used to compute stable namespaces for cache keys.
            // 'prefix_seed' => 'your_vendor_name/app_name',

            // The "app" cache stores to the filesystem by default.
            // The data in this cache should persist between deploys.
            // Other options include Redis ('cache.adapter.redis') or APCu ('cache.adapter.apcu').
            // 'app' => 'cache.adapter.redis',
            // 'default_redis_provider' => 'redis://localhost',

            // Namespaced pools use the above "app" backend by default.
            // 'pools' => [
            //     'my.dedicated.cache' => null,
            // ],
        ],
    ]);
};
