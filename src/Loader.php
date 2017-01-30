<?php
/**
 * Yii 2 config loader
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016-2017 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\yii\config;

use yii\base\Object;

/**
 * Base config loader.
 */
abstract class Loader extends Object
{
    /**
     * @var string|string[]|null a tier name or an array of tier names to match a tier name specified in [[Config]].
     *
     * If there're an array it will match *any of* specified values.
     * You can also use an exclamation mark (`!`) before a name to use a `not` match. Example:
     *
     * ```php
     * [
     *     'tier1',
     *     '!tier2',
     * ]
     * ```
     *
     * It matches if the tier name is `tier1` *or* **not** `tier2`.
     */
    public $tier;

    /**
     * @var string|string[]|null an environment name or an array of environment names to match an environment name specified in [[Config]].
     *
     * If there're an array it will match *any of* specified values.
     * You can also use an exclamation mark (`!`) before a name to use a `not` match. Example:
     *
     * ```php
     * [
     *     'env1',
     *     '!env2',
     * ]
     * ```
     *
     * It matches if the environment name is `env1` *or* **not** `env2`.
     */
    public $env;

    /**
     * @var string full path to a directory where [[Config]] will store its cached configs.
     */
    public $path;

    /**
     * @var bool whether the file is required.
     */
    public $required = true;

    /**
     * @var bool whether to look for a local config in addition to a main one.
     *
     * For example, if [[$enableLocal]] is `true` and a main config file name is `NAME.EXT`,
     * [[Config]] will also look for the `NAME-local.EXT` file.
     */
    public $enableLocal = true;

    /**
     * @var Config [[Config]] instance.
     */
    protected $config;

    /**
     * @var Storage internal config object instance.
     */
    protected $storage;

    /**
     * Creates a new Loader object.
     * @param Config $configObject current [[Config]] instance.
     * @param Storage $storage internal config object instance.
     * @param array $config name-value pairs that will be used to initialize the object properties.
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
     * @return string[] config file pathes.
     * @throws ConfigNotFoundException
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
                continue;
            }

            $files[] = $filePath;
            \Yii::trace("Loaded config file: '{$filePath}'", __METHOD__);
            $nonLocalFileCount++;
            if (!$this->enableLocal) {
                continue;
            }

            $filePath = $this->makeLocalPath($filePath);
            if (is_file($filePath)) {
                $files[] = $filePath;
                \Yii::trace("Loaded config file: '{$filePath}'", __METHOD__);
            }
        }
        if ($this->required && $nonLocalFileCount === 0) {
            throw new ConfigNotFoundException("The '{$this->path}' config file is required but not available.");
        }

        return $files;
    }

    /**
     * Returns a file path with a "-local" suffix.
     * @param string $path the file path.
     * @return string file path with a "-local" suffix.
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
     * @return bool whether the configuration allows to load this config.
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
     * @param mixed $value the value to be tested.
     * @param mixed $allowedValues the list of allowed values.
     * @return bool whether the value is in the list of allowed values.
     */
    protected function isValueAllowed($value, $allowedValues)
    {
        if ($allowedValues === null) {
            return true;
        }

        foreach ((array) $allowedValues as $allowedValue) {
            if (strpos($allowedValue, '!') === 0) {
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
