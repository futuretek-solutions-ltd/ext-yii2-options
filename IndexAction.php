<?php
/**
 * Created by PhpStorm.
 * User: bocekma
 * Date: 15.9.2017
 * Time: 10:41
 */

namespace futuretek\options;


use yii\base\Action;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;

class IndexAction extends Action
{
    public function run () {
        $dataProvider = new ActiveDataProvider([
            'query' => Option::find(),
            'pagination' => [
                'pageSize' => 9999,
            ],
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ],
            ],

        ]);

        return $this->controller->render('@vendor/futuretek/yii2-options/views/index', ['grid' => GridView::widget([
            'dataProvider' => $dataProvider,
            'responsive' => false,
            'striped' => false,
            'toolbar' => [],
            'layout' => '<div class="box-body table-responsive">{items}</div>',
            'pjax' => true,
            'columns' => [
                [
                    'attribute' => 'title',
                ],
                [
                    'attribute' => 'value',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return OptionHelper::formatValue($model);
                    },
                ],
            ],
            'export' => false,
        ])]);
    }
}