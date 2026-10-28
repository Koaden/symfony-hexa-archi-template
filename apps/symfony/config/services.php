<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->import('services/application.php');
    $container->import('services/infrastructure.php');
    $container->import('services/use_cases.php');
    $container->import('services/repositories.php');
    $container->import('services/messenger.php');

    if (in_array($container->env(), ['dev', 'test'], true)) {
        $container->import('services/data_fixtures.php');
    }

    $services = $container->services()
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true);

    $services->set('App\\Domain\\Event\\EventsRegisterer');
};
