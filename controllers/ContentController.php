<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class ContentController extends Controller
{

  /**
  * Landing page for the site
  */
  public function actionIndex()
  {
    $this->layout='basic';
    return $this->render('index',[
      'publicURI'=>Yii::$app->params['publicURI']
    ]);
  }


}
?>
