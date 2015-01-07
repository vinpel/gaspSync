<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use Hawk;

use yii\helpers\Markdown;
class SiteController extends Controller
{
  public function behaviors()
  {
    return [
      'access' => [
        'class' => AccessControl::className(),
        'only' => ['logout'],
        'rules' => [
          [
            'actions' => ['logout'],
            'allow' => true,
            'roles' => ['@'],
          ],
        ],
      ],
      'verbs' => [
        'class' => VerbFilter::className(),
        'actions' => [
          'logout' => ['post'],
        ],
      ],
    ];
  }

  public function actions()
  {
    return [
      'error' => [
        'class' => 'yii\web\ErrorAction',
      ],
      'captcha' => [
        'class' => 'yii\captcha\CaptchaAction',
        'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
      ],
    ];
  }
  /**
  * Landing page for the site
  */

  public function actionIndex()
  {

    return $this->render('index',['publicURI'=>Yii::$app->params['publicURI']]);
  }
  public function actionReadme()
  {
    $myHtml = Markdown::process(file_get_contents(Yii::getAlias('@app/README.md')), 'extra');
    return $this->render('readme',['README'=>$myHtml]);
  }

/**
* send a pre-configured user.js file based on the server config
*/
  public function actionUserjs(){
    $this->layout = 'empty';
    $tt= $this->render('userjs',['publicURI'=>Yii::$app->params['publicURI']]);
    \Yii::$app->response->sendContentAsFile($tt,'user.js')->send();
  }

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

    public function actionLogin()
    {
      if (!\Yii::$app->user->isGuest) {
        return $this->goHome();
      }

      $model = new LoginForm();
      if ($model->load(Yii::$app->request->post()) && $model->login()) {
        return $this->goBack();
      } else {
        return $this->render('login', [
          'model' => $model,
          ]);
        }
      }

      public function actionLogout()
      {
        Yii::$app->user->logout();

        return $this->goHome();
      }

      public function actionContact()
      {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
          Yii::$app->session->setFlash('contactFormSubmitted');

          return $this->refresh();
        } else {
          return $this->render('contact', [
            'model' => $model,
            ]);
          }
        }

        public function actionAbout()
        {
          return $this->render('about');
        }
      }
