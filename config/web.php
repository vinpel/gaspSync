<?php

$params = require(__DIR__ . '/params.php');

$config = [
  'id' => 'basic',
  'basePath' => dirname(__DIR__),
  'bootstrap' => ['log'],

  'aliases' => [
    '@storage' => '@app/storage',
  ],
  'components' => [
    'request' => [
      // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
      'cookieValidationKey' => 'sUK-zzb10rYX4qGuFnhrUTGyZmXQJ-eJ',
    ],
    'response' => [
      //   'format' => yii\web\Response::FORMAT_JSON,
      'charset' => 'UTF-8',
    ],

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
    'mailer' => [
      'class' => 'yii\swiftmailer\Mailer',
      // send all mails to a file by default. You have to set
      // 'useFileTransport' to false and configure a transport
      // for the mailer to send real emails.
      'useFileTransport' => true,
    ],
    'log' => [
      'traceLevel' => YII_DEBUG ? 3 : 0,
      'targets' => [
        [
          'class' => 'yii\log\FileTarget',
          'levels' => ['error', 'warning','info'],
        ],
      ],
    ],
    'db' => require(__DIR__ . '/db.php'),
    'urlManager' => [
      'enablePrettyUrl' => true,
      'showScriptName' => false,
      'rules' => [
        ['class' => 'app\components\IncludeAllUrlRules'],
      ],

    ],
  ],
  'params' => $params,
];

if (YII_ENV_DEV) {
  // configuration adjustments for 'dev' environment
  $config['bootstrap'][] = 'debug';
  $config['modules']['debug'] = 'yii\debug\Module';
  $config['modules']['debug'] = [
    'class' => 'yii\debug\Module',
    'allowedIPs' => ['172.16.28.1','10.0.4.50']
  ];

  $config['bootstrap'][] = 'gii';
  $config['modules']['gii'] = [
    'class'=>'yii\gii\Module',
    'allowedIPs' => ['172.16.28.1', '10.0.4.50','192.168.0.35']
  ];



}

return $config;
