<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-6-22
 * Time: 下午2:59
 */

namespace app\modules\v1\services;

use yii\rest\Action;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\Url;


class VerifyAction extends Action {
    /**
     * @var string the scenario to be assigned to the new model before it is validated and saved.
     */
    public $scenario = Model::SCENARIO_DEFAULT;
    /**
     * @var string the name of the view action. This property is need to create the URL when the mode is successfully created.
     */
    public $viewAction = 'view';

    /**
     * Creates a new model.
     * @return \yii\db\ActiveRecordInterface the model newly created
     * @throws \Exception if there is any error when creating the model
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /**
         * @var \yii\db\ActiveRecord $model
         */
        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        }

        return $model;
    }
} 