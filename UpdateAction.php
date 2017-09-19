<?php

namespace futuretek\options;

use Yii;
use yii\base\Action;

/**
 * Class UpdateAction
 *
 * @package futuretek\options
 * @author  Martin Bocek
 * @license Apache-2.0
 * @link    http://www.futuretek.cz
 */
class UpdateAction extends Action
{
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public function run () {
        /** @var Option[] $options */
        $options = Option::find()->indexBy('id')->where(['system' => 0, 'context' => 'Option'])->all();
        foreach ($options as $option) {
            $option->system = (int)$option->system;
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

        return $this->controller->render('@vendor/futuretek/yii2-options/views/update', ['options' => $options]);
    }
}