<?php
namespace app\components;

use yii\web\UrlRuleInterface;
use yii\base\Object;


use app\models\FxaError;
use app\components\SyncUrlRules;
use app\components\AccountServerUrlRules;

/**
* This class include the 2 others rules, to have multiple Urlrules class files
*/

class IncludeAllUrlRules extends Object implements UrlRuleInterface
{
  /**
  * @inheritdoc
  */
  public function createUrl($manager, $route, $params){
    return SyncUrlRules::createUrl($manager, $route, $params);
    //return false;  // this rule does not apply
  }
  /**
  * Here we put custom Paths
  */
  public function parseRequest($manager, $request){
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
