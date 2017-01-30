<?php

namespace sergeymakinen\yii\config\tests\helpers;

use sergeymakinen\yii\config\PhpBootstrapLoader;

class TestPhpBootstrapLoader extends PhpBootstrapLoader
{
    public function load()
    {
        foreach ($this->resolveFiles() as $file) {
            /** @noinspection PhpIncludeInspection */
            require $file;
        }
    }
}
