<?php
/**
 * Application configuration for unit tests
 */
return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../config/web.php'),
    require(__DIR__ . '/config.php'),
    [
      'params'=>['publicURI' => 'http://localhost:8080/index-test.php',],
    ]
);
