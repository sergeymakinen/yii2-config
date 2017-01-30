<?php
/**
 * Yii 2 config loader
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\yii\config;

use yii\helpers\Json;

/**
 * JSON config loader.
 */
class JsonLoader extends ArrayLoader
{
    /**
     * @inheritDoc
     */
    protected function loadFile($path)
    {
        return Json::decode(file_get_contents($path));
    }
}
