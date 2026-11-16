<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if ($container->env() !== 'dev') {
        return;
    }

    $container->extension('web_profiler', [
        'toolbar' => true,
    ]);

    $container->extension('framework', [
        'profiler' => [
            'collect_serializer_data' => true,
        ],
    ]);
};
