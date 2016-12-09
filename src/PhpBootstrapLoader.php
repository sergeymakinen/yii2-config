<?php
/**
 * Yii 2 config loader.
 *
 * @see       https://github.com/sergeymakinen/yii2-config
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-config/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\config;

use yii\base\InvalidValueException;
use yii\helpers\StringHelper;

/**
 * PHP bootstrap config loader.
 */
class PhpBootstrapLoader extends Loader
{
    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        foreach ($this->resolveFiles() as $file) {
            $contents = file_get_contents($file);
            if (StringHelper::startsWith($contents, '<?php')) {
                $contents = mb_substr($contents, 5, null, 'UTF-8');
            } elseif (StringHelper::startsWith($contents, '<?')) {
                $contents = mb_substr($contents, 2, null, 'UTF-8');
            } else {
                throw new InvalidValueException("No PHP opening tag found in the beginning of '{$file}'.");
            }
            $this->storage->bootstrap[] = $contents;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        foreach ($this->resolveFiles() as $file) {
            /** @noinspection PhpIncludeInspection */
            require_once $file;
        }
    }
}
