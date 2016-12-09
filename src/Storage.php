<?php
/**
 * Yii 2 config loader.
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\config;

use yii\base\Object;

/**
 * Internal config object.
 *
 * It's used to store init and config parts in loaders.
 */
class Storage extends Object
{
    /**
     * Config bootstrap (strings of a PHP code).
     *
     * @var string[]
     */
    public $bootstrap = [];

    /**
     * Config array.
     *
     * @var array
     */
    public $config = [];

    /**
     * Resets the properties to an empty array.
     */
    public function reset()
    {
        $this->config = $this->bootstrap = [];
    }
}
