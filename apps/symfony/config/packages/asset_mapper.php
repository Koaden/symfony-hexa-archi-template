<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $assetMapper = [
        'paths' => [
            'assets/',
        ],
        'missing_import_mode' => 'strict',
    ];

    if ($container->env() === 'prod') {
        $assetMapper['missing_import_mode'] = 'warn';
    }

    $container->extension('framework', [
        'asset_mapper' => $assetMapper,
    ]);
};
