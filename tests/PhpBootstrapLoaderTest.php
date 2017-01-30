<?php

namespace sergeymakinen\yii\config\tests;

use sergeymakinen\yii\config\PhpBootstrapLoader;
use yii\helpers\StringHelper;

class PhpBootstrapLoaderTest extends TestCase
{
    use CacheConfigProviderTrait;

    /**
     * @dataProvider cacheConfigProvider
     *
     * @param array $testConfig
     */
    public function testCompileOk(array $testConfig)
    {
        $config = $this->createConfig(array_merge(['enableCache' => true], $testConfig));
        $config->flushCache();
        $config->cache();
        $this->assertEquals([
            $this->loadPhpCode('@tests/config/init1.php', 5),
            $this->loadPhpCode('@tests/config/init2.php', 5),
        ], $this->getInaccessibleProperty($config, 'storage')->bootstrap);

        $config = $this->createConfig([
            'enableCache' => true,
            'files' => [
                [
                    'class' => PhpBootstrapLoader::className(),
                    'path' => 'closing-tag.php',
                ],
            ],
        ]);
        $config->cache();
        $this->assertEquals([
            $this->loadPhpCode('@tests/config/init1.php', 5),
            $this->loadPhpCode('@tests/config/init2.php', 5),
            $this->loadPhpCode('@tests/config/closing-tag.php', 5, -2),
        ], $this->getInaccessibleProperty($config, 'storage')->bootstrap);
    }

    /**
     * @dataProvider cacheConfigProvider
     * @expectedException \yii\base\InvalidValueException
     *
     * @param array $testConfig
     */
    public function testPlainFileCompileError(array $testConfig)
    {
        $config = $this->createConfig(array_merge([
            'files' => [
                [
                    'class' => PhpBootstrapLoader::className(),
                    'path' => 'plain.php',
                ],
            ],
        ], $testConfig));
        $config->cache();
    }

    /**
     * @dataProvider cacheConfigProvider
     * @expectedException \yii\base\InvalidValueException
     *
     * @param array $testConfig
     */
    public function testEchoTagCompileError(array $testConfig)
    {
        $config = $this->createConfig(array_merge([
            'files' => [
                [
                    'class' => PhpBootstrapLoader::className(),
                    'path' => 'echo.php',
                ],
            ],
        ], $testConfig));
        $config->cache();
    }

    /**
     * @dataProvider cacheConfigProvider
     * @expectedException \yii\base\InvalidValueException
     *
     * @param array $testConfig
     */
    public function testMixedPhpHtmlCompileError(array $testConfig)
    {
        $config = $this->createConfig(array_merge([
            'files' => [
                [
                    'class' => PhpBootstrapLoader::className(),
                    'path' => 'php-html.php',
                ],
            ],
        ], $testConfig));
        $config->cache();
    }

    protected function loadPhpCode($path, $index, $length = null)
    {
        return trim(StringHelper::byteSubstr(file_get_contents(\Yii::getAlias($path)), $index, $length));
    }
}
