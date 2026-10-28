<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $framework = [
        'secret' => '%env(APP_SECRET)%',
        'session' => true,
    ];

    if ($container->env() === 'test') {
        $framework['test'] = true;
        $framework['session'] = [
            'storage_factory_id' => 'session.storage.factory.mock_file',
        ];
    }

    $container->extension('framework', $framework);
};
