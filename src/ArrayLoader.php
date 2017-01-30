<?php
/**
 * Yii 2 config loader
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\yii\config;

use yii\helpers\ArrayHelper;

/**
 * Parseable config loader.
 */
abstract class ArrayLoader extends Loader
{
    /**
     * @var string|null the key in a dot notation format to insert into the config.
     */
    public $key;

    /**
     * @inheritDoc
     */
    public function compile()
    {
        $this->loadFiles();
    }

    /**
     * @inheritDoc
     */
    public function load()
    {
        $this->loadFiles();
    }

    /**
     * Returns the config for the resolved file.
     * @param string $path the file path.
     * @return array the config.
     */
    abstract protected function loadFile($path);

    /**
     * Returns an array with the value set in the key specified in a dot notation format.
     * @param string $key the key name in a dot notation format.
     * @param mixed $value the value.
     * @return array a result array.
     */
    protected function createArrayByKey($key, $value)
    {
        $array = [];
        $current = &$array;
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            $current[$key] = [];
            $current = &$current[$key];
        }
        $current[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * Loads resolved files into the internal config object.
     */
    private function loadFiles()
    {
        foreach ($this->resolveFiles() as $file) {
            $value = $this->loadFile($file);
            if ($this->key !== null) {
                $value = $this->createArrayByKey($this->key, $value);
            }
            $this->storage->config = ArrayHelper::merge($this->storage->config, $value);
        }
    }
}
