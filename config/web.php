<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/nmecho','v1/user', 'v1/relationship']],
                ['class' => 'yii\web\UrlRule', 'pattern' => '<module:\w+>/login', 'route' => '<module>/user/verify', 'verb' => 'POST'],
                ['class' => 'yii\web\UrlRule', 'pattern' => '<module:\w+>/register', 'route' => '<module>/user/create', 'verb' => 'POST'],
                ['class' => 'yii\web\UrlRule', 'pattern' => '<module:\w+>/update', 'route' => '<module>/user/reset', 'verb' => 'PUT'],
                ['class' => 'yii\web\UrlRule', 'pattern' => '<module:\w+>/chatlogin', 'route' => '<module>/user/exchangechattoken', 'verb' => 'GET'],
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<module:\w+>' => '<module>/default/index',
                '' => 'site/index',
            ],
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';

    $config['modules']['v1'] = 'app\modules\v1\v1';
}

return $config;
