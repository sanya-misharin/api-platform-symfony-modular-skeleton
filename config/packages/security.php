<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

return static function (ContainerConfigurator $container): void {
    $container->extension('security', [
        'password_hashers' => [
            PasswordAuthenticatedUserInterface::class => 'auto',
        ],

        'providers' => [
            // Define your user providers here
            'users_in_memory' => ['memory' => null],
        ],

        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
            'main' => [
                'lazy' => true,
                // 'provider' => 'users_in_memory',
            ],
        ],

        // Easy way to control access for large sections of your site.
        // Note: Only the *first* access control that matches will be used.
        'access_control' => [
            // ['path' => '^/admin', 'roles' => 'ROLE_ADMIN'],
            // ['path' => '^/profile', 'roles' => 'ROLE_USER'],
        ],
    ]);

    if ('test' === $container->env()) {
        $container->extension('security', [
            'password_hashers' => [
                PasswordAuthenticatedUserInterface::class => [
                    'algorithm' => 'auto',
                    'cost' => 4,
                    'time_cost' => 3,
                    'memory_cost' => 10,
                ],
            ],
        ]);
    }
};
