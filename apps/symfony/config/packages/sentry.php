<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if ($container->env() !== 'prod') {
        return;
    }

    $container->extension('sentry', [
        'dsn' => '%env(SENTRY_DSN)%',
        'environment' => '%kernel.environment%',
        'options' => [
            'traces_sample_rate' => 0.2,
            'profiles_sample_rate' => 0.0,
        ],
        'messenger' => [
            'enabled' => true,
        ],
        'tracing' => [
            'enabled' => true,
            'dbal' => [
                'enabled' => true,
            ],
            'twig' => [
                'enabled' => true,
            ],
            'http_client' => [
                'enabled' => true,
            ],
        ],
    ]);
};
