<?php

namespace app\controllers;

use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use Hawk;

use yii\helpers\Markdown;
/**
* Landing page
*/
class SiteController extends Controller
{
  /**
  * @inheritdoc
  */
  public function actions(){
    return [
      'error' => [
        'class' => 'yii\web\ErrorAction',
      ],
    ];
  }
  /**
  * Landing page for the site
  */
  public function actionIndex(){
    return $this->render('index',[
      'publicURI'=>Yii::$app->params['publicURI']
    ]);
  }
  /**
  * Affichage du readme
  */
  public function actionReadme(){
    $myHtml = Markdown::process(file_get_contents(Yii::getAlias('@app/README.md')), 'extra');// gfm?

    return $this->render('readme',[
      'README'=>$myHtml
    ]);
  }

  /**
  * send a pre-configured user.js file based on the server config
  */
  public function actionUserjs(){
    $this->layout = 'empty';
    $userjs= $this->render('userjs',[
      'publicURI'=>Yii::$app->params['publicURI']
    ]);
    \Yii::$app->response->sendContentAsFile($userjs,'user.js')->send();
  }
  /**
  * ?
  */
  public function actionConfig(){
    $tbl=json_decode('{"cookiesEnabled":true,
      "fxaccountUrl":"https://api.accounts.firefox.com/v1",
      "oauthUrl":"https://oauth.accounts.firefox.com",
      "profileUrl":"https://profile.accounts.firefox.com",
      "oauthClientId":"ea3ca969f8c6bb0d",
      "language":"fr","metricsSampleRate":0.1}');
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return $tbl;
    }

  }
