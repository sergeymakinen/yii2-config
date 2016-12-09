<?php

namespace sergeymakinen\tests\config;

use sergeymakinen\config\Config;
use sergeymakinen\config\Loader;
use sergeymakinen\config\PhpArrayLoader;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\VarDumper;

class ConfigTest extends TestCase
{
    public function testGetCachePath()
    {
        $loader = $this->createConfig();
        $hash = md5($loader->configDir);
        $this->assertEquals(
            realpath(\Yii::getAlias("@tests/runtime/config/console-test-barenv-{$hash}.php")),
            realpath($this->invokeInaccessibleMethod($loader, 'getCachePath'))
        );

        $this->assertEquals(
            realpath(\Yii::getAlias('@tests/runtime/config/foo')),
            realpath($this->invokeInaccessibleMethod($this->createConfig([
                'cacheFileName' => function () {
                    return 'foo';
                },
            ]), 'getCachePath'))
        );
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testLoadCachedNoCacheDir()
    {
        $this->createConfig([
            'cacheDir' => null,
            'enableCaching' => true,
        ])->load();
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testConfigDirNotSet()
    {
        $this->createConfig([
            'configDir' => null,
        ]);
    }

    public function shortcutsProvider()
    {
        return [
            "'bar.php'" => [[
                'shortcut' => ['bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'path' => 'bar.php',
                    'env' => null,
                    'key' => null,
                    'tier' => null,
                ],
            ]],
            "'foo' => 'bar.php'" => [[
                'shortcut' => ['foo' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => 'foo',
                    'key' => null,
                    'path' => 'bar.php',
                    'tier' => null,
                ],
            ]],
            "'tbz:foo' => 'bar.php'" => [[
                'shortcut' => ['tbz:foo' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => 'foo',
                    'key' => null,
                    'path' => 'bar.php',
                    'tier' => 'tbz',
                ],
            ]],
            "'foo@' => 'bar.php'" => [[
                'shortcut' => ['foo@' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => 'foo',
                    'key' => null,
                    'path' => 'bar.php',
                    'tier' => null,
                ],
            ]],
            "'tbz:foo@' => 'bar.php'" => [[
                'shortcut' => ['tbz:foo@' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => 'foo',
                    'key' => null,
                    'path' => 'bar.php',
                    'tier' => 'tbz',
                ],
            ]],
            "'foo@baz' => 'bar.php'" => [[
                'shortcut' => ['foo@baz' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => 'foo',
                    'key' => 'baz',
                    'path' => 'bar.php',
                    'tier' => null,
                ],
            ]],
            "'!tbz:foo@baz' => 'bar.php'" => [[
                'shortcut' => ['!tbz:foo@baz' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => 'foo',
                    'key' => 'baz',
                    'path' => 'bar.php',
                    'tier' => '!tbz',
                ],
            ]],
            "'@baz' => 'bar.php'" => [[
                'shortcut' => ['@baz' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => null,
                    'key' => 'baz',
                    'path' => 'bar.php',
                    'tier' => null,
                ],
            ]],
            "'tbz:@baz' => 'bar.php'" => [[
                'shortcut' => ['tbz:@baz' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => null,
                    'key' => 'baz',
                    'path' => 'bar.php',
                    'tier' => 'tbz',
                ],
            ]],
            "':@' => 'bar.php'" => [[
                'shortcut' => [':@' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => null,
                    'key' => null,
                    'path' => 'bar.php',
                    'tier' => null,
                ],
            ]],
            "'@' => 'bar.php'" => [[
                'shortcut' => ['@' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => null,
                    'key' => null,
                    'path' => 'bar.php',
                    'tier' => null,
                ],
            ]],
            "':' => 'bar.php'" => [[
                'shortcut' => [':' => 'bar.php'],
                'actual' => [
                    'class' => PhpArrayLoader::className(),
                    'env' => null,
                    'key' => null,
                    'path' => 'bar.php',
                    'tier' => null,
                ],
            ]],
        ];
    }

    /**
     * @dataProvider shortcutsProvider
     * @param array $testCase
     */
    public function testShortcuts(array $testCase)
    {
        if (!class_exists('yii\helpers\ReplaceArrayValue')) {
            $this->markTestSkipped('No ReplaceArrayValue class.');
            return;
        }

        $loader = $this->createConfig([
            'files' => new ReplaceArrayValue($testCase['shortcut']),
        ]);
        $loaders = $this->invokeInaccessibleMethod($loader, 'resolveLoaders');
        $this->assertCount(1, $loaders);
        /** @var Loader $loader */
        $loader = $loaders[0];
        $this->assertInstanceOf($testCase['actual']['class'], $loader);
        unset($testCase['actual']['class']);
        foreach ($testCase['actual'] as $name => $value) {
            self::assertSame($value, $loader->{$name}, VarDumper::export([
                'testCase' => $testCase,
                'name' => $name,
            ]));
        }
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testNoLoader()
    {
        $loader = $this->createConfig();
        unset($loader->loaders['php']);
        $loader->load();
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testWrongFilesEntry()
    {
        $loader = $this->createConfig();
        $loader->files[] = new \stdClass();
        $loader->load();
    }

    public function testCache()
    {
        $expected = '$_ENV[\'init1\'] = true;

$_ENV[\'init2\'] = true;

return [
    \'foo\' => \'bar\',
    \'local\' => true,
];';
        $loader = $this->createConfig();
        $loader->flushCache();
        $loader->cache();
        $path = $this->getCachePath($loader);
        $this->assertFileExists($path);
        $this->assertContains($expected, file_get_contents($path));

        $loader = $this->createConfig(['enableCaching' => true]);
        $this->assertTrue($loader->getIsCached());
        $this->assertEquals([
            'foo' => 'bar',
            'local' => true,
        ], $loader->load());
        $this->assertEmpty($this->getInaccessibleProperty($loader, 'storage')->bootstrap);

        $loader->flushCache();
        $loader = $this->createConfig(['enableCaching' => true]);
        $this->assertFalse($loader->getIsCached());
        $this->assertEquals([
            'foo' => 'bar',
            'local' => true,
        ], $loader->load());
        $this->assertNotEmpty($this->getInaccessibleProperty($loader, 'storage')->bootstrap);
    }

    public function testFromFileOk()
    {
        $expected = $this->getDefaultConfig();
        $config = Config::fromFile(\Yii::getAlias('@tests/config/config.php'));
        $actual = [];
        foreach (array_keys($expected) as $name) {
            $actual[$name] = $config->{$name};
        }
        $this->assertEquals($expected, $actual);
    }

    public function testFromFileYamlOverride()
    {
        $expected = $this->getDefaultConfig();
        $override = [
            'configDir' => $expected['configDir'],
            'cacheDir' => $expected['cacheDir'],
        ];
        $config = Config::fromFile(\Yii::getAlias('@tests/config/config.yml'), $override);
        $actual = [];
        foreach (array_keys($expected) as $name) {
            $actual[$name] = $config->{$name};
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testFromFileNoConfigDir()
    {
        Config::fromFile(\Yii::getAlias('@tests/config/empty-config.php'));
    }

    protected function getCachePath(Config $loader)
    {
        $hash = md5($loader->configDir);
        return \Yii::getAlias("@tests/runtime/config/console-test-barenv-{$hash}.php");
    }
}
