<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'messenger' => [
            'default_bus' => 'command.bus',
            'transports' => [
                'sync' => 'sync://',
            ],
            'buses' => [
                'command.bus' => [
                    'middleware' => [
                        'App\\Infrastructure\\Symfony\\Messenger\\Middleware\\DomainEventDispatcher',
                        'dispatch_after_current_bus',
                        'doctrine_transaction',
                    ],
                ],
                'query.bus' => null,
                'event.bus' => [
                    'default_middleware' => 'allow_no_handlers',
                    'middleware' => [
                        'dispatch_after_current_bus',
                        'doctrine_transaction',
                    ],
                ],
            ],
        ],
    ]);
};
