<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-6-22
 * Time: 下午1:57
 */

namespace app\modules\v1\controllers;

use app\common\controllers;
use app\common\models\PrimeKey;
use app\common\utilities\GlobalUtility;
use app\modules\v1\models\User;
use Yii;
use yii\data\SqlDataProvider;
use yii\helpers\Security;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use app\common\utilities\MathUtility;
use yii\db\mssql\PDO;
use PDOException;

class UserController extends controllers\UserController {
    public $modelClass = 'app\modules\v1\models\User';

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
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function verbs()
    {
        $verbs = parent::verbs();

        $verbs['verify'] = ['POST'];
        $verbs['reset'] = ['PUT'];
        $verbs['exchangechattoken'] = ['GET'];
        $verbs['follow'] = ['POST'];

        return $verbs;
    }

    public function prepareDataProvider()
    {
        // prepare and return a data provider for the "index" action

    }

    public function actionCreate()
    {
        $response = $this->getRestResponse();

        $params = Yii::$app->getRequest()->getBodyParams();

        if(isset($params['username']) && isset($params['password'])) {
            $username = $params['username'];
            if (User::findOne(['username' => $username]))
                $this->handleError(409, 'duplicate username');


            $password = $params['password'];
            $passwordHash = Security::generatePasswordHash($password);

//            /**
//             * @var \app\modules\v1\models\User $model
//             */
            $model = new User();
            $model->load($params, '');

            $model->setAttribute('password', $passwordHash);
            $model->nick_name = $model->username;
            if (GlobalUtility::isValidEmail($model->username))
                $model->email = $model->username;

            if (GlobalUtility::isValidPhone($model->username))
                $model->phone = $model->username;

            if ($model->save()) {
                $response->setStatusCode(201);
                $id = implode(',', array_values($model->getPrimaryKey(true)));
                $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));

                try {
                    $db = new PDO('mysql:host=127.0.0.1;dbname=ejabberd', 'root', 'hly');
                    $sql = 'insert into `users` set `username` = :username, `password` = :password';
                    $sth = $db->prepare($sql);
                    $count = $sth->execute(array(':username' => $model->id, ':password' => $model->password));
                    if ($count == 1) {
                        return array('user' => $model, 'chat_token' => $model->password);
                    } else {
                        $this->handleError(500, 'im register error');
                    }
                    $db = null;
                } catch (PDOException $e) {
                    $this->handleError(500, 'im register error'.$e->getMessage());
                    die();
                }

                return $model;
            } else {
                $this->handleError(500, 'model save error');
            }
        } else {
            $this->handleError(400, 'username or password missing');
        }
    }

    public function actionVerify()
    {
        $request = Yii::$app->getRequest()->getBodyParams();
        $response = $this->getRestResponse();
//        echo 'request param is set username '.(isset($request['username']) ? 'YES' : 'NO').' and is set password '.(isset($request['password'])?'YES':'NO');
        if(isset($request['username']) && isset($request['password'])) {
            $username = $request['username'];
            $password = $request['password'];

            $user = User::findOne([
                'username'=>$username,
            ]);
            if($user != null) {

                if(Security::validatePassword($password, $user->password)) {
                    $user->access_token = Security::generateRandomKey();
                    $expireIn = strtotime('+1 year');
                    $user->expire_in = $expireIn;
                    if($user->save()) {
                        $response->setStatusCode(200);

                        return array('user' => $user, 'chat_token' => $user->password);
                    } else {
                        $this->handleError(500,'model save error');
                    }

                } else {
                    $this->handleError(403,'invalid password');
                }
            } else {
                $this->handleError(404, 'not found user');
            }


        } else {
            $this->handleError(400);
        }
    }

    public function actionExchangechattoken()
    {
        $headers = Yii::$app->getRequest()->getHeaders();
        if (isset($headers['X-Auth-AccessToken'])) {
            $token = $headers['X-Auth-AccessToken'];
            $user = User::findIdentityByAccessToken($token);
            if ($user != null) {
                $this->handleResponse(array('token' => $user->password));
            } else {
                $this->handleError(404);
            }
        } else {
            $this->handleError(400, 'header field X-Auth-AccessToken missing');
        }
    }

    public function actionReset()
    {
        $password = Yii::$app->getRequest()->getBodyParam('password');
        if ($password == null)
            $this->handleError(400,'need param password');

        $headers = Yii::$app->getRequest()->getHeaders();

        if (isset($headers['X-Auth-AccessToken'])) {
            $accessToken = $headers['X-Auth-AccessToken'];
            $user = User::findIdentityByAccessToken($accessToken);

            if ($user == null)
                $this->handleError(403,'invalid access token');

            $passwordHash = Security::generatePasswordHash($password);
            $user->password = $passwordHash;

            if ($user->save()) {
                $this->handleResponse('password reset success');
            } else {
                $this->handleError(500);
            }
        } elseif (isset($headers['X-Auth-ResetToken'])) {
            $resetToken = $headers['X-Auth-ResetToken'];
            $user = User::findIdentityByResetToken($resetToken);

            if ($user == null)
                $this->handleError(403,'invalid reset token');

            $passwordHash = Security::generatePasswordHash($password);
            $user->password = $passwordHash;

            if ($user->save()) {
                $this->handleResponse('password reset success');
            } else {
                $this->handleError(500);
            }
        }
    }

    public function actionFetch($keyword)
    {
//        $tableName = Nmecho::tableName();
//        $result = PrimeKey::findOne(['table_name' => $tableName]);
//        $max = $result->max;
        $max = PrimeKey::maxPrimeKey(User::tableName());
        $randomIds = MathUtility::fetchRandom(5, 1, $max);

        $query = new Query();
        $provider = new ActiveDataProvider([
            'query' => $query->addSelect(['id', 'username', 'nick_name', 'password'])->from('nm_user')->where(['id' => $randomIds]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $provider;
    }

    // select a.id, exists(SELECT * FROM `nm_relationship` as r, `nm_user`  as u WHERE u.id=r.user_id and u.id=1 and  r.friend_id=a.id) as relationship from nm_user as a;
    public function actionList()
    {
        $sql = 'SELECT `id`, `nick_name` FROM `nm_user` WHERE 1=1';
        $params = [];

        $headers = Yii::$app->getRequest()->getHeaders();
        if (isset($headers['X-Auth-AccessToken'])) {
            $token = $headers['X-Auth-AccessToken'];
            $user = User::findIdentityByAccessToken($token);
            if ($user == null) {
                $this->handleError(403, 'invalid token');
            } else {
                $sql = 'select a.id, a.nick_name, exists(SELECT 1 FROM `nm_relationship` as r, `nm_user`  as u WHERE u.id=r.user_id and u.id=:userId and r.friend_id=a.id) as relationship from nm_user as a;';
                $params = [':userId'=>$user->id];
            }
        }


        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM `nm_user` WHERE 1=1')->queryScalar();
        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $provider;
    }

    /// 添加好友
    public function actionFollow()
    {
        $headers = Yii::$app->getRequest()->getHeaders();
        if (!isset($headers['X-Auth-AccessToken'])) {
            $this->handleError(403, 'token is missing');
        }

        $token = $headers['X-Auth-AccessToken'];
        $user = User::findIdentityByAccessToken($token);
        if ($user == null) {
            $this->handleError(403, 'invalid token');
        }


    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if($action != 'index' || $action != 'verify' || $action != 'create') {
            $headers = Yii::$app->getRequest()->getHeaders();
            if(isset($headers['X-Auth-AccessToken'])) {
                $accessToken = $headers['X-Auth-AccessToken'];
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
} 