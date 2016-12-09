<?php
/**
 * Yii 2 config loader.
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\config;

/**
 * PHP array config loader.
 */
class PhpArrayLoader extends ArrayLoader
{
    /**
     * {@inheritdoc}
     */
    public function loadFile($path)
    {
        /** @noinspection PhpIncludeInspection */
        return require $path;
    }
}
