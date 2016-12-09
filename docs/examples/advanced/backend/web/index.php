<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

$config = sergeymakinen\config\Config::fromFile(__DIR__ . '/../../common/config/config.php', ['tier' => 'backend']);

(new yii\web\Application($config))->run();
