<?php

return [
    'configDir' => __DIR__,
    'cacheDir' => __DIR__ . '/../runtime/config',
    'tier' => 'console-test',
    'env' => 'barenv',
    'dirs' => [
        '',
        '{env}',
    ],
    'files' => [
        'test' => [
            'tier' => 'console-test',
            'env' => 'barenv',
            'path' => 'test.php',
        ],
        'skip' => [
            'env' => '!barenv',
            'path' => 'barenv/bar.php',
        ],
        'init1' => [
            'class' => sergeymakinen\config\PhpBootstrapLoader::className(),
            'path' => 'init1.php',
        ],
        'init2' => [
            'class' => sergeymakinen\tests\config\mocks\TestPhpBootstrapLoader::className(),
            'path' => 'init2.php',
        ],
    ],
];
