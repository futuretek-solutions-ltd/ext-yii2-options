<?php

namespace futuretek\options;

use yii\base\Action;

/**
 * Class IndexAction
 *
 * @package futuretek\options
 * @author  Martin Bocek
 * @license Apache-2.0
 * @link    http://www.futuretek.cz
 */
class IndexAction extends Action
{
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        if (\Yii::$app->request->post('form-submit') !== null) {
            foreach (\Yii::$app->get('options')->getDefinition() as $option) {
                $value = \Yii::$app->request->post($option['name']);
                if (($value === '' || $value === null) && $option['type'] === Option::TYPE_PASSWORD) {
                    continue;
                }
                \Yii::$app->get('options')->set($option['name'], $value);
            }

            \Yii::$app->session->setFlash('info', \Yii::t('fts-yii2-options', 'Successfully saved.'), true);
        }

        return $this->controller->render('@vendor/futuretek/yii2-options/views/index');
    }
}