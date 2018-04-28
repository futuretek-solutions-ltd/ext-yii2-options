<?php

namespace futuretek\options\compat;

/**
 * Compatibility class for use when migrating from version 1.x
 *
 * @package futuretek\options\compat
 * @author  Lukas Cerny <lukas.cerny@futuretek.cz>
 * @license https://www.apache.org/licenses/LICENSE-2.0.html Apache-2.0
 * @link    http://www.futuretek.cz
 */
class Option
{
    const TYPE_BOOL = 'B';
    const TYPE_DATETIME = 'D';
    const TYPE_EMAIL = 'E';
    const TYPE_FLOAT = 'F';
    const TYPE_INT = 'I';
    const TYPE_OPTION = 'O';
    const TYPE_PHONE = 'P';
    const TYPE_STRING = 'S';
    const TYPE_TEXT = 'X';
    const TYPE_HTML = 'H';
    const TYPE_TIME = 'T';
    const TYPE_URL = 'U';
    const TYPE_PASSWORD = 'W';

    /**
     * Get option value
     *
     * @param string $name Option name
     * @param string $context Option context
     * @param int|null $context_id Context ID
     * @param mixed|null $defaultValue Default value
     * @return null|string Option value or "NOT_SET" when option not found
     * @throws \yii\base\InvalidConfigException
     * @deprecated Please use Yii::$app->options->get() instead
     */
    public static function get($name, $context = 'Option', $context_id = null, $defaultValue = null)
    {
        if ($context === 'Option' && $context_id === null) {
            $context = null;
        }

        return \Yii::$app->get('options')->get($name, $context, $context_id, $defaultValue);
    }

    /**
     * Get all context options
     *
     * @param string $context Context
     * @param int $context_id Context ID
     * @return array Associative array of options (name => value)
     * @throws \yii\base\InvalidConfigException
     * @deprecated Please use Yii::$app->options->getAll() instead
     */
    public static function getAll($context = 'Option', $context_id = null)
    {
        if ($context === 'Option' && $context_id === null) {
            $context = null;
        }

        return \Yii::$app->get('options')->getAll($context, $context_id);
    }

    /**
     * Set option value / create option
     *
     * @param string $name Option name
     * @param string $value Option value
     * @param string $context Option context
     * @param int|null $context_id Context ID
     * @param string $type Option type (default: Option::TYPE_STRING)
     * @param bool $system Is this option system (default: false)
     * @return bool If option was set successfully
     * @throws \yii\base\InvalidConfigException
     * @deprecated Please use Yii::$app->options->set() instead
     */
    public static function set($name, $value, $context = 'Option', $context_id = null, $type = Option::TYPE_STRING, $system = false)
    {
        if ($context === 'Option' && $context_id === null) {
            $context = null;
        }

        return \Yii::$app->get('options')->set($name, $value, $context, $context_id);
    }
}