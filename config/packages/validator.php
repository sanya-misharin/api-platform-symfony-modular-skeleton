<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'validation' => [
            // Enables validator auto-mapping support.
            // For instance, basic validation constraints will be inferred from Doctrine's metadata.
            // 'auto_mapping' => [
            //     'App\Entity\' => [],
            // ],
        ],
    ]);

    if ('test' === $container->env()) {
        $container->extension('framework', [
            'validation' => [
                'not_compromised_password' => false,
            ],
        ]);
    }
};
