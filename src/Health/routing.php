<?php

declare(strict_types=1);

use App\Health\Controller\HealthController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import(HealthController::class, 'attribute');
};
