<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $di->import('../src/**/{di}.{php,xml,yaml,yml}');
    $di->import("../src/**/{di}_{$di->env()}".'.{php,xml,yaml,yml}');
    $di->import('../src/**/{doctrine}.{php,xml,yaml,yml}');
    $di->import("../src/**/{doctrine}_{$di->env()}".'.{php,xml,yaml,yml}');
    $di->import('../src/**/{api_platform}.{php,xml,yaml,yml}');
    $di->import("../src/**/{api_platform}_{$di->env()}".'.{php,xml,yaml,yml}');
};
