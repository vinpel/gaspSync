<?php
/*
This class permit to have multiple Urlrules calss
*/
namespace app\components;

use yii\web\UrlRule;


use app\models\FxaError;
use app\components\SyncUrlRules;
use app\components\AccountServerUrlRules;


class UrlRules extends UrlRule
{
  public $connectionID = 'db';

  public function init()
  {

    if ($this->name === null) {
      $this->name = __CLASS__;
    }
  }

  public function createUrl($manager, $route, $params)
  {
    return false;  // this rule does not apply
  }
  /**
  * Here we put custom Paths
  */
  public function parseRequest($manager, $request)
  {
    $ret=SyncUrlRules::parseRequest($manager,$request);
    if ($ret !==false){
      return $ret;
    }
    $ret=AccountServerUrlRules::parseRequest($manager,$request);
    if ($ret !==false){
      return $ret;
    }
    return false;  // any rules apply
  }
}
