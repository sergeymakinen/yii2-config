<?php

return [
    'configDir' => __DIR__,
    'cache' => [
        'class' => \sergeymakinen\yii\phpfilecache\Cache::className(),
        'cachePath' => '@tests/runtime/cache',
    ],
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
            'class' => sergeymakinen\yii\config\PhpBootstrapLoader::className(),
            'path' => 'init1.php',
        ],
        'init2' => [
            'class' => \sergeymakinen\yii\config\tests\helpers\TestPhpBootstrapLoader::className(),
            'path' => 'init2.php',
        ],
    ],
];
