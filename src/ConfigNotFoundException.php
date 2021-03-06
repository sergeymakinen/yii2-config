<?php
/**
 * Yii 2 config loader
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\yii\config;

use yii\base\Exception;

/**
 * An exception caused by a config file not found.
 */
class ConfigNotFoundException extends Exception
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Config File Not Found';
    }
}
