<?php

namespace futuretek\options;

use rmrevin\yii\fontawesome\FA;
use rmrevin\yii\fontawesome\FontAwesome;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class OptionHelper
 *
 * @package futuretek\options
 * @author  Lukas Cerny <lukas.cerny@futuretek.cz>
 * @license https://www.apache.org/licenses/LICENSE-2.0.html Apache-2.0
 * @link    http://www.futuretek.cz
 */
class OptionHelper
{
    /**
     * Return rendered edit field
     *
     * @param array $item
     * @return string|\yii\widgets\ActiveField
     * @throws \yii\base\InvalidArgumentException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \yii\base\InvalidParamException
     */
    public static function returnRenderEditField(array $item)
    {
        $value = Yii::$app->get('options')->get($item['name']);

        $options = [
            'class' => 'form-control',
            'id' => $item['name'],
        ];

        $renderLabel = true;
        $labelOptions = [
            'class' => 'control-label',
        ];

        switch ($item['type']) {
            case Option::TYPE_BOOL:
                $renderLabel = false;
                $content = '<label class="control-label checkbox-container" for="' . $item['name'] . '">' . $item['title'] .
                    Html::checkbox($item['name'], $value, array_merge($options, [
                    ])) .
                    Html::tag('span', '', ['class' => 'checkmark']) . '</label>';
                break;
            case Option::TYPE_DATETIME:
                $content = self::inputAddon('calendar', Html::input('date', $item['name'], $value, $options));
                break;
            case Option::TYPE_EMAIL:
                $content = self::inputAddon('envelope', Html::input('email', $item['name'], $value, $options));
                break;
            case Option::TYPE_FLOAT:
            case Option::TYPE_INT:
                $content = Html::input('number', $item['name'], $value, $options);
                break;
            case Option::TYPE_OPTION:
                $content = Html::dropDownList($item['name'], $value, ArrayHelper::map(Option::getData($item), 'id', 'name'), $options);
                break;
            case Option::TYPE_PHONE:
                $content = self::inputAddon('phone', Html::input('tel', $item['name'], $value, $options));
                break;
            case Option::TYPE_STRING:
                $content = Html::input('text', $item['name'], $value, $options);
                break;
            case Option::TYPE_HTML:
                if (Yii::$app->hasModule('redactor')) {
                    $content = yii\redactor\Redactor::widget([
                        'name' => $item['name'],
                        'value' => $value,
                        'options' => $options,
                    ]);
                    break;
                }
            //No break
            case Option::TYPE_TEXT:
                $content = Html::textarea($item['name'], $value, array_merge($options, ['rows' => 6]));
                break;
            case Option::TYPE_TIME:
                $content = self::inputAddon('clock-o', Html::input('time', $item['name'], $value, $options));
                break;
            case Option::TYPE_URL:
                $content = self::inputAddon('globe', Html::input('url', $item['name'], $value, $options));
                break;
            case Option::TYPE_PASSWORD:
                $content = self::inputAddon('key', Html::input('password', $item['name'], null, $options));
                break;
            default:
                throw new InvalidArgumentException(Yii::t('fts-yii2-options', 'Option type {type} not found', ['type' => $item['type']]));
        }

        return $renderLabel ? Html::label($item['title'], $item['name'], $labelOptions) . $content : $content;
    }

    /**
     * Render add-on icon in input
     *
     * @param string $icon Icon class
     * @param string $content Content
     * @return string
     */
    public static function inputAddon($icon, $content)
    {
        return Html::tag('div', Html::tag('span', FontAwesome::i($icon), ['class' => 'input-group-addon']) . $content, ['class' => 'input-group']);
    }
}