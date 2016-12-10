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
            $contents = $this->getPurePhp(file_get_contents($file));
            if ($contents === false) {
                throw new InvalidValueException("The '{$file}' file is not a pure PHP file.");
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

    /**
     * Returns a pure PHP code from the input string or false if the string is not a pure PHP file.
     *
     * @param string $contents
     *
     * @return string|false
     * @since 1.1
     */
    protected function getPurePhp($contents)
    {
        $tokens = token_get_all($contents);
        $tokenCount = count($tokens);
        if (
            $tokenCount === 0
            || $this->isDesiredToken($tokens[0], [T_INLINE_HTML, T_OPEN_TAG_WITH_ECHO])
            || $this->isDesiredToken($tokens[$tokenCount - 1], T_INLINE_HTML)
        ) {
            return false;
        }

        for ($index = 1; $index < $tokenCount; $index++) {
            if ($this->isDesiredToken($tokens[$index], [T_INLINE_HTML, T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO])) {
                return false;
            }
        }

        return trim(StringHelper::byteSubstr($contents, StringHelper::byteLength($tokens[0][1])));
    }

    /**
     * Returns whether the token is of a desired type.
     *
     * @param array|string $token
     * @param int|int[] $type
     *
     * @return bool
     */
    private function isDesiredToken($token, $type)
    {
        if (is_array($token) && in_array($token[0], (array) $type, true)) {
            return true;
        } else {
            return false;
        }
    }
}
