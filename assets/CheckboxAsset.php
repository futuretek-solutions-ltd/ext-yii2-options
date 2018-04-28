<?php

namespace futuretek\options\assets;

use yii\web\AssetBundle;

class CheckboxAsset extends AssetBundle
{
    public $sourcePath = '@vendor/futuretek/yii2-options/assets/checkbox';

    public $css = [
        YII_ENV_DEV ? 'checkbox.scss' : 'checkbox.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];

    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
