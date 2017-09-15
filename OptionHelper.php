<?php
/**
 * Created by PhpStorm.
 * User: bocekma
 * Date: 15.9.2017
 * Time: 10:25
 */

namespace futuretek\options;


use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class OptionHelper
 * @package futuretek\options
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

    public static function renderEditField(ActiveForm $form, Option $option)
    {
        if ($option->type === 'B') { ?>
            <?php /* Boolean */ ?>
            <?= $form->field($option, '[' . $option->id . ']value')->label($option->title)->checkbox()->hint($option->description) ?>
        <?php } else if ($option->type === 'D') { ?>
            <?php /* Date */ ?>
            <?= $form->field($option, '[' . $option->id . ']value', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>{input}</div>',
            ])->label($option->title)->input('date')->hint($option->description) ?>
        <?php } else if ($option->type === 'E') { ?>
            <?php /* Email */ ?>
            <?= $form->field($option, '[' . $option->id . ']value', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-envelope"></i></span>{input}</div>',
            ])->label($option->title)->input('email')->hint($option->description) ?>
        <?php } else if ($option->type === 'F') { ?>
            <?php /* Float */ ?>
            <?= $form->field($option, '[' . $option->id . ']value')->label($option->title)->input('number')->hint($option->description) ?>
        <?php } else if ($option->type === 'I') { ?>
            <?php /* Int */ ?>
            <?= $form->field($option, '[' . $option->id . ']value')->label($option->title)->input('number')->hint($option->description) ?>
        <?php } else if ($option->type === 'O') { ?>
            <?php /* Option */ ?>
            <?= $form->field($option, '[' . $option->id . ']value')->label($option->title)->dropDownList(ArrayHelper::map($option->getData(), 'id', 'name'))->hint($option->description) ?>
        <?php } else if ($option->type === 'P') { ?>
            <?php /* Phone */ ?>
            <?= $form->field($option, '[' . $option->id . ']value', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-phone"></i></span>{input}</div>',
            ])->label($option->title)->input('tel')->hint($option->description) ?>
        <?php } else if ($option->type === 'S') { ?>
            <?php /* String */ ?>
            <?= $form->field($option, '[' . $option->id . ']value')->label($option->title)->input('text')->hint($option->description) ?>
        <?php } else if ($option->type === 'X') { ?>
            <?php /* Long text */ ?>
            <?= $form->field($option, '[' . $option->id . ']value')->label($option->title)->textarea(['rows' => 6])->hint($option->description) ?>
        <?php } else if ($option->type === 'T') { ?>
            <?php /* Time */ ?>
            <?= $form->field($option, '[' . $option->id . ']value', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-clock-o"></i></span>{input}</div>',
            ])->label($option->title)->input('time')->hint($option->description) ?>
        <?php } else if ($option->type === 'U') { ?>
            <?php /* URL */ ?>
            <?= $form->field($option, '[' . $option->id . ']value')->label($option->title)->input('url')->hint($option->description) ?>
        <?php } else if ($option->type === 'W') { ?>
            <?php /* Password */ ?>
            <?= $form->field($option, '[' . $option->id . ']value', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-key"></i></span>{input}</div>',
            ])->label($option->title)->input('password')->hint($option->description) ?>
        <?php } else { ?>
            <p class="label label-danger"><?= Yii::t('fts-yii2-options', 'Option type {type} not found', ['type' => $option->type]) ?></p>
        <?php }

    }
}