<?php

namespace futuretek\options;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * Class OptionHelper
 *
 * @package futuretek\options
 * @author  Martin Bocek
 * @license Apache-2.0
 * @link    http://www.futuretek.cz
 */
class OptionHelper
{
    /**
     * Generate formatted value for Yes/No/null options
     *
     * @param int $value Attribute value
     * @param bool $icon Show icon instead of text
     * @return string Generated html code
     */
    public static function gridValueYesNo($value, $icon = true)
    {
        switch ($value) {
            case 0:
                return $icon ? '<i class="fa fa-times text-danger" title="' . Yii::t('fts-yii2-options', 'No') . '"></i>' : Yii::t('fts-yii2-options', 'No');
                break;
            case 1:
                return $icon ? '<i class="fa fa-check text-success" title="' . Yii::t('fts-yii2-options', 'Yes') . '"></i>' : Yii::t('fts-yii2-options', 'Yes');
                break;
            default:
                return $icon ? '<i class="fa fa-question text-info" title="' . Yii::t('fts-yii2-options', 'Unknown') . '"></i>' : Yii::t('fts-yii2-options', 'Unknown');
        }
    }

    /**
     * @param Option $model Option model
     * @return string
     * @throws \RuntimeException
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public static function formatValue($model)
    {
        switch ($model->type) {
            case Option::TYPE_BOOL:
                $output = self::gridValueYesNo($model->value);
                break;
            case Option::TYPE_DATETIME:
                $output = Yii::$app->formatter->asDatetime($model->value);
                break;
            case Option::TYPE_OPTION:
                $values = ArrayHelper::map($model->getData(), 'id', 'name');
                $output = $values[$model->value];
                break;
            case Option::TYPE_PASSWORD:
                $output = '********';
                break;
            case Option::TYPE_TIME:
                $output = Yii::$app->formatter->asTime($model->value);
                break;
            default:
                $output = $model->value;
        }

        if ($model->unit !== null) {
            $output .= ' ' . $model->unit;
        }

        return $output;
    }

    /**
     * Render edit field
     *
     * @param ActiveForm $form
     * @param Option $option
     * @throws \RuntimeException
     * @throws \yii\base\InvalidParamException
     */
    public static function renderEditField(ActiveForm $form, Option $option)
    {
        switch ($option->type) {
            case Option::TYPE_BOOL:
                echo $form->
                field($option, '[' . $option->id . ']value')
                    ->label($option->title)
                    ->checkbox()
                    ->hint($option->description);
                break;
            case Option::TYPE_DATETIME:
                echo $form
                    ->field($option, '[' . $option->id . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>{input}</div>',
                    ])->label($option->title)
                    ->input('date')
                    ->hint($option->description);
                break;
            case Option::TYPE_EMAIL:
                echo $form
                    ->field($option, '[' . $option->id . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-envelope"></i></span>{input}</div>',
                    ])->label($option->title)
                    ->input('email')
                    ->hint($option->description);
                break;
            case Option::TYPE_FLOAT:
            case Option::TYPE_INT:
                echo $form
                    ->field($option, '[' . $option->id . ']value')
                    ->label($option->title)
                    ->input('number')
                    ->hint($option->description);
                break;
            case Option::TYPE_OPTION:
                echo $form
                    ->field($option, '[' . $option->id . ']value')
                    ->label($option->title)
                    ->dropDownList(ArrayHelper::map($option->getData(), 'id', 'name'))
                    ->hint($option->description);
                break;
            case Option::TYPE_PHONE:
                echo $form
                    ->field($option, '[' . $option->id . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-phone"></i></span>{input}</div>',
                    ])
                    ->label($option->title)
                    ->input('tel')
                    ->hint($option->description);
                break;
            case Option::TYPE_STRING:
                echo $form
                    ->field($option, '[' . $option->id . ']value')
                    ->label($option->title)
                    ->input('text')
                    ->hint($option->description);
                break;
            case Option::TYPE_TEXT:
                echo $form
                    ->field($option, '[' . $option->id . ']value')
                    ->label($option->title)
                    ->textarea(['rows' => 6])
                    ->hint($option->description);
                break;
            case Option::TYPE_HTML:
                if (Yii::$app->hasModule('redactor')) {
                    echo $form
                        ->field($option, '[' . $option->id . ']value')
                        ->label($option->title)
                        ->hint($option->description)
                        ->widget(\yii\redactor\widgets\Redactor::className());
                } else {
                    echo $form
                        ->field($option, '[' . $option->id . ']value')
                        ->label($option->title)
                        ->textarea(['rows' => 6])
                        ->hint($option->description);
                }
                break;
            case Option::TYPE_TIME:
                echo $form
                    ->field($option, '[' . $option->id . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-clock-o"></i></span>{input}</div>',
                    ])
                    ->label($option->title)
                    ->input('time')
                    ->hint($option->description);
                break;
            case Option::TYPE_URL:
                echo $form
                    ->field($option, '[' . $option->id . ']value')
                    ->label($option->title)
                    ->input('url')
                    ->hint($option->description);
                break;
            case Option::TYPE_PASSWORD:
                echo $form
                    ->field($option, '[' . $option->id . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-key"></i></span>{input}</div>',
                    ])
                    ->label($option->title)
                    ->input('password')
                    ->hint($option->description);
                break;
            default:
                echo Html::tag('p', Yii::t('fts-yii2-options', 'Option type {type} not found', ['type' => $option->type]), ['class' => 'label label-danger']);
        }
    }
}