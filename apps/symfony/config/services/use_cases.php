<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->private();

    $services->load('App\\Domain\\UseCase\\Command\\', '%kernel.project_dir%/src/Domain/UseCase/Command/**/Handler.php')
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->load('App\\Domain\\UseCase\\Query\\', '%kernel.project_dir%/src/Domain/UseCase/Query/**/Handler.php')
        ->tag('messenger.message_handler', ['bus' => 'query.bus']);
};
