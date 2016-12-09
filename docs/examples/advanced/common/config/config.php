<?php

return [
    'configDir' => __DIR__ . '/../..',
    'cacheDir' => __DIR__ . '/../../console/runtime/config',
    'enableCaching' => YII_ENV_PROD,
    /*
     * 'common/config/...'
     *
     * 'backend/config/...'
     * or
     * 'console/config/...'
     * or
     * 'frontend/config/...'
     */
    'dirs' => [
        'common/config',
        '{tier}/config',
    ],
    'files' => [
        /*
         * 'common/config/bootstrap.php'
         * 'common/config/bootstrap-local.php'
         *
         * 'backend/config/bootstrap.php'
         * 'backend/config/bootstrap-local.php'
         * or
         * 'console/config/bootstrap.php'
         * 'console/config/bootstrap-local.php'
         * or
         * 'frontend/config/bootstrap.php'
         * 'frontend/config/bootstrap-local.php'
         */
        [
            'class' => 'sergeymakinen\config\PhpBootstrapLoader',
            'path' => 'bootstrap.php',
        ],
        /*
         * When YII_ENV_TEST is false:
         *
         * 'common/config/main.php'
         * 'common/config/main-local.php'
         *
         * 'backend/config/main.php'
         * 'backend/config/main-local.php'
         * or
         * 'console/config/main.php'
         * 'console/config/main-local.php'
         * or
         * 'frontend/config/main.php'
         * 'frontend/config/main-local.php'
         */
        '!test' => 'main.php',
        /*
         * When tier is 'console' and YII_ENV_TEST is true:
         *
         * 'common/config/main.php'
         * 'common/config/main-local.php'
         *
         * 'console/config/main.php'
         * 'console/config/main-local.php'
         */
        'console:test' => 'main.php',
        /*
         * When tier is 'backend' or 'frontend' and YII_ENV_TEST is true:
         *
         * 'common/config/test.php'
         * 'common/config/test-local.php'
         *
         * 'backend/config/test.php'
         * 'backend/config/test-local.php'
         * or
         * 'frontend/config/test.php'
         * 'frontend/config/test-local.php'
         */
        '!console:test' => 'test.php',
        /*
         * 'common/config/params.php'
         * 'common/config/params-local.php'
         *
         * 'backend/config/params.php'
         * 'backend/config/params-local.php'
         * or
         * 'console/config/params.php'
         * 'console/config/params-local.php'
         * or
         * 'frontend/config/params.php'
         * 'frontend/config/params-local.php'
         */
        '@params' => 'params.php',
    ],
];
