<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->private();

    $services->load('App\\Infrastructure\\', '%kernel.project_dir%/src/Infrastructure/')
        ->exclude([
            '%kernel.project_dir%/src/Infrastructure/Symfony/Messenger/',
            '%kernel.project_dir%/src/Infrastructure/Doctrine/DataFixtures/',
            '%kernel.project_dir%/src/Infrastructure/Doctrine/Repository/',
        ]);
};
