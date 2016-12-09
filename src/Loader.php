<?php
/**
 * Yii 2 config loader.
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\config;

use yii\base\InvalidConfigException;
use yii\base\Object;

/**
 * Base config loader.
 */
abstract class Loader extends Object
{
    /**
     * A tier name or a list of tier types is compatible to be able to load this file.
     *
     * @var string|string[]|null
     */
    public $tier;

    /**
     * An environment name or a list of environment names is compatible to be able to load this file.
     *
     * @var string|string[]|null
     */
    public $env;

    /**
     * The file path.
     *
     * @var string
     */
    public $path;

    /**
     * Whether the file is required.
     *
     * @var bool
     */
    public $required = true;

    /**
     * Whether to try load a "-local" local file as well as a main file.
     *
     * @var bool
     */
    public $enableLocal = true;

    /**
     * Config instance.
     *
     * @var Config
     */
    protected $config;

    /**
     * Internal config object instance.
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Creates a new Loader object.
     *
     * @param Config $configObject
     * @param Storage $storage
     * @param array $config
     */
    public function __construct(Config $configObject, Storage $storage, $config = [])
    {
        $this->config = $configObject;
        $this->storage = $storage;
        parent::__construct($config);
    }

    /**
     * Compiles resolved files into the internal config object.
     */
    abstract public function compile();

    /**
     * Loads resolved files into the internal config object.
     */
    abstract public function load();

    /**
     * Resolves the current configuration into config file pathes.
     *
     * @throws InvalidConfigException
     * @return string[]
     */
    public function resolveFiles()
    {
        if (!$this->isAllowed()) {
            return [];
        }

        $nonLocalFileCount = 0;
        $files = [];
        foreach ($this->config->dirs as $path) {
            $filePath = $this->config->configDir . '/' . strtr($path . '/' . $this->path, [
                '{tier}' => $this->config->tier,
                '{env}' => $this->config->env,
            ]);
            if (!is_file($filePath)) {
                \Yii::trace("Skipped the non-existent '{$filePath}' config file.", __METHOD__);
                continue;
            }

            $files[] = $filePath;
            \Yii::trace("Loaded the '{$filePath}' config file.", __METHOD__);
            $nonLocalFileCount++;
            if (!$this->enableLocal) {
                continue;
            }

            $filePath = $this->makeLocalPath($filePath);
            if (is_file($filePath)) {
                $files[] = $filePath;
                \Yii::trace("Loaded the '{$filePath}' config file.", __METHOD__);
            } else {
                \Yii::trace("Skipped the non-existent '{$filePath}' config file.", __METHOD__);
            }
        }
        if ($this->required && $nonLocalFileCount === 0) {
            throw new ConfigNotFoundException("The '{$this->path}' config file is required but not available.");
        }

        return $files;
    }

    /**
     * Returns a file path with a "-local" suffix.
     *
     * @param string $path
     *
     * @return string
     */
    protected function makeLocalPath($path)
    {
        $extension = '.' . pathinfo($path, PATHINFO_EXTENSION);
        if ($extension !== '.') {
            $path = mb_substr($path, 0, -1 * mb_strlen($extension, 'UTF-8'), 'UTF-8');
        }
        $path .= '-local';
        if ($extension !== '.') {
            $path .= $extension;
        }
        return $path;
    }

    /**
     * Returns whether the configuration allows to load this config.
     *
     * @return bool
     */
    protected function isAllowed()
    {
        if (!$this->isValueAllowed($this->config->tier, $this->tier)) {
            return false;
        }

        if (!$this->isValueAllowed($this->config->env, $this->env)) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether the value is in the list of allowed values.
     *
     * @param mixed $value
     * @param mixed $allowedValues
     *
     * @return bool
     */
    protected function isValueAllowed($value, $allowedValues)
    {
        if (!isset($allowedValues)) {
            return true;
        }

        foreach ((array) $allowedValues as $allowedValue) {
            if (substr($allowedValue, 0, 1) === '!') {
                if (substr($allowedValue, 1) !== $value) {
                    return true;
                }
            } else {
                if ($allowedValue === $value) {
                    return true;
                }
            }
        }
        return false;
    }
}
