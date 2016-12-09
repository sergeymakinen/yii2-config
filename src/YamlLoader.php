<?php
/**
 * Yii 2 config loader.
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\config;

use Symfony\Component\Yaml\Yaml;

/**
 * YAML config loader.
 */
class YamlLoader extends ArrayLoader
{
    /**
     * {@inheritdoc}
     */
    public function loadFile($path)
    {
        return Yaml::parse(file_get_contents($path));
    }
}
