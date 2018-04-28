<?php
/**
 * @var yii\web\View $this
 */

use futuretek\options\assets\CheckboxAsset;
use futuretek\options\OptionHelper;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

CheckboxAsset::register($this);
\rmrevin\yii\fontawesome\AssetBundle::register($this);

$this->title = Yii::t('fts-yii2-options', 'Options');
?>

<div class="option-index">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'box']]) ?>
    <div class="row">
        <div class="col pb-4">
            <h1><?= $this->title ?></h1>
        </div>
        <div class="col pt-4 pb-2">
            <?= Html::submitButton(Yii::t('fts-yii2-options', 'Save'), ['class' => 'btn btn-primary pull-right', 'name' => 'form-submit', 'value' => 1]) ?>
        </div>
    </div>
    <div class="row">
    </div>
    <div class="row">
        <div class="col">
            <?php
            $items = [];
            $first = true;
            foreach (Yii::$app->options->config as $group) {
                if (!$group['visible']) {
                    continue;
                }

                $content = '';
                foreach ($group['items'] as $option) {
                    if (!is_array($option)) {
                        //Divider
                        $content .= Html::tag('div', Html::tag('h3', $option), ['class' => 'divider']);
                        continue;
                    }

                    if (!$option['visible'] || $option['context']) {
                        continue;
                    }

                    $content .= '
                    <div class="form-group">
                        ' . OptionHelper::returnRenderEditField($option) . '
                        ' . (empty($option['hint']) ? '' : '<p class="help-block">' . $option['hint'] . '</p>') . '
                    </div>
                    ';
                }

                $items[] = [
                    'label' => $group['title'],
                    'content' => Html::tag('div', $content, ['class' => 'opt-group']),
                    'active' => $first,
                ];
                $first = false;
            }
            ?>
            <?= Tabs::widget([
                'items' => $items,
                'options' => [
                    'class' => 'tabsControl',
                ],
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col pt-4 pb-2">
            <?= Html::submitButton(Yii::t('fts-yii2-options', 'Save'), ['class' => 'btn btn-primary pull-right', 'name' => 'form-submit', 'value' => 1]) ?>
        </div>
    </div>
    <?php
    ActiveForm::end();

    $this->registerJs("$('ul.tabsControl > li:first-child > a').addClass('active');", \yii\web\View::POS_READY);

    ?>
</div>