<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-6-27
 * Time: 上午9:40
 */

namespace app\modules\v1\controllers;

use app\modules\v1\models\PrimeKey;
use Yii;
use app\modules\v1\models\Relationship;
use app\modules\v1\models\User;


class RelationshipController extends \app\common\controllers\RelationshipController
{
    public $modelClass = 'app\modules\v1\models\Relationship';

    public function actions()
    {
        $actions = parent::actions();

        // disable the "create" actions
        unset($actions['create']);

        // customize the data provider preparation with the "prepareDataProvider()" method
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionCreate()
    {
        $headers = Yii::$app->getRequest()->getHeaders();
        if (!isset($headers['X-Auth-Token'])) {
            $this->handleError(403, 'token is missing');
        }

        $token = $headers['X-Auth-Token'];
        $user = User::findIdentityByAccessToken($token);
        if ($user == null) {
            $this->handleError(403, 'invalid token');
        }

        $relationShip = new Relationship();
        $relationShip->load(Yii::$app->getRequest()->getBodyParams());
        $max = PrimeKey::maxPrimeKey(Relationship::tableName());
        $relationShip->id = ''.($max + 1);
        if ($relationShip->save()) {
            $this->handleResponse('Success');
        } else {
            $this->handleError(500, 'save relationship error');
        }
    }
} 