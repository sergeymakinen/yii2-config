<?php

namespace sergeymakinen\yii\config\tests;

/**
 * @preserveGlobalState disabled
 * @runTestsInSeparateProcesses
 */
class LoaderTest extends TestCase
{
    protected static $init1Tested = false;

    protected function setUp()
    {
        parent::setUp();
        $this->resetInitConfig();
    }

    public function testOk()
    {
        $this->assertEquals([
            'foo' => 'bar',
            'local' => true,
        ], $this->createConfig()->load());
        $this->assertInitConfigLoaded();
    }

    public function substitutionsProvider()
    {
        return [
            "'path' => '{env}/test.php'" => [[
                'files' => [
                    'test' => [
                        'path' => '{env}/test.php',
                    ],
                ],
            ]],
            "'dirs' => ['{tier}']" => [[
                'tier' => 'barenv',
                'dirs' => ['{tier}'],
                'files' => [
                    'test' => [
                        'tier' => 'barenv',
                    ],
                ],
            ]],
            "'path' => '{tier}/test.php'" => [[
                'tier' => 'barenv',
                'files' => [
                    'test' => [
                        'tier' => 'barenv',
                        'path' => '{tier}/test.php',
                    ],
                ],
            ]],
        ];
    }

    /**
     * @dataProvider substitutionsProvider
     *
     * @param array $config
     */
    public function testSubstitutions(array $config)
    {
        $result = [
            'foo' => 'bar',
            'local' => true,
        ];
        $this->assertEquals($result, $this->createConfig($config)->load());
        $this->assertInitConfigLoaded();
    }

    public function testNoTypeProperty()
    {
        $this->assertEquals([
            'foo' => 'bar',
            'local' => true,
        ], $this->createConfig([
            'files' => [
                'test' => [
                    'tier' => null,
                ],
            ],
        ])->load());
        $this->assertInitConfigLoaded();
    }

    public function testPathNotMatchingTypeProperty()
    {
        $this->assertEmpty($this->createConfig([
            'files' => [
                'test' => [
                    'tier' => 'type',
                ],
            ],
        ])->load());
        $this->assertInitConfigLoaded();
    }

    public function testNoEnvProperty()
    {
        $this->assertEquals([
            'foo' => 'bar',
            'local' => true,
        ], $this->createConfig([
            'files' => [
                'test' => [
                    'env' => null,
                ],
            ],
        ])->load());
        $this->assertInitConfigLoaded();
    }

    public function testNotEnvProperty()
    {
        $this->assertEquals([
            'bar' => 'foo',
        ], $this->createConfig([
            'env' => 'fooenv',
        ])->load());
        $this->assertInitConfigLoaded();
    }

    public function testPathNotMatchingEnvProperty()
    {
        $this->assertEmpty($this->createConfig([
            'files' => [
                'test' => [
                    'env' => 'env',
                ],
            ],
        ])->load());
        $this->assertInitConfigLoaded();
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testNoPathProperty()
    {
        $this->createConfig([
            'files' => [
                'test' => [
                    'path' => null,
                ],
            ],
        ])->load();
    }

    public function testOptionalPathNotFoundProperty()
    {
        $this->assertEmpty($this->createConfig([
            'files' => [
                'test' => [
                    'required' => false,
                    'path' => 'test1.php',
                ],
            ],
        ])->load());
        $this->assertInitConfigLoaded();
    }

    /**
     * @expectedException \sergeymakinen\yii\config\ConfigNotFoundException
     */
    public function testRequiredPathNotFoundProperty()
    {
        $this->createConfig([
            'files' => [
                'test' => [
                    'path' => 'test1.php',
                ],
            ],
        ])->load();
    }

    public function testNoLocalFile()
    {
        $this->assertEquals([
            'foo' => 'bar',
        ], $this->createConfig([
            'files' => [
                'test' => [
                    'enableLocal' => false,
                ],
            ],
        ])->load());
        $this->assertInitConfigLoaded();

        $this->assertEquals([
            'bar' => 'foo',
        ], $this->createConfig([
            'files' => [
                'test' => [
                    'path' => 'bar.php',
                ],
            ],
        ])->load());
        $this->assertInitConfigLoaded();
    }

    /**
     * @expectedException \sergeymakinen\yii\config\ConfigNotFoundException
     */
    public function testNoRequiredMainButLocalFile()
    {
        $this->createConfig([
            'files' => [
                'test' => [
                    'path' => 'foo.php',
                ],
            ],
        ])->load();
    }

    public function testNoOptionalMainButLocalFile()
    {
        $this->assertEmpty($this->createConfig([
            'files' => [
                'test' => [
                    'path' => 'foo.php',
                    'required' => false,
                ],
            ],
        ])->load());
        $this->assertInitConfigLoaded();
    }

    protected function resetInitConfig()
    {
        unset($_ENV['init1'], $_ENV['init2']);
    }

    protected function assertInitConfigLoaded()
    {
        if (!self::$init1Tested) {
            self::$init1Tested = true;
            $this->assertArrayHasKey('init1', $_ENV);
            $this->assertTrue($_ENV['init1']);
        }
        $this->assertArrayHasKey('init2', $_ENV);
        $this->assertTrue($_ENV['init2']);
        $this->resetInitConfig();
    }
}
