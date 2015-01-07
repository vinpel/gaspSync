<?php

//\Codeception\Util\Debug::debug($hawk);
$email=time().'test@exemple.com';
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that the content server works');

$I->wantTo('have "scrypt" PHP extension loaded (too slow without it)');
$I->assertTrue(extension_loaded("scrypt"));



$I->wantTo('get landing page ');
$I->sendGET('/index-test.php');
$I->seeResponseCodeIs(200);
$I->see('Sync Storage configuration');


$I->wantTo('get login apage ');
$I->sendGET('/index-test.php/content/');
$I->seeResponseCodeIs(200);
$I->see('Create account');

$I->wantTo('Create an account');
$data['email']="test@test.com";
$data['authPW']="04253ecdf6ab0bf828e8bbdaade510575a86d5dd5e3b77f3ebfaada9a19a1390";

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/index-test.php/v1/account/create?keys=true',$data);
?>
