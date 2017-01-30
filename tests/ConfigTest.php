<?php

namespace sergeymakinen\yii\config\tests;

use sergeymakinen\yii\config\Config;
use sergeymakinen\yii\config\Loader;
use sergeymakinen\yii\config\PhpArrayLoader;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\VarDumper;

class ConfigTest extends TestCase
{
    use CacheConfigProviderTrait;

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
     *
     * @param array $testCase
     */
    public function testShortcuts(array $testCase)
    {
        if (!class_exists('yii\helpers\ReplaceArrayValue')) {
            $this->markTestSkipped("No 'yii\\helpers\\ReplaceArrayValue' class.");
            return;
        }

        $config = $this->createConfig([
            'files' => new ReplaceArrayValue($testCase['shortcut']),
        ]);
        $loaders = $this->invokeInaccessibleMethod($config, 'resolveLoaders');
        $this->assertCount(1, $loaders);
        /** @var Loader $config */
        $config = $loaders[0];
        $this->assertInstanceOf($testCase['actual']['class'], $config);
        unset($testCase['actual']['class']);
        foreach ($testCase['actual'] as $name => $value) {
            $this->assertSame($value, $config->{$name}, VarDumper::export([
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
        $config = $this->createConfig([
            'files' => [
                'test' => [
                    'path' => 'path.txt',
                ],
            ],
        ]);
        $config->load();
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testWrongFilesEntry()
    {
        $config = $this->createConfig();
        $config->files[] = new \stdClass();
        $config->load();
    }

    /**
     * @dataProvider cacheConfigProvider
     *
     * @param array $testConfig
     */
    public function testCache(array $testConfig)
    {
        $finalConfig = array_merge(['enableCache' => true], $testConfig);
        $config = $this->createConfig($finalConfig);
        $config->flushCache();
        $this->assertFalse($config->getIsCached());
        $this->assertFalse($config->cache->exists($this->getCacheKey($config)));
        $this->assertTrue($config->cache());
        $this->assertTrue($config->getIsCached());
        $this->assertTrue($config->cache->exists($this->getCacheKey($config)));

        $config = $this->createConfig($finalConfig);
        $this->assertTrue($config->getIsCached());
        $this->assertEquals([
            'foo' => 'bar',
            'local' => true,
        ], $config->load());
        $this->assertEmpty($this->getInaccessibleProperty($config, 'storage')->bootstrap);

        $config->flushCache();
        $config = $this->createConfig($finalConfig);
        $this->assertFalse($config->getIsCached());
        $this->assertEquals([
            'foo' => 'bar',
            'local' => true,
        ], $config->load());
        $this->assertNotEmpty($this->getInaccessibleProperty($config, 'storage')->bootstrap);
    }

    public function testFromFileOk()
    {
        $expected = $this->getDefaultConfig();
        $config = Config::fromFile('@tests/config/config.php');
        $actual = [];
        foreach (array_keys($expected) as $name) {
            $actual[$name] = $config->{$name};
        }
        $this->assertEquals($expected, $actual);
    }

    public function testFromFileYamlOverride()
    {
        $expected = $this->getDefaultConfig();
        $override = ['configDir' => $expected['configDir']];
        $config = Config::fromFile('@tests/config/config.yml', $override);
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
        Config::fromFile('@tests/config/empty-config.php');
    }

    public function testCalculateCacheKey()
    {
        $config = $this->createConfig();
        $this->assertEquals([
            $config->tier,
            $config->env,
            md5($config->configDir),
        ], $this->getCacheKey($config));
    }

    public function testIncludeCacheConfig()
    {
        $expected = [
            'foo' => 'bar',
            'local' => true,
        ];
        $this->assertEquals($expected, $this->createConfig()->load());

        $this->assertEquals($expected, $this->createConfig(['includeCacheConfig' => false])->load());

        $config = $this->createConfig(['includeCacheConfig' => true]);
        $expected['components']['configCache'] = $config->cache;
        $this->assertEquals($expected, $config->load());
    }

    protected function getCacheKey(Config $config)
    {
        return $this->invokeInaccessibleMethod($config, 'calculateCacheKey');
    }
}
