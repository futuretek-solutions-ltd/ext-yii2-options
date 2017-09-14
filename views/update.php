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
            <?php foreach ($xoptions as $index => $opt) { ?>
                <?php if ($opt->type === 'B') { ?>
                    <?php /* Boolean */ ?>
                    <?= $form->field($opt, '[' . $index . ']value')->label($opt->title)->widget(SwitchInput::classname(), [
                        'pluginOptions' => [
                            'onText' => '<i class="fa fa-check"></i>',
                            'offText' => '<i class="fa fa-remove"></i>',
                            'onColor' => 'success',
                            'offColor' => 'danger'
                        ],
                        'containerOptions' => ['class' => '']
                    ])->hint($opt->description) ?>
                <?php } else if ($opt->type === 'D') { ?>
                    <?php /* Date */ ?>
                    <?= $form->field($opt, '[' . $index . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>{input}</div>',
                    ])->label($opt->title)->input('date')->hint($opt->description) ?>
                <?php } else if ($opt->type === 'E') { ?>
                    <?php /* Email */ ?>
                    <?= $form->field($opt, '[' . $index . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-envelope"></i></span>{input}</div>',
                    ])->label($opt->title)->input('email')->hint($opt->description) ?>
                <?php } else if ($opt->type === 'F') { ?>
                    <?php /* Float */ ?>
                    <?= $form->field($opt, '[' . $index . ']value')->label($opt->title)->input('number')->hint($opt->description) ?>
                <?php } else if ($opt->type === 'I') { ?>
                    <?php /* Int */ ?>
                    <?= $form->field($opt, '[' . $index . ']value')->label($opt->title)->input('number')->hint($opt->description) ?>
                <?php } else if ($opt->type === 'O') { ?>
                    <?php /* Option */ ?>
                    <?= $form->field($opt, '[' . $index . ']value')->label($opt->title)->dropDownList(\yii\helpers\ArrayHelper::map($opt->getData(), 'id', 'name'))->hint($opt->description) ?>
                <?php } else if ($opt->type === 'P') { ?>
                    <?php /* Phone */ ?>
                    <?= $form->field($opt, '[' . $index . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-phone"></i></span>{input}</div>',
                    ])->label($opt->title)->input('tel')->hint($opt->description) ?>
                <?php } else if ($opt->type === 'S') { ?>
                    <?php /* String */ ?>
                    <?= $form->field($opt, '[' . $index . ']value')->label($opt->title)->input('text')->hint($opt->description) ?>
                <?php } else if ($opt->type === 'X') { ?>
                    <?php /* Long text */ ?>
                    <?= $form->field($opt, '[' . $index . ']value')->label($opt->title)->textarea(['rows' => 6])->hint($opt->description) ?>
                <?php } else if ($opt->type === 'T') { ?>
                    <?php /* Time */ ?>
                    <?= $form->field($opt, '[' . $index . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-clock-o"></i></span>{input}</div>',
                    ])->label($opt->title)->input('time')->hint($opt->description) ?>
                <?php } else if ($opt->type === 'U') { ?>
                    <?php /* URL */ ?>
                    <?= $form->field($opt, '[' . $index . ']value')->label($opt->title)->input('url')->hint($opt->description) ?>
                <?php } else if ($opt->type === 'W') { ?>
                    <?php /* Password */ ?>
                    <?= $form->field($opt, '[' . $index . ']value', [
                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon"><i class="fa fa-key"></i></span>{input}</div>',
                    ])->label($opt->title)->input('password')->hint($opt->description) ?>
                <?php } else { ?>
                    <p class="label label-danger"><?= Yii::t('fts-yii2-options', 'Option type {type} not found', ['type' => $opt->type]) ?></p>
                <?php } ?>
            <?php } ?>
        </div>

        <div class="box-footer text-right">
            <?= Html::submitButton(Yii::t('fts-yii2-options', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php yii\bootstrap\ActiveForm::end(); ?>
    </div>
</div>
