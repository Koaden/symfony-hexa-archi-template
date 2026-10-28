<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->private();

    $services->load('App\\Infrastructure\\Doctrine\\Repository\\', '%kernel.project_dir%/src/Infrastructure/Doctrine/Repository/*');

    $projectDir = dirname(__DIR__, 2);

    $mappings = [
        [$projectDir.'/src/Domain/Data/Collection', 'App\\Domain\\Data\\Collection\\', 'Collection', 'App\\Infrastructure\\Doctrine\\Repository\\', 'Repository'],
        [$projectDir.'/src/Domain/Repository', 'App\\Domain\\Repository\\', 'Repository', 'App\\Infrastructure\\Doctrine\\Repository\\', 'Repository'],
    ];

    foreach ($mappings as [$directory, $domainNamespace, $domainSuffix, $infrastructureNamespace, $infrastructureSuffix]) {
        foreach (glob($directory.'/*'.$domainSuffix.'.php') ?: [] as $file) {
            $name = basename($file, '.php');
            $base = substr($name, 0, -strlen($domainSuffix));
            $domainClass = $domainNamespace.$name;
            $infrastructureClass = $infrastructureNamespace.$base.$infrastructureSuffix;

            if (class_exists($infrastructureClass) || interface_exists($infrastructureClass)) {
                $container->services()->alias($domainClass, $infrastructureClass);
            }
        }
    }
};
