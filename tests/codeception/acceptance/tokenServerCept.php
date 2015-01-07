<?php

//\Codeception\Util\Debug::debug($hawk);
$email=time().'test@exemple.com';
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that the token server works');

//get valid hawk / browserid for testing
$I->wantTo('grab test hawk & assertion for testing');
$I->sendGET('/index-test.php/token/test-get-assertion',['email'=>$email]);
$assertion=$I->grabDataFromJsonResponse("assertion");

$I->wantTo('test rejection without an assertion');
$I->sendGET('/index-test.php/tokenServer/1.0/sync/1.5');
$I->seeResponseContainsJson([
  'error' => 'Unauthorized',
  ] );
  $I->seeResponseCodeIs(401);

  $I->wantTo('get an authToken');
  $I->haveHttpHeader('authorization',$assertion);
  $I->sendGET('/index-test.php/tokenServer/1.0/sync/1.5',array());
  $I->seeResponseCodeIs(200);
  $I->seeResponseContainsJson(['status' => 'okay']);

  $uid=$I->grabDataFromJsonResponse("uid");
  $api_endpoint=$I->grabDataFromJsonResponse("api_endpoint");
  $id=$I->grabDataFromJsonResponse("id");
  $key=$I->grabDataFromJsonResponse("key");


      ?>
