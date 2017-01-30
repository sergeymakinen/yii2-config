<?php
/**
 * Yii 2 config loader
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\yii\config;

/**
 * INI config loader.
 */
class IniLoader extends ArrayLoader
{
    /**
     * @inheritDoc
     */
    protected function loadFile($path)
    {
        return parse_ini_string(file_get_contents($path));
    }
}
