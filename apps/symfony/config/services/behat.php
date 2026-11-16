<?php

declare(strict_types=1);

use App\Tests\Behat\Context\DatabaseContext;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->set(DatabaseContext::class)
        ->public();
};
