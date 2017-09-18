<?php
/**
 * @var yii\web\View $this
 * @var ActiveDataProvider $dataProvider
 */

use futuretek\options\OptionHelper;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('fts-yii2-options', 'Options');
?>
<div class="crud-index">
    <div class="row">
        <div class="col pb-4">
            <h1><?= $this->title ?></h1>
        </div>
    </div>
    <div class="row">
        <div class="col pb-2">
            <?= Html::a(Yii::t('fts-yii2-options', 'Update'), ['update'], ['class' => 'btn btn-primary pull-right']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="box">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => '<div class="box-body table-responsive">{items}</div>',
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
                ]) ?>
            </div>
        </div>
    </div>
</div>
