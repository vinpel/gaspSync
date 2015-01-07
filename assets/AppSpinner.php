<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

 /*
 using  https://github.com/mozilla/fxa-js-client
 Flatly Theme http://www.bootstrapzero.com/bootstrap-template/flatly-theme
 */
namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author pelisset vinpel@hotmail.com
 * @since 0.5
 */
class AppSpinner extends AssetBundle
{
  public $basePath = '@webroot';
  public $baseUrl = '@web';

  public $css = [
    'css/spinner.css',
  ];
  public $js = [];
}
