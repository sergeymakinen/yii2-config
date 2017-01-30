<?php
/**
 * Yii 2 config loader
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016-2017 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\yii\config;

use sergeymakinen\yii\phpfilecache\Cache as PhpFileCache;
use sergeymakinen\yii\phpfilecache\ValueWithBootstrap;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\caching\Cache;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Config loader.
 *
 * @property bool $isCached whether the config is cached. This property is read-only.
 */
class Config extends Object
{
    /**
     * @var string full path to a base directory to look for configs.
     * You may use a path alias here.
     */
    public $configDir;

    /**
     * @var bool whether to enable caching.
     * The complete configuration will be analyzed and converted
     * to a single PHP file which will be cared by a OPcode cacher so it will load almost immediately.
     * @since 2.0
     */
    public $enableCache = false;

    /**
     * @var int number of seconds that a cached config can remain valid in a cache.
     * Use `0` to never expire.
     * @since 2.0
     */
    public $cacheDuration = 3600;

    /**
     * @var Cache|string|array the [[Cache]] object or the application component ID of the [[Cache]] object.
     * It can also be an array that is used to create a [[Cache]] instance.
     * @since 2.0
     */
    public $cache = [
        'class' => 'sergeymakinen\yii\phpfilecache\Cache',
        'cachePath' => '@yii/../../../runtime/cache',
    ];

    /**
     * @var bool|null whether to inject the cache config/instance into the main config.
     * By default the cache config will only be included if there are `id` & `basePath` keys
     * in the main config and no `cacheConfig` key in the `components` array.
     * @see $cache
     * @since 2.0
     */
    public $includeCacheConfig;

    /**
     * @var string|null tier name (e. g. `console`, `web`, `backend`, `frontend`).
     */
    public $tier;

    /**
     * @var string|null environment name (e. g. `dev`, `test`, `prod`).
     */
    public $env = YII_ENV;

    /**
     * @var string[] array of pathes relative to [[configDir]].
     * [[Config]] will look for configs in each directory in the order they are defined.
     * You can use the following substitutions:
     *
     * | Name | Description
     * | --- | ---
     * | `{env}` | Config environment name ([[env]]).
     * | `{tier}` | Config tier name ([[tier]]).
     */
    public $dirs = [''];

    /**
     * @var array|string[]|Loader[] config file configurations.
     * Array of:
     *
     * - [[Loader]] instances
     * - array that is used to create [[Loader]] instances
     * - shortcuts
     */
    public $files = [];

    /**
     * @var string[] registered config file loaders per extension.
     */
    public $loaders = [];

    /**
     * @var Storage the internal config instance.
     */
    protected $storage;

    /**
     * Loads and returns the [[Config]] instance from the configuration file.
     * @param string $path the configuration file path.
     * You may use a path alias here. Also the file may have any extension which is loadable by [[Config]] by default.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     * @return static [[Config]] instance.
     * @throws InvalidConfigException
     */
    public static function fromFile($path, array $config = [])
    {
        $fileConfig = (new static([
            'includeCacheConfig' => false,
            'configDir' => dirname($path),
            'tier' => $path,
            'files' => [
                [
                    'enableLocal' => false,
                    'path' => basename($path),
                ],
            ],
        ]))->load();
        return new static(ArrayHelper::merge($fileConfig, $config));
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->enableCache) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }
        if (empty($this->configDir)) {
            throw new InvalidConfigException('The "configDir" property must be set.');
        }

        $this->configDir = \Yii::getAlias($this->configDir);
        $this->storage = new Storage();
    }

    /**
     * Returns whether the config is cached.
     * @return bool whether the config is cached.
     */
    public function getIsCached()
    {
        return $this->cache->exists($this->calculateCacheKey());
    }

    /**
     * Compiles the config and writes it to the cache.
     * @return bool whether caching was successful.
     */
    public function cache()
    {
        $this->compile();
        if ($this->cache instanceof PhpFileCache) {
            $value = new ValueWithBootstrap($this->storage->config, implode("\n\n", $this->storage->bootstrap));
        } else {
            $value = [
                $this->storage->config,
                implode("\n\n", $this->storage->bootstrap),
            ];
        }
        return $this->cache->set($this->calculateCacheKey(), $value, $this->cacheDuration);
    }

    /**
     * Removes the cached config.
     * @return bool whether flushing was successful.
     */
    public function flushCache()
    {
        return $this->cache->delete($this->calculateCacheKey());
    }

    /**
     * Loads the config from/ignoring the cache and returns it.
     * @return array the config.
     */
    public function load()
    {
        if ($this->enableCache) {
            $config = $this->loadCached();
            if ($config === false) {
                $config = $this->loadFresh();
                $this->cache();
            }
        } else {
            $config = $this->loadFresh();
        }
        return $this->includeCacheConfig($config);
    }

    /**
     * Loads the config from the cache.
     * @return array|false the config or `false` if loading failed.
     */
    protected function loadCached()
    {
        $token = "Loading config from cache: '{$this->tier}'";
        \Yii::beginProfile($token, __METHOD__);
        $this->storage->reset();
        $cacheValue = $this->cache->get($this->calculateCacheKey());
        if ($cacheValue === false) {
            \Yii::endProfile($token, __METHOD__);
            return false;
        }

        if ($this->cache instanceof PhpFileCache) {
            $this->storage->config = $cacheValue;
        } else {
            $this->storage->config = $cacheValue[0];
            eval($cacheValue[1]);
        }
        \Yii::endProfile($token, __METHOD__);
        return $this->storage->config;
    }

    /**
     * Loads the config ignoring the cache.
     * @return array the config.
     */
    protected function loadFresh()
    {
        $token = "Loading config: '{$this->tier}'";
        \Yii::beginProfile($token, __METHOD__);
        $this->storage->reset();
        foreach ($this->resolveLoaders() as $file) {
            $file->load();
        }
        \Yii::endProfile($token, __METHOD__);
        return $this->storage->config;
    }

    /**
     * Resolves file configurations into [[Loader]] instances.
     * @return Loader[] [[Loader]] instances.
     * @throws InvalidConfigException
     */
    protected function resolveLoaders()
    {
        $availableLoaders = array_merge($this->defaultLoaders(), $this->loaders);
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
                        throw new InvalidConfigException('The "path" property must be set.');
                    }

                    $extension = pathinfo($file['path'], PATHINFO_EXTENSION);
                    if (!isset($availableLoaders[$extension])) {
                        throw new InvalidConfigException("No loader available for the '$extension' extension.");
                    }

                    $class = $availableLoaders[$extension];
                }
                $file = new $class($this, $this->storage, $file);
            }
            if ($file instanceof Loader) {
                $loaders[] = $file;
            } else {
                throw new InvalidConfigException('The "files" property must be an array of Loader objects, or configuration arrays, or strings.');
            }
        }
        return $loaders;
    }

    /**
     * Maps a shortcut to an array for [[resolveLoaders()]].
     * @param mixed $key the array entry key.
     * @param string $value the array entry value.
     * @return array a configuration array.
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
        $token = "Compiling config: '{$this->tier}'";
        \Yii::beginProfile($token, __METHOD__);
        $this->storage->reset();
        foreach ($this->resolveLoaders() as $file) {
            $file->compile();
        }
        \Yii::endProfile($token, __METHOD__);
    }

    /**
     * Returns an array of default loaders available.
     * @return string[] default loaders.
     * @since 2.0
     */
    protected function defaultLoaders()
    {
        return [
            'ini' => 'sergeymakinen\yii\config\IniLoader',
            'json' => 'sergeymakinen\yii\config\JsonLoader',
            'php' => 'sergeymakinen\yii\config\PhpArrayLoader',
            'yaml' => 'sergeymakinen\yii\config\YamlLoader',
            'yml' => 'sergeymakinen\yii\config\YamlLoader',
        ];
    }

    /**
     * Calculates and returns a key based on some [[Config]] parameters.
     * @return array the cache key.
     * @since 2.0
     */
    protected function calculateCacheKey()
    {
        return [
            $this->tier,
            $this->env,
            md5($this->configDir),
        ];
    }

    /**
     * Merges the config with the cache config and returns it.
     * @param array $config
     * @return array
     */
    private function includeCacheConfig(array $config)
    {
        if (
            $this->includeCacheConfig === true
            || (
                $this->includeCacheConfig === null
                && isset($config['id'], $config['basePath'])
                && !isset($config['components']['configCache'])
            )
        ) {
            $config['components']['configCache'] = $this->cache;
        }
        return $config;
    }
}
