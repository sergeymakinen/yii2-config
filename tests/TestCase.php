<?php

namespace sergeymakinen\yii\config\tests;

use sergeymakinen\yii\config\Config;
use yii\helpers\ArrayHelper;

abstract class TestCase extends \sergeymakinen\yii\tests\TestCase
{
    protected function createConfig(array $config = [])
    {
        return new Config(ArrayHelper::merge($this->getDefaultConfig(), $config));
    }

    protected function getDefaultConfig()
    {
        return require __DIR__ . '/config/config.php';
    }
}
