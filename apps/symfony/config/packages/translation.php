<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'default_locale' => 'en',
        'translator' => [
            'default_path' => '%kernel.project_dir%/translations',
            'providers' => [],
        ],
    ]);
};
