<?php

namespace sergeymakinen\tests\config\mocks;

use sergeymakinen\config\PhpBootstrapLoader;

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
