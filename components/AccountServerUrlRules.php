<?php
/*
Handles custom urls base on the Fxa recommendations
*/
namespace app\components;

use yii\web\UrlRule;


use app\models\FxaError;



class AccountServerUrlRules 
{
  public $connectionID = 'db';

  public function init()
  {

    if ($this->name === null) {
      $this->name = __CLASS__;
    }
  }
  /*
  Way to create custom urls
  */
  public function createUrl($manager, $route, $params)
  {

    return false;  // this rule does not apply
  }
  /**
  * Here we put custom Paths
  */
  static public function parseRequest($manager, $request)
  {
    // **
    // **

    ///v1/account/create
    $version=\Yii::$app->params['fxaVersions'];//SyncVersion ProtocoleVersion ContentVersion
    $pathInfo = $request->getPathInfo();

    $verb = $request->getMethod();
    $endPointUrl=\Yii::$app->params['endPointUrl'];


    //API ENDPOINTS : Account

    if (strcmp($verb,'GET')==0 && preg_match('#^v1/account/create#', $pathInfo, $matches)) {
      return  ['account/create',[]];
    }
    if (strcmp($verb,'GET')==0 && preg_match('#^v1/account/status#', $pathInfo, $matches)) {
      return  ['account/account-status',[]];
    }
    if (strcmp($verb,'GET')==0 && preg_match('#^v1/account/devices#', $pathInfo, $matches)) {
      return  ['account/device',[]];
    }
    if (strcmp($verb,'GET')==0 && preg_match('#^v1/account/keys#', $pathInfo, $matches)) {
      return  ['account/account-keys',[]];
    }
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/account/reset#', $pathInfo, $matches)) {
      return  ['account/account-reset',[]];
    }

    if (strcmp($verb,'POST')==0 && preg_match('#^v1/account/destroy#', $pathInfo, $matches)) {
      return  ['account/account-destroy',[]];
    }

    //API ENDPOINTS : Authentication
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/account/login#', $pathInfo, $matches)) {
      return  ['account/authentication-login',[]];
    }

    //API ENDPOINTS :  Session
    if (strcmp($verb,'GET')==0 && preg_match('#^v1/session/status#', $pathInfo, $matches)) {
      return  ['account/session-status',[]];
    }
    if (strcmp($verb,'GET')==0 && preg_match('#^v1/session/destroy#', $pathInfo, $matches)) {
      return  ['account/session-destroy',[]];
    }

    //API ENDPOINTS : Recovery Email
    if (strcmp($verb,'GET')==0 && preg_match('#^v1/recovery_email/status#', $pathInfo, $matches)) {
      return  ['account/recovery-email-status',[]];
    }
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/recovery_email/resend_code#', $pathInfo, $matches)) {
      return  ['account/recovery-email-resend-code',[]];
    }
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/recovery_email/verify_code#', $pathInfo, $matches)) {
      return  ['account/recovery-email-verify-code',[]];
    }

    //API ENDPOINTS : Certificate Signing
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/certificate/sign#', $pathInfo, $matches)) {
      return  ['account/certificate-sign',[]];
    }

    //API ENDPOINTS : Password
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/password/change/start#', $pathInfo, $matches)) {
      return  ['account/password-change-start',[]];
    }
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/password/change/finish#', $pathInfo, $matches)) {
      return  ['account/password-change-finish',[]];
    }
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/password/forgot/send_code#', $pathInfo, $matches)) {
      return  ['account/password-forgot-send-code',[]];
    }
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/password/forgot/resend_code#', $pathInfo, $matches)) {
      return  ['account/password-forgot-resend-code',[]];
    }
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/password/forgot/verify_code#', $pathInfo, $matches)) {
      return  ['account/password-forgot-verify-code',[]];
    }
    if (strcmp($verb,'GET')==0 && preg_match('#^v1/password/forgot/status#', $pathInfo, $matches)) {
      return  ['account/password-forgot-status',[]];
    }


    //API ENDPOINTS : Miscellaneous
    if (strcmp($verb,'POST')==0 && preg_match('#^v1/get_random_bytes#', $pathInfo, $matches)) {
      return  ['account/miscellaneous-random-bytes',[]];
    }
    //API ENDPOINTS : Public Keys
    if (strcmp($verb,'GET')==0 && preg_match('#^.well-known/browserid#', $pathInfo, $matches)) {
      return  ['account/get-public-key',[]];
    }


    return false;  // any rules apply
  }
}
