<?php
namespace futuretek\options;

use futuretek\grid\GridView;
use Yii;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\db\IntegrityException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class AdminController
 *
 * @package futuretek\options
 * @author  Lukáš Černý <lukas.cerny@futuretek.cz>, Petr Compel <petr.compel@futuretek.cz>
 * @license Apache-2.0
 * @link    http://www.futuretek.cz
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!in_array(Option::tableName(), Yii::$app->db->schema->tableNames, true)) {
            throw new IntegrityException(Yii::t('fts-yii2-options', 'Before use you must install migrations.'));
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all Option models.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionIndex()
    {
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

        return $this->render('@vendor/futuretek/yii2-options/views/index', ['grid' => GridView::widget([
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
                        return $this->formatValue($model);
                    },
                ],
            ],
            'export' => false,
        ])]);
    }

    /**
     * @param Option $model Option model
     * @return string
     */
    protected function formatValue($model)
    {
        switch ($model->type) {
            case Option::TYPE_BOOL:
                $output = self::gridValueYesNo($model->value);
                break;
            case Option::TYPE_DATETIME:
                $output = Yii::$app->formatter->asDatetime($model->value);
                break;
            case Option::TYPE_OPTION:
                $values = ArrayHelper::map($model->getData(), 'id', 'name');
                $output = $values[$model->value];
                break;
            case Option::TYPE_PASSWORD:
                $output = '********';
                break;
            case Option::TYPE_TIME:
                $output = Yii::$app->formatter->asTime($model->value);
                break;
            default:
                $output = $model->value;
        }

        if ($model->unit !== null) {
            $output .= ' ' . $model->unit;
        }

        return $output;
    }

    /**
     * Generate formatted value for Yes/No/null options
     *
     * @param int $value Attribute value
     * @param bool $icon Show icon instead of text
     * @return string Generated html code
     */
    public static function gridValueYesNo($value, $icon = true)
    {
        switch ($value) {
            case 0:
                return $icon ? '<i class="fa fa-times text-danger" title="' . Yii::t('fts-yii2-options', 'No') . '"></i>' : Yii::t('fts-yii2-options', 'No');
                break;
            case 1:
                return $icon ? '<i class="fa fa-check text-success" title="' . Yii::t('fts-yii2-options', 'Yes') . '"></i>' : Yii::t('fts-yii2-options', 'Yes');
                break;
            default:
                return $icon ? '<i class="fa fa-question text-info" title="' . Yii::t('fts-yii2-options', 'Unknown') . '"></i>' : Yii::t('fts-yii2-options', 'Unknown');
        }
    }

    /**
     * Updates an existing Option model.
     * If update is successful, the browser will be redirected to the index page.
     *
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws NotFoundHttpException
     * @throws InvalidParamException
     */
    public function actionUpdate()
    {
        /** @var Option[] $options */
        $options = Option::find()->indexBy('id')->where(['system' => 0, 'context' => 'Option'])->all();
        foreach ($options as $option) {
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

            return $this->redirect('index');
        }

        return $this->render('@vendor/futuretek/yii2-options/views/update', ['xoptions' => $options]);
    }
}
