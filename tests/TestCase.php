<?php

namespace sergeymakinen\tests\config;

use sergeymakinen\config\Config;
use yii\helpers\ArrayHelper;

abstract class TestCase extends \sergeymakinen\tests\TestCase
{
    protected function createConfig(array $config = [])
    {
        $config = ArrayHelper::merge($this->getDefaultConfig(), $config);
        return new Config($config['configDir'], $config);
    }

    protected function getDefaultConfig()
    {
        return require __DIR__ . '/config/config.php';
    }
}
