# Yii 2 config loader

Versatile config loader for Yii 2. You can define a single config definition file in your favorite language which will define a configuration for all your application tiers (`console`, `backend`, `frontend`, etc).

[![Code Quality](https://img.shields.io/scrutinizer/g/sergeymakinen/yii2-config.svg?style=flat-square)](https://scrutinizer-ci.com/g/sergeymakinen/yii2-config) [![Build Status](https://img.shields.io/travis/sergeymakinen/yii2-config.svg?style=flat-square)](https://travis-ci.org/sergeymakinen/yii2-config) [![Code Coverage](https://img.shields.io/codecov/c/github/sergeymakinen/yii2-config.svg?style=flat-square)](https://codecov.io/gh/sergeymakinen/yii2-config) [![SensioLabsInsight](https://img.shields.io/sensiolabs/i/bb50f2be-7108-4923-992e-b4ee636f0252.svg?style=flat-square)](https://insight.sensiolabs.com/projects/bb50f2be-7108-4923-992e-b4ee636f0252)

[![Packagist Version](https://img.shields.io/packagist/v/sergeymakinen/yii2-config.svg?style=flat-square)](https://packagist.org/packages/sergeymakinen/yii2-config) [![Total Downloads](https://img.shields.io/packagist/dt/sergeymakinen/yii2-config.svg?style=flat-square)](https://packagist.org/packages/sergeymakinen/yii2-config) [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Table of contents

- [Installation](#installation)
- [Usage](#usage)
- [Example config](#example-config)
- [Shortcuts](#shortcuts)
- [Supported config formats](#supported-config-formats)
  * [INI](#ini)
  * [JSON](#json)
  * [PHP array](#php-array)
  * [PHP bootstrap](#php-bootstrap)
  * [YAML](#yaml)
- [Short API documentation](#short-api-documentation)
  * [Config object](#config-object)
    + [$cacheDir](#cachedir)
    + [$cacheFileName](#cachefilename)
    + [$configDir](#configdir)
    + [$dirs](#dirs)
    + [$enableCaching](#enablecaching)
    + [$env](#env)
    + [$files](#files)
    + [$tier](#tier)
  * [Loader object](#loader-object)
    + [$enableLocal](#enablelocal)
    + [$env](#env-1)
    + [$path](#path)
    + [$required](#required)
    + [$tier](#tier-1)
- [Extending](#extending)

## Installation

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```bash
composer require "sergeymakinen/yii2-config:^1.0"
```

or add

```json
"sergeymakinen/yii2-config": "^1.0"
```

to the require section of your `composer.json` file.

## Usage

First you need to define your config: it may be a PHP array right in the file you plan to include it in but it's better to place it in a file which can be in any [supported format](#supported-config-formats). Just like it's done in the [example](#example).

Then your entry scripts have to be modified to load the config. It's how it can look like for a `console` tier [`yii` file](docs/examples/basic/yii) (consider a *tier* as a *type*) in a Yii 2 [basic project template](https://github.com/yiisoft/yii2-app-basic):

```php
#!/usr/bin/env php
<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = sergeymakinen\config\Config::fromFile(__DIR__ . '/config/config.php', ['tier' => 'console']);

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```

And for a `backend` tier [`backend/web/index.php` file](docs/examples/advanced/backend/web/index.php) in a Yii 2 [advanced application template](https://github.com/yiisoft/yii2-app-advanced):

```php
<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

$config = sergeymakinen\config\Config::fromFile(__DIR__ . '/../../common/config/config.php', ['tier' => 'backend']);

(new yii\web\Application($config))->run();
```

## Example config

Consider this [config](docs/examples/readme/config/config.php):

```php
<?php

return [
    'configDir' => __DIR__,
    'cacheDir' => dirname(__DIR__) . '/runtime/config',
    'enableCaching' => YII_ENV_PROD,
    'dirs' => [
        '',
        '{env}',
    ],
    'files' => [
        [
            'class' => 'sergeymakinen\config\PhpBootstrapLoader',
            'path' => 'bootstrap.php',
        ],
        'common.php',
        '{tier}.php',
        'web:@components.urlManager.rules' => 'routes.php',
        '@components.log.targets' => 'logs.php',
        '@params' => 'params.php',
    ],
];
```

`Config` will look for the following config files in `CONFIG_DIR` and `CONFIG_DIR/ENV` directories:

- `bootstrap.php` and `bootstrap-local.php` for a PHP code
- `common.php` and `common-local.php`
- `TIER.php` and `TIER-local.php`
- `routes.php` and `routes-local.php` when the tier is `web` will be merged as:

```php
[
    'components' => [
        'urlManager' => [
            'rules' => [
                // routes.php and routes-local.php contents
            ]
        ]
    ]
]
```

- `logs.php` and `logs-local.php` will be merged as:

```php
[
    'components' => [
        'log' => [
            'targets' => [
                // logs.php and logs-local.php contents
            ]
        ]
    ]
]
```

- `params.php` and `params-local.php` when the tier is `web` will be merged as:

```php
[
    'params' => [
        // params.php and params-local.php contents
    ]
]
```

## Shortcuts

As you can see in the [example section](#example-config) there are different ways to specify a config file configuration. To be able to write less and more compact, some common options can be written a single string instead of an array.

`'TIER:ENV@KEY' => 'PATH'` will be resolved as (you can omit any part you don't need):

```php
[
    'tier' => 'TIER',
    'env' => 'ENV',
    'key' => 'KEY',
    'path' => 'PATH',
]
```

Samples:

<table>
<thead>
<th>Shortcut</th>
<th>Result</th>
</thead>
<tbody>
<tr>
<td><code>'bar'</code></td>
<td><pre lang="php">
[
    'path' => 'bar',
]
</pre></td>
</tr>
<tr>
<td><code>'foo' => 'bar'</code></td>
<td><pre lang="php">
[
    'env' => 'foo',
    'path' => 'bar',
]
</pre></td>
</tr>
<tr>
<td><code>'foo@baz' => 'bar'</code></td>
<td><pre lang="php">
[
    'env' => 'foo',
    'key' => 'baz',
    'path' => 'bar',
]
</pre></td>
</tr>
<tr>
<td><code>'loren:foo@baz' => 'bar'</code></td>
<td><pre lang="php">
[
    'tier' => 'loren',
    'env' => 'foo',
    'key' => 'baz',
    'path' => 'bar',
]
</pre></td>
</tr>
</tbody>
</table>

## Supported config formats

### INI

**Extension**: `ini`

**Loader class**: `sergeymakinen\config\IniLoader`

**Example**:

```ini
[config]
class = yii\db\Connection
dsn = "mysql:host=localhost;dbname=yii2basic"
username = root
password = ""
charset = utf8
```

### JSON

**Extension**: `json`

**Loader class**: `sergeymakinen\config\JsonLoader`

**Example**:

```json
{
    "class": "yii\\db\\Connection",
    "dsn": "mysql:host=localhost;dbname=yii2basic",
    "username": "root",
    "password": "",
    "charset": "utf8"
}
```

### PHP array

**Extension**: `php`

**Loader class**: `sergeymakinen\config\PhpArrayLoader`

**Example**:

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

### PHP bootstrap

**Extension**: `php`

**Loader class**: `sergeymakinen\config\PhpBootstrapLoader`

**Attention**: you need to explicitly set the class name to use this loader:

```php
[
    'class' => 'sergeymakinen\config\PhpBootstrapLoader',
    'path' => 'mybootstrapfile.php',
    // ...
]
```

**Example**:

```php
<?php

Yii::$container->set(yii\grid\GridView::class, function ($container, $params, $config) {
    if (Yii::$app->controller instanceof yii\debug\controllers\DefaultController) {
        $defaults = [];
    } else {
        $defaults = [
            'layout' => '<div class="table-responsive">{items}</div><div class="grid-view-footer clearfix"><div class="pull-left">{summary}</div><div class="pull-right">{pager}</div></div>',
            'tableOptions' => ['class' => 'table table-striped'],
        ];
    }
    return new yii\grid\GridView(array_merge($defaults, $config));
});
```

### YAML

**Extension**: `yml`, `yaml`

**Loader class**: `sergeymakinen\config\YamlLoader`

**Attention**: you need to install the Symfony YAML library:

Either run

```bash
composer require "symfony/yaml:^2.8 || ^3.2"
```

or add

```json
"symfony/yaml": "^2.8 || ^3.2"
```

to the require section of your `composer.json` file.

**Example**:

```yaml
class: 'yii\db\Connection'
dsn: 'mysql:host=localhost;dbname=yii2basic'
username: root
password: ''
charset: utf8
```

## Short API documentation

### Config object

#### $cacheDir

Full path to a directory where `Config` will store cached configs.

**Required**: only if you use caching

**Default**: none

#### $cacheFileName

Cache file name or a `Closure` which returns the file name. Both variants can use the following substitutions:

| Name | Description
| --- | ---
| `{env}` | Config environment name (`$env`)
| `{hash}` | MD5 hash of the config dir path (`$configDir`)
| `{tier}` | Config tier name (`$tier`)

**Required**: no

**Default**: `'{tier}-{env}-{hash}.php'`

#### $configDir

Full path to a directory which `Config` will use as a base directory to look for configs.

**Required**: yes

**Default**: none

#### $dirs

Array of pathes relative to `$configDir`. `Config` will look for configs in each directory in the order they are defined. You can use the following substitutions:

| Name | Description
| --- | ---
| `{env}` | Config environment name (`$env`)
| `{tier}` | Config tier name (`$tier`)


**Required**: no

**Default**: `['']`

#### $enableCaching

Whether to enable caching.

The complete configuration will be analyzed and converted to a single PHP file which will be cared by a OPcode cacher so it will load almost immediately.

**Attention**: Config loads the cached file until it's removed manually or using `flushCache()`.

**Required**: no

**Default**: `false`

#### $env

Environment name (e. g. `dev`, `test`, `prod`).

**Required**: no

**Default**: `YII_ENV` constant value

#### $files

Array of:

- [`Loader` objects](#loader-object)
- array configurations of [`Loader` objects](#loader-object)
- [shortcuts](#shortcuts)

See an [example](#example-config).

**Required**: yes

**Default**: none

#### $tier

Tier name (e. g. `console`, `web`, `backend`, `frontend`).

**Required**: no

**Default**: `'common'`

### Loader object

#### $enableLocal

Whether to look for a local config in addition to a main one. For example, if `$enableLocal` is `true` and a main config file name is `NAME.EXT`, Config will also look for the `NAME-local.EXT` file.

**Required**: no

**Default**: `true`

#### $env

An environment name or an array of environment names to match an environment name specified in `Config`. If there're an array it will match *any of* specified values. You can also use an exclamation mark (`!`) before a name to use a `not` match. Example:

```php
[
    'env1',
    '!env2',
]
```

It matches if the environment name is `env1` *or* **not** `env2`.

**Required**: no

**Default**: none

#### $path

Full path to a directory where `Config` will store its cached configs.

**Required**: yes

**Default**: none

#### $required

Full path to a directory where Config will store its cached configs.

**Required**: no

**Default**: `true`

#### $tier

A tier name or an array of tier names to match a tier name specified in `Config`. If there're an array it will match *any of* specified values. You can also use an exclamation mark (`!`) before a name to use a `not` match. Example:

```php
[
    'tier1',
    '!tier2',
]
```

It matches if the tier name is `tier1` *or* **not** `tier2`.

**Required**: no

**Default**: none

## Extending

For example let's try to write a simple XML loader:

```php
use yii\helpers\Json;

class XmlLoader extends sergeymakinen\config\ArrayLoader
{
    /**
     * {@inheritdoc}
     */
    public function loadFile($path)
    {
        $xml = simplexml_load_string(file_get_contents($path), 'SimpleXMLElement', LIBXML_NOCDATA);
        return Json::decode(Json::encode($xml));
    }
}
```

If you wish to use the loader automatically for XML files then add the following entry to the `$resolvers` array of `Config`:

```php
'xml' => 'XmlLoader'
```
