<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
* Utilisation du thème Gentelella
* @author Pélisset Vincent <vpelisset@oisehabitat.fr>
*/
class GentelellaAsset extends AssetBundle{

  public $sourcePath = '@bower/gentelella/';

  public $css = [
    'build/css/custom.min.css',
    'vendors/nprogress/nprogress.css',

  ];
  public $js = [
    'build/js/custom.min.js',
    'vendors/fastclick/lib/fastclick.js'
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
    'yii\bootstrap\BootstrapPluginAsset',
    'yii\web\JqueryAsset',
    '\rmrevin\yii\fontawesome\AssetBundle'
  ];
}

?>
