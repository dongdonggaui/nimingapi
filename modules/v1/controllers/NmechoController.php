<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-5-22
 * Time: 下午5:15
 */

namespace app\modules\v1\controllers;

use app\common;
use app\modules\v1\models\Nmecho;
use app\modules\v1\models\PrimeKey;
use app\common\utilities\MathUtility;
use yii\data\ActiveDataProvider;
use yii\rest\CreateAction;
use yii\web\JsonResponseFormatter;
use yii\db\Query;
use Yii;
use yii\helpers\Url;


class NmechoController extends common\controllers\NmechoController
{
    public $modelClass = 'app\modules\v1\models\Nmecho';

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',    // 默认分页处理
    ];

    public function actions()
    {
        $actions = parent::actions();

        // disable the "create" actions
        unset($actions['create']);

        // customize the data provider preparation with the "prepareDataProvider()" method
//        $actions['create']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function prepareDataProvider()
    {
        // prepare and return a data provider for the "fetch" action

    }

    public function actionCreate()
    {
        /**
         * @var \yii\db\ActiveRecord $model
         */
        $model = new $this->modelClass();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));

            // 对应主键最大值加1
            $tableName = Nmecho::tableName();
            $result = PrimeKey::findOne(['table_name' => $tableName]);
            $result->max++;
            $result->save();
        }

        return $model;
    }

    public function actionFetch($keyword)
    {
//        $tableName = Nmecho::tableName();
//        $result = PrimeKey::findOne(['table_name' => $tableName]);
//        $max = $result->max;
        $max = Nmecho::maxPrimeKey();
        $randomIds = MathUtility::fetchRandom(5, 1, $max);

        $query = new Query();
        $provider = new ActiveDataProvider([
            'query' => $query->addSelect(['id', 'content', 'user', 'create_time'])->from('nm_echo')->where(['id' => $randomIds]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $provider;
    }

    public function actionList($userId)
    {
        $query = new Query();
        $provider = new ActiveDataProvider([
            'query' => $query->addSelect(['id', 'content', 'user', 'create_time'])->from('nm_echo')->where(['user' => $userId]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $provider;
    }
} 