<?php

return [
    'configDir' => __DIR__,
    'cacheDir' => __DIR__ . '/../runtime/config',
    'enableCaching' => YII_ENV_PROD,
    'files' => [
        /*
         * When YII_ENV_TEST is false:
         *
         * 'console.php'
         * 'console-local.php'
         * or
         * 'web.php'
         * 'web-local.php'
         */
        '!test' => '{tier}.php',
        /*
         * When YII_ENV_TEST is true:
         *
         * 'test.php'
         * 'test-local.php'
         */
        'test' => 'test.php',
        /*
         * When YII_ENV_TEST is false:
         *
         * 'db.php'
         * 'db-local.php'
         */
        '!test@components.db' => 'db.php',
        /*
         * When YII_ENV_TEST is true:
         *
         * test_db.php
         * test_db-local.php
         */
        'test@components.db' => 'test_db.php',
        /*
         * 'params.php'
         * 'params-local.php'
         */
        '@params' => 'params.php',
    ],
];
