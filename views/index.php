<?php
/**
 * @var yii\web\View $this
 * @var string $grid
 */
use yii\helpers\Html;

$this->title = Yii::t('fts-yii2-options', 'Options');
$this->beginBlock('breadcrumbs');
echo Html::a(Yii::t('fts-yii2-options', 'Update'), ['update'], ['class' => 'btn btn-primary']);
$this->endBlock();
?>
<div class="crud-index row">
    <div class="col-xs-12">
        <div class="box">
            <?= $grid ?>
        </div>
    </div>
</div>
