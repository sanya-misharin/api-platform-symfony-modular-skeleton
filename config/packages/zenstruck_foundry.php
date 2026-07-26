<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// The bundle is only enabled in dev/test (see config/bundles.php).
return static function (ContainerConfigurator $container): void {
    if (\in_array($container->env(), ['dev', 'test'], true)) {
        $container->extension('zenstruck_foundry', [
            'persistence' => [
                // Flush only once per call of PersistentObjectFactory::create().
                'flush_once' => true,
            ],
        ]);
    }
};
