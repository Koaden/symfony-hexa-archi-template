<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $router = [
        'default_uri' => '%env(DEFAULT_URI)%',
    ];

    if ($container->env() === 'prod') {
        $router['strict_requirements'] = null;
    }

    $container->extension('framework', [
        'router' => $router,
    ]);
};
