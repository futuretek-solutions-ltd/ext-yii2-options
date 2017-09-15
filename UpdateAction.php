<?php
/**
 * Created by PhpStorm.
 * User: bocekma
 * Date: 15.9.2017
 * Time: 10:35
 */

namespace futuretek\options;


use Yii;
use yii\base\Action;

/**
 * Class ActionUpdate
 * @package futuretek\options
 */
class UpdateAction extends Action
{
    /**
     * @return string|\yii\web\Response
     */
    public function run () {
        /** @var Option[] $options */
        $options = Option::find()->indexBy('id')->where(['system' => 0, 'context' => 'Option'])->all();
        foreach ($options as $option) {
            if ($option->type === Option::TYPE_PASSWORD) {
                $option->value = null;
            }
        }

        /** @noinspection NotOptimalIfConditionsInspection */
        if (Option::loadMultiple($options, Yii::$app->request->post()) && Option::validateMultiple($options)) {
            foreach ($options as $option) {
                if ($option->type === Option::TYPE_PASSWORD && ($option->value === '' || $option->value === null)) {
                    continue;
                }
                Option::set($option->name, $option->value, $option->context, $option->context_id, $option->type, $option->system);
            }

            return $this->controller->redirect('index');
        }

        return $this->controller->render('@vendor/futuretek/yii2-options/views/update', ['xoptions' => $options]);
    }
}