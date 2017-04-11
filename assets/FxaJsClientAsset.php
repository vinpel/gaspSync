<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
* Script Javascript permettant de gerer la crypto coté client
* @author pelisset vinpel@hotmail.com
* @since 0.5
*/
class FxaJsClientAsset extends AssetBundle{

  public $sourcePath = '@bower';
  public $css = [ ];
  public $js = [
    'fxa-js-client/fxa-client.min.js',
  ];
  public $depends = [
    'app\assets\AppSpinner',
  ];

}
