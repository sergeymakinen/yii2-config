<?php

namespace sergeymakinen\tests\config;

use sergeymakinen\config\PhpBootstrapLoader;
use yii\helpers\StringHelper;

class PhpBootstrapLoaderTest extends TestCase
{
    public function testCompileOk()
    {
        $loader = $this->createConfig();
        $loader->flushCache();
        $loader->cache();
        $this->assertEquals([
            $this->loadPhpCode('@tests/config/init1.php', 5),
            $this->loadPhpCode('@tests/config/init2.php', 5),
        ], $this->getInaccessibleProperty($loader, 'storage')->bootstrap);
    }

    /**
     * @expectedException \yii\base\InvalidValueException
     */
    public function testPlainFileCompileError()
    {
        $loader = $this->createConfig([
            'files' => [
                [
                    'class' => PhpBootstrapLoader::className(),
                    'path' => 'plain.php',
                ],
            ],
        ]);
        $loader->cache();
    }

    /**
     * @expectedException \yii\base\InvalidValueException
     */
    public function testEchoTagCompileError()
    {
        $loader = $this->createConfig([
            'files' => [
                [
                    'class' => PhpBootstrapLoader::className(),
                    'path' => 'echo.php',
                ],
            ],
        ]);
        $loader->cache();
    }

    /**
     * @expectedException \yii\base\InvalidValueException
     */
    public function testMixedPhpHtmlCompileError()
    {
        $loader = $this->createConfig([
            'files' => [
                [
                    'class' => PhpBootstrapLoader::className(),
                    'path' => 'php-html.php',
                ],
            ],
        ]);
        $loader->cache();
    }

    protected function loadPhpCode($path, $index)
    {
        return trim(StringHelper::byteSubstr(file_get_contents(\Yii::getAlias($path)), $index));
    }
}
