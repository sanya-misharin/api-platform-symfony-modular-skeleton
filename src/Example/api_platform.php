<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('api_platform', [
        'mapping' => [
            'paths' => [
                '%kernel.project_dir%/src/Example/Entity',
            ],
        ],
    ]);
};
