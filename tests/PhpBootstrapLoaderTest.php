<?php

namespace sergeymakinen\tests\config;

use sergeymakinen\config\PhpBootstrapLoader;

class PhpBootstrapLoaderTest extends TestCase
{
    public function testCompileOk()
    {
        $loader = $this->createConfig();
        $loader->flushCache();
        $loader->cache();
        $this->assertEquals([
            mb_substr(file_get_contents(\Yii::getAlias('@tests/config/init1.php')), 5, null, 'UTF-8'),
            mb_substr(file_get_contents(\Yii::getAlias('@tests/config/init2.php')), 5, null, 'UTF-8'),
        ], $this->getInaccessibleProperty($loader, 'storage')->bootstrap);

        $loader = $this->createConfig([
            'files' => [
                [
                    'class' => PhpBootstrapLoader::className(),
                    'path' => 'short.php',
                ],
            ],
        ]);
        $loader->cache();
        $this->assertEquals([
            mb_substr(file_get_contents(\Yii::getAlias('@tests/config/init1.php')), 5, null, 'UTF-8'),
            mb_substr(file_get_contents(\Yii::getAlias('@tests/config/init2.php')), 5, null, 'UTF-8'),
            mb_substr(file_get_contents(\Yii::getAlias('@tests/config/short.php')), 2, null, 'UTF-8'),
        ], $this->getInaccessibleProperty($loader, 'storage')->bootstrap);
    }

    /**
     * @expectedException \yii\base\InvalidValueException
     */
    public function testCompileError()
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
}
