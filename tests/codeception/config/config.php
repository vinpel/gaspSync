<?php
/**
* Application configuration shared by all test types
*/
return [
  'aliases' => [
    '@storage' => '@app/storage',
  ],
  'components' => [
    /*'db' => [
      'dsn' => 'mysql:host=localhost;dbname=sync',
    ],*/
    'mailer' => [
      'useFileTransport' => true,
    ],
    'urlManager' => [
      'showScriptName' => true,
    ],
    'request' => [
      'parsers' => [
        'application/json' => 'yii\web\JsonParser',
        ]
      ],
      'response' => [
        //   'format' => yii\web\Response::FORMAT_JSON,
        'charset' => 'UTF-8',
      ],

    ],

  ];
