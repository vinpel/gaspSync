<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
* @author Vincent Pélisset <vinpel@hotmail.com>
*/
class ModularAdminAsset extends AssetBundle
{
  public $sourcePath = '@bower/patternfly/dist';
  public $baseUrl = '@web';
  public $css = [
    'css/patternfly.min.css',
    'css/patternfly.css.map',

  ];
  public $js = [
    'js/patternfly.min.js'
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
  ];
}
