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
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;

/**
 * Config loader.
 *
 * @property bool $isCached Whether the config is cached.
 */
class Config extends Object
{
    /**
     * Config base directory.
     *
     * @var string
     */
    public $configDir;

    /**
     * Config cache directory.
     *
     * @var string
     */
    public $cacheDir;

    /**
     * Config file name or name function.
     *
     * @var \Closure|string
     */
    public $cacheFileName = '{tier}-{env}-{hash}.php';

    /**
     * Enables caching parsed config files into a single PHP file.
     *
     * @var bool
     */
    public $enableCaching = false;

    /**
     * Config tier name.
     *
     * @var string
     */
    public $tier = 'common';

    /**
     * Config environment name.
     *
     * @var string
     */
    public $env = YII_ENV;

    /**
     * Directory pathes relative to the configDir to look for config files.
     *
     * @var string[]
     */
    public $dirs = [''];

    /**
     * Config file configurations.
     *
     * @var array|string[]|Loader[]
     */
    public $files;

    /**
     * Registered config file loaders per extension.
     *
     * @var array
     */
    public $loaders = [
        'ini' => 'sergeymakinen\config\IniLoader',
        'json' => 'sergeymakinen\config\JsonLoader',
        'php' => 'sergeymakinen\config\PhpArrayLoader',
        'yaml' => 'sergeymakinen\config\YamlLoader',
        'yml' => 'sergeymakinen\config\YamlLoader',
    ];

    /**
     * The internal config instance.
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Creates a new Config object.
     *
     * @param string $configDir
     * @param array $config
     */
    public function __construct($configDir, $config = [])
    {
        $this->configDir = $configDir;
        parent::__construct($config);
    }

    /**
     * Loads and returns the config from the configuration file.
     *
     * @param string $path
     * @param array $config
     *
     * @throws InvalidConfigException
     * @return static
     */
    public static function fromFile($path, array $config = [])
    {
        $fileConfig = (new static(StringHelper::dirname($path), [
            'files' => [
                [
                    'enableLocal' => false,
                    'path' => StringHelper::basename($path),
                ],
            ],
        ]))->load();
        $config = ArrayHelper::merge($fileConfig, $config);
        if (!isset($config['configDir']) || empty($config['configDir'])) {
            throw new InvalidConfigException("The 'configDir' key must not be empty.");
        }

        return new static($config['configDir'], $config);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (empty($this->configDir)) {
            throw new InvalidConfigException("The 'configDir' property must not be empty.");
        }

        $this->storage = new Storage();
    }

    /**
     * Returns whether the config is cached.
     *
     * @return bool
     */
    public function getIsCached()
    {
        return is_file($this->getCachePath());
    }

    /**
     * Compiles the config and writes it to the cache.
     */
    public function cache()
    {
        $this->compile();
        if (!is_dir($this->cacheDir)) {
            FileHelper::createDirectory($this->cacheDir);
        }
        $contents = array_merge(
            ['<?php'],
            array_map(function ($script) {
                return trim($script);
            }, $this->storage->bootstrap),
            ['return ' . VarDumper::export($this->storage->config) . ';']
        );
        file_put_contents(
            $this->getCachePath(),
            implode("\n\n", $contents) . "\n"
        );
    }

    /**
     * Removes the cached config.
     */
    public function flushCache()
    {
        if ($this->getIsCached()) {
            unlink($this->getCachePath());
        }
    }

    /**
     * Loads the config from/ignoring the cache and returns it.
     *
     * @return array
     */
    public function load()
    {
        if ($this->enableCaching) {
            if ($this->getIsCached()) {
                return $this->loadCached();
            } else {
                $this->cache();
                return $this->storage->config;
            }
        } else {
            return $this->loadFresh();
        }
    }

    /**
     * Returns the cache file full path.
     *
     * @throws InvalidConfigException
     * @return string
     */
    protected function getCachePath()
    {
        if (empty($this->cacheDir)) {
            throw new InvalidConfigException("The 'cacheDir' property must not be empty.");
        }

        $path = $this->cacheDir . '/';
        if ($this->cacheFileName instanceof \Closure) {
            $path .= call_user_func($this->cacheFileName);
        } else {
            $path .= $this->cacheFileName;
        }
        return strtr($path, [
            '{tier}' => $this->tier,
            '{env}' => $this->env,
            '{hash}' => md5($this->configDir),
        ]);
    }

    /**
     * Loads the config from the cache.
     *
     * @return array
     */
    protected function loadCached()
    {
        $token = "Loading config from cache: {$this->tier}";
        \Yii::beginProfile($token, __METHOD__);
        $this->storage->reset();
        /** @noinspection PhpIncludeInspection */
        $this->storage->config = require $this->getCachePath();
        \Yii::endProfile($token, __METHOD__);
        return $this->storage->config;
    }

    /**
     * Loads the config ignoring the cache.
     *
     * @return array
     */
    protected function loadFresh()
    {
        $token = "Loading config: {$this->tier}";
        \Yii::beginProfile($token, __METHOD__);
        $this->storage->reset();
        foreach ($this->resolveLoaders() as $file) {
            $file->load();
        }
        \Yii::endProfile($token, __METHOD__);
        return $this->storage->config;
    }

    /**
     * Resolves file configurations into Loader instances.
     *
     * @throws InvalidConfigException
     * @return Loader[]
     */
    protected function resolveLoaders()
    {
        $loaders = [];
        foreach ($this->files as $key => $file) {
            if (is_string($file)) {
                $file = $this->resolveShortcut($key, $file);
            }
            if (is_array($file)) {
                if (isset($file['class'])) {
                    $class = $file['class'];
                    unset($file['class']);
                } else {
                    if (!isset($file['path'])) {
                        throw new InvalidConfigException("The 'path' property must be set.");
                    }
                    $extension = pathinfo($file['path'], PATHINFO_EXTENSION);
                    if (!isset($this->loaders[$extension])) {
                        throw new InvalidConfigException("No loader available for the '$extension' extension.");
                    }
                    $class = $this->loaders[$extension];
                }
                $file = new $class($this, $this->storage, $file);
            }
            if ($file instanceof Loader) {
                $loaders[] = $file;
            } else {
                throw new InvalidConfigException("The 'files' property must be an array of Loader objects, or configuration arrays, or strings.");
            }
        }
        return $loaders;
    }

    /**
     * Maps a shortcut to an array for resolveFiles.
     *
     * @param mixed $key
     * @param string $value
     *
     * @return array
     */
    protected function resolveShortcut($key, $value)
    {
        $file = ['path' => $value];
        if (is_string($key)) {
            $config = explode('@', $key, 2);
            if ($config[0] !== '') {
                $tierEnvConfig = explode(':', $config[0], 2);
                if (isset($tierEnvConfig[1])) {
                    if ($tierEnvConfig[0] !== '') {
                        $file['tier'] = $tierEnvConfig[0];
                    }
                    if ($tierEnvConfig[1] !== '') {
                        $file['env'] = $tierEnvConfig[1];
                    }
                } else {
                    $file['env'] = $config[0];
                }
            }
            if (isset($config[1]) && $config[1] !== '') {
                $file['key'] = $config[1];
            }
        }
        return $file;
    }

    /**
     * Compiles all specified files according to their configurations.
     */
    protected function compile()
    {
        $token = "Compiling config: {$this->tier}";
        \Yii::beginProfile($token, __METHOD__);
        $this->storage->reset();
        foreach ($this->resolveLoaders() as $file) {
            $file->compile();
        }
        \Yii::endProfile($token, __METHOD__);
    }
}
