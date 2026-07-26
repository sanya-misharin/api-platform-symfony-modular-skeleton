<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Exclude the root-level config files (di.php, routing.php) — not classes;
    // real services live in subdirectories (Controller/, …).
    $services->load('App\\Health\\', './')
        ->exclude(['./*.php']);
};
