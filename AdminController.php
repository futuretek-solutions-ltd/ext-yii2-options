<?php
namespace futuretek\options;

use Yii;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\db\IntegrityException;
use yii\filters\VerbFilter;
use yii\grid\GridView;
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
                        return OptionHelper::formatValue($model);
                    },
                ],
            ],
            'export' => false,
        ])]);
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
