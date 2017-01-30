<?php

namespace sergeymakinen\yii\config\tests;

class PhpArrayLoaderTest extends TestCase
{
    public function testCompile()
    {
        $loader = $this->createConfig([
            'enableCache' => true,
        ]);
        $loader->flushCache();
        $loader->cache();
        $this->assertEquals([
            'foo' => 'bar',
            'local' => true,
        ], $this->getInaccessibleProperty($loader, 'storage')->config);
    }

    public function keyPropertyProvider()
    {
        $result = [
            'foo' => 'bar',
            'local' => true,
        ];
        return [
            [
                [
                    'files' => [
                        'test' => [
                            'key' => 'foo.bar',
                        ],
                    ],
                ],
                ['foo' => ['bar' => $result]],
            ],
            [
                [
                    'files' => [
                        'test' => [
                            'key' => 'baz',
                        ],
                    ],
                ],
                ['baz' => $result],
            ],
            [
                [
                    'files' => [
                        'test' => [
                            'key' => '.',
                        ],
                    ],
                ],
                ['' => ['' => $result]],
            ],
        ];
    }

    /**
     * @dataProvider keyPropertyProvider
     *
     * @param array $config
     * @param array $expected
     */
    public function testKeyProperty(array $config, array $expected)
    {
        $this->assertEquals($expected, $this->createConfig($config)->load());
    }
}
