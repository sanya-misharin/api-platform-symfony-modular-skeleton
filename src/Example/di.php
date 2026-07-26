<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Exclude Entity/ (ORM-managed, not services) and the root-level config files
    // (di.php, api_platform.php, …) which are not classes — otherwise a
    // classmap-authoritative autoloader (prod build) fails trying to resolve them.
    $services->load('App\\Example\\', './')
        ->exclude(['./Entity/', './*.php']);
};
