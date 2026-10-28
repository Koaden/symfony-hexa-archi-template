<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->private();

    $services->load('App\\Infrastructure\\Symfony\\Messenger\\Middleware\\', '%kernel.project_dir%/src/Infrastructure/Symfony/Messenger/Middleware/');

    $services->set('App\\Infrastructure\\Symfony\\Messenger\\CommandBus')
        ->bind('$messageBus', service('command.bus'));

    $services->alias('App\\Application\\MessageBus\\CommandBus', 'App\\Infrastructure\\Symfony\\Messenger\\CommandBus');

    $services->set('App\\Infrastructure\\Symfony\\Messenger\\QueryBus')
        ->bind('$messageBus', service('query.bus'));

    $services->alias('App\\Application\\MessageBus\\QueryBus', 'App\\Infrastructure\\Symfony\\Messenger\\QueryBus');

    $services->set('App\\Infrastructure\\Symfony\\Messenger\\EventBus')
        ->bind('$messageBus', service('event.bus'));

    $services->alias('App\\Application\\MessageBus\\EventBus', 'App\\Infrastructure\\Symfony\\Messenger\\EventBus');
};
