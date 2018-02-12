<?php
/**
 * Yii 2 config loader
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\yii\config;

use yii\base\BaseObject;

/**
 * Internal config object.
 *
 * It's used to store bootstrap and config parts in loaders.
 */
class Storage extends BaseObject
{
    /**
     * @var string[] config bootstrap (strings of a PHP code).
     */
    public $bootstrap = [];

    /**
     * @var array config array.
     */
    public $config = [];

    /**
     * Resets the properties to an empty array.
     */
    public function reset()
    {
        $this->bootstrap = $this->config = [];
    }
}
