<?php
/**
 * @var yii\web\View $this
 * @var \futuretek\options\Option[] $xoptions
 */
use futuretek\switchinput\SwitchInput;
use yii\helpers\Html;

$this->title = Yii::t('fts-yii2-options', 'Update options');
?>
<div class="row">
    <div class="option-form col-xs-12">
        <?php
        $form = yii\bootstrap\ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => '{label}{beginWrapper}{input}{hint}{error}{endWrapper}',
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
                    'offset' => 'col-sm-offset-2',
                    'wrapper' => 'col-sm-10 col-md-6',
                    'error' => '',
                    'hint' => ''
                ]
            ],
            'options' => ['class' => 'box']
        ]);
        ?>
        <div class="box-body">
            <?php foreach ($xoptions as $index => $opt) {
                \futuretek\options\OptionHelper::renderEditField($form, $opt);
             } ?>
        </div>

        <div class="box-footer text-right">
            <?= Html::submitButton(Yii::t('fts-yii2-options', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php yii\bootstrap\ActiveForm::end(); ?>
    </div>
</div>
