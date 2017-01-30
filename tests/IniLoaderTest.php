<?php

namespace sergeymakinen\yii\config\tests;

use yii\helpers\ArrayHelper;

class IniLoaderTest extends PhpArrayLoaderTest
{
    protected function createConfig(array $config = [])
    {
        return parent::createConfig(ArrayHelper::merge([
            'files' => [
                'test' => [
                    'path' => 'test.ini',
                ],
            ],
        ], $config));
    }
}
