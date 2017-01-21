<?php

require(__DIR__ . '/alias.php');
$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
  'id' => 'basic-console',
  'basePath' => dirname(__DIR__),
  'bootstrap' => ['log', 'gii'],
  'controllerNamespace' => 'app\commands',
  'modules' => [
    'gii' => 'yii\gii\Module',
  ],
  'components' => [
    'cache' => [
      'class' => 'yii\caching\FileCache',
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
    'db' => $db,

    ],
    'params' => $params,
  ];
