<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-6-23
 * Time: 下午4:13
 */

namespace app\common\controllers;


use yii\rest\ActiveController;
use Yii;
use app\common\models\User;
use yii\web\Response;

class RestController extends ActiveController {
    public function checkAccess($action, $model = null, $params = [])
    {
        if($action != 'index') {
            $headers = Yii::$app->getRequest()->getHeaders();
            if(isset($headers['X-Device-Token'])) {
                $accessToken = $headers['X-Device-Token'];
                $user = User::findIdentityByAccessToken($accessToken);
                if($user != null) {
                    return true;
                } else {

                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    public function handleError($statusCode, $message = null)
    {
        $response = $this->getRestResponse();

        if ($message == null) {
            $message = Response::$httpStatuses[$statusCode];
        }

        $response->statusCode = $statusCode;
        $response->content = json_encode(array('msg'=>$message));
        $response->send();
        exit;
    }

    public function handleResponse($message = null)
    {
        $response = $this->getRestResponse();

        if ($message == null) {
            $message = array('msg' => 'Success');
        } elseif (is_string($message)) {
            $message = array('msg' => $message);
        }

        $response->statusCode = 200;
        $response->content = json_encode($message);
        $response->send();
        exit;
    }

    public function getRestResponse()
    {
        $response = Yii::$app->getResponse();
        $response->getHeaders()->set('Content-Type','application/json');

        return $response;
    }
}