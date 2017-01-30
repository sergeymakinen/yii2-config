<?php

namespace sergeymakinen\yii\config\tests;

use yii\caching\ArrayCache;
use yii\caching\FileCache;
use yii\helpers\ReplaceArrayValue;

trait CacheConfigProviderTrait
{
    /**
     * @var ArrayCache
     */
    protected static $arrayCache;

    public function cacheConfigProvider()
    {
        if (!class_exists('yii\helpers\ReplaceArrayValue')) {
            $this->markTestSkipped("No 'yii\\helpers\\ReplaceArrayValue' class.");
            return null;
        }

        if (static::$arrayCache === null) {
            static::$arrayCache = new ArrayCache();
        }
        return [
            ['PhpFileCache' => []],
            ['FileCache' => ['cache' => new ReplaceArrayValue([
                'class' => FileCache::className(),
                'cachePath' => '@tests/runtime/cache',
            ])]],
            ['ArrayCache' => ['cache' => new ReplaceArrayValue(static::$arrayCache)]],
        ];
    }
}
