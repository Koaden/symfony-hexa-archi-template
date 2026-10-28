<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('doctrine_migrations', [
        'enable_service_migrations' => true,
        'migrations_paths' => [
            'App\\Infrastructure\\Doctrine\\Migrations' => '%kernel.project_dir%/src/Infrastructure/Doctrine/Migrations',
        ],
        'enable_profiler' => false,
    ]);
};
