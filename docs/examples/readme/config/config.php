<?php

return [
    'configDir' => __DIR__,
    'cacheDir' => dirname(__DIR__) . '/runtime/config',
    'enableCaching' => YII_ENV_PROD,
    'dirs' => [
        '',
        '{env}',
    ],
    'files' => [
        [
            'class' => 'sergeymakinen\config\PhpBootstrapLoader',
            'path' => 'bootstrap.php',
        ],
        'common.php',
        '{tier}.php',
        'web:@components.urlManager.rules' => 'routes.php',
        '@components.log.targets' => 'logs.php',
        '@params' => 'params.php',
    ],
];
