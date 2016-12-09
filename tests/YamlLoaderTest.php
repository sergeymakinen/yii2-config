<?php

namespace sergeymakinen\tests\config;

use yii\helpers\ArrayHelper;

class YamlLoaderTest extends PhpArrayLoaderTest
{
    protected function createConfig(array $config = [])
    {
        return parent::createConfig(ArrayHelper::merge([
            'files' => [
                'test' => [
                    'path' => 'test.yml',
                ],
            ],
        ], $config));
    }
}
