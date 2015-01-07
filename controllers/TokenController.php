<?php
namespace app\controllers;
use yii\web\Response;
use BrowserID;

use app\models\Token;
use app\models\FxaError;
class TokenController extends \yii\web\Controller
{
  public $enableCsrfValidation = false;
  /**
  *
  */
  public function __construct($id, $module, $config = []){
    parent::__construct($id, $module, $config = []);
  }
  /**
  *
  */
  public function actionIndex()
  {
    return $this->render('index');
  }
  /**
  * used in unit test to give a valid assertion and hawkID 
  */
  public function actionTestGetAssertion($email)
  {
    if (!defined('YII_ENV') || YII_ENV!='test'){
      return FxaError::fxaAuthError(998,'Not in testmode');
    }
    $myToken=new Token();
    try{
      //Fake assertion
      $assertion = $myToken->createAssertion($email,\Yii::$app->params['publicURI']);
      //Yii2 put all header in lower case ...
      \Yii::$app->request->headers->set('Authorization',$assertion);
      $ret['assertion']=$assertion;
      //we need to get the secret Key
      $authToken= $myToken->createAuthToken();
      $hawk = \Hawk\Hawk::generateHeader($authToken['id'], $authToken['key'], 'GET',
      \Yii::$app->params['publicURI'].'/'.\Yii::$app->request->url);
      $ret['hawk']=$hawk;
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return $ret;
    }
    catch (\Exception $e) {
      print $e->getMessage() ;
    }
  }
  /**
  * used in unit test to give a valid hawk for a specified url
  */
  public function actionTestGetHawk($id,$key,$url,$verb='GET')
  {

    if (!defined('YII_ENV') || YII_ENV!='test'){
      return FxaError::fxaAuthError(998,'Not in testmode');
    }
    \Yii::info('aurl : '.$url);
    $myToken=new Token();
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $ret['hawk'] = \Hawk\Hawk::generateHeader($id, $key, $verb, $url);
    return $ret;
  }
  /**
  * Create an auth Token
  * The assertion is checked inside createAuthToken
  */
  public function actionCreatetoken(){
    $token= new Token();
    try{
      $monToken=$token->createAuthToken();
    }
    catch (\Exception $e){
      return FxaError::fxaAuthError(110,'Createtoken : ' .$e->getMessage());
    }
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $monToken;
  }
}
?>
