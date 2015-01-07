<?php

namespace app\controllers;
use yii\helpers\Markdown;

class AboutController extends \yii\web\Controller
{
    public function actionIndex()
    {
      $fileContent=file_get_contents(\Yii::getAlias('@storage/README.md'));
      $markdown = Markdown::process($fileContent,'gfm');
      return $this->render('index',['markdown'=>$markdown]);
    }

}
