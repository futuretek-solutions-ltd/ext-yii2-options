<?php

namespace futuretek\options;

use yii\base\InvalidParamException;
use yii\base\Object;

/**
 * Class OptionData
 *
 * @package futuretek\options
 * @author  Lukas Cerny <lukas.cerny@futuretek.cz>
 * @license Apache-2.0
 * @link    http://www.futuretek.cz
 */
class OptionData extends Object
{
    const TYPE_ARRAY = 'A';
    const TYPE_CALLABLE = 'C';

    /**
     * @var string
     */
    public $type;
    /**
     * @var array|callable Data definition
     * * For type `OptionData::TYPE_ARRAY` pass associative array containing keys `id` and `name`
     * * For type `OptionData::TYPE_CALLABLE` pass any callable returning associative array containing keys `id` and `name`
     */
    public $data;

    /**
     * Get option data
     *
     * @return array
     * @throws \yii\base\InvalidParamException
     * @throws \RuntimeException
     */
    public function getData()
    {
        switch ($this->type) {
            case self::TYPE_ARRAY:
                return $this->data;
            case self::TYPE_CALLABLE:
                if (!is_callable($this->data)) {
                    throw new \RuntimeException(\Yii::t('fts-yii2-options', 'Data is not callable.'));
                }

                return call_user_func($this->data);
            default:
                throw new InvalidParamException(\Yii::t('fts-yii2-options', 'Invalid data type {type}.', ['type' => $this->type]));
        }
    }
}