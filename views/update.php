<?php
/**
 * @var yii\web\View $this
 * @var \futuretek\options\Option[] $options
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('fts-yii2-options', 'Update options');
$lastCat = null;
?>

<div class="row">
    <div class="col pb-4">
        <h1><?= $this->title ?></h1>
    </div>
</div>

<?php $form = ActiveForm::begin([
    'options' => ['class' => 'box'],
]); ?>
<div class="row">
    <div class="col">
        <?php foreach ($options as $index => $opt) {
            if ($lastCat !== $opt->category) {
                echo Html::tag('h2', $opt->category, ['class' => 'option-category']);
            }
            $lastCat = $opt->category;
            \futuretek\options\OptionHelper::renderEditField($form, $opt);
        } ?>
    </div>
</div>
<div class="row">
    <div class="col pt-4 pb-2">
        <?= Html::a(Yii::t('fts-yii2-options', 'Cancel'), ['index'], ['class' => 'btn btn-danger']) ?>
    </div>
    <div class="col pt-4 pb-2">
        <?= Html::submitButton(Yii::t('fts-yii2-options', 'Save'), ['class' => 'btn btn-primary pull-right']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
