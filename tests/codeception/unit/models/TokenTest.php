<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;
use app\models\Token;

class TokenTest extends TestCase
{

  private $browserIDToken;
  private $audience;
  protected function setUp()
  {
    parent::setUp();
    $this->audience=\Yii::$app->params['publicURI'];
    // uncomment the following to load fixtures for user table
    //$this->loadFixtures(['user']);
  }
  public function testTokenCreateVerifyAssert(){
    $Authorization='BrowserID eyJhbGciOiJSUzI1NiJ9.eyJmeGEtZ2VuZXJhdGlvbiI6MTQxMzUzMzYxMzMzNSwiZnhhLWxhc3RBdXRoQXQiOjE0MTM3MDk4NzIsImZ4YS12ZXJpZmllZEVtYWlsIjoic3luYzRAcGVsaXNzZXQuY29tIiwicHVibGljLWtleSI6eyJhbGdvcml0aG0iOiJEUyIsInkiOiJjZWVlYWUwNGEyNTU0OGNhMDMxNDMxYjdjOGE1ODE3OWVlMmFmMDgzZTliNzE5Y2QzNDVlMjQ4NmJhYjYyM2YxMzdjZWIxYjY2NGRiYzY4YmFjZTQyOGFjMmM4M2M1MThhZjJkZTVkZWFkMWViODEzZmZlZWRiMDU3ODhhYzEwZjQzODhkZmI4Y2NhNmQzYzlkNzQxYTQ2NmEyY2ZkYzQyMWM5ZWY0ZjZmOWRhNmY1Mzg0YjI3ZDczNjExZWM0NDZhM2Y0YjdhMzE3NDNhZGFjMjMxMWIzMGZlZDU3YzAxZDcwMGFiOTdjY2RiYTczZjhjZjFjOTJjMDRmZmZhMTMwIiwicCI6ImZmNjAwNDgzZGI2YWJmYzViNDVlYWI3ODU5NGIzNTMzZDU1MGQ5ZjFiZjJhOTkyYTdhOGRhYTZkYzM0ZjgwNDVhZDRlNmUwYzQyOWQzMzRlZWVhYWVmZDdlMjNkNDgxMGJlMDBlNGNjMTQ5MmNiYTMyNWJhODFmZjJkNWE1YjMwNWE4ZDE3ZWIzYmY0YTA2YTM0OWQzOTJlMDBkMzI5NzQ0YTUxNzkzODAzNDRlODJhMThjNDc5MzM0MzhmODkxZTIyYWVlZjgxMmQ2OWM4Zjc1ZTMyNmNiNzBlYTAwMGMzZjc3NmRmZGJkNjA0NjM4YzJlZjcxN2ZjMjZkMDJlMTciLCJxIjoiZTIxZTA0ZjkxMWQxZWQ3OTkxMDA4ZWNhYWIzYmY3NzU5ODQzMDljMyIsImciOiJjNTJhNGEwZmYzYjdlNjFmZGYxODY3Y2U4NDEzODM2OWE2MTU0ZjRhZmE5Mjk2NmUzYzgyN2UyNWNmYTZjZjUwOGI5MGU1ZGU0MTllMTMzN2UwN2EyZTllMmEzY2Q1ZGVhNzA0ZDE3NWY4ZWJmNmFmMzk3ZDY5ZTExMGI5NmFmYjE3YzdhMDMyNTkzMjllNDgyOWIwZDAzYmJjNzg5NmIxNWI0YWRlNTNlMTMwODU4Y2MzNGQ5NjI2OWFhODkwNDFmNDA5MTM2YzcyNDJhMzg4OTVjOWQ1YmNjYWQ0ZjM4OWFmMWQ3YTRiZDEzOThiZDA3MmRmZmE4OTYyMzMzOTdhIn0sInByaW5jaXBhbCI6eyJlbWFpbCI6ImIyNjQwNjZkMmI2ZTRjZTk4OGNjNTVjMmEwOTQ3NDk3QGFwaS5hY2NvdW50cy5maXJlZm94LmNvbSJ9LCJpYXQiOjE0MTM3MDk4NjM1ODYsImV4cCI6MTQxMzczMTQ3MzU4NiwiaXNzIjoiYXBpLmFjY291bnRzLmZpcmVmb3guY29tIn0.QJFzrq9SwNUhmTIany4hpIFp2DO7GR_lz2Ba85zAPF9cI780-rufYlAHNDsOEMPPayrEUXCjEr0Pj_WXU1NjRjsDGMnSDY7rL5_xgAa6QbqzOOMk9ZQIRbA2iBqRi6_xET97vAyDFvl52zFyG3WCJCmwkchcJOxgPKFPpwE70mELOMBvV9UQ_aC9CBOBDxi-PXtshVKq83sVU00eJuZsuVRG-cReJ_xZletRZS9nWw7jqWWbDERB_RXccZ50Xzr3qR23RuFL5ZUvnoxYrASg5kkjsxDoc9WzaY0l25dw0yVxneA_wSD5xoX_PnweyP67EImokan5DJGdaYiYbeV9~eyJhbGciOiJEUzEyOCJ9.eyJleHAiOjIyMDIxMTIwMjEzNTYsImF1ZCI6Imh0dHBzOi8vMTkyLjE2OC4wLjQ5In0=.sn-lXfcN-XkPiX_ZbkUP9AgHHY_hnA-yyHFd0zko35NB2iV3w9Piwg==';
    \Yii::$app->request->headers->set('AUTHORIZATION',$Authorization);
    $token= new Token();
    $this->browserIDToken=$token->createAssertion('testtest@signedMessage.com',$this->audience);
    try{
      $ret=$token->verifyAssertion($this->audience,$this->browserIDToken);
    }
    catch (\Exception $e) {
      \Codeception\Util\Debug::debug($e->getMessage());
      $ret=false;
    }
    if ($ret['status']=='okay'){
      $ret=true;
    }
    $this->assertTrue($ret);
  }
  public function testTokenExpiredAssertion(){
    # Expired issuer : firefox
    $audience='token.services.mozilla.com';
    $oldAssertion='eyJhbGciOiJSUzI1NiJ9.eyJmeGEtZ2VuZXJhdGlvbiI6MTQwOTgyMzM5NzkzMywiZnhhLWxhc3RBdXRoQXQiOjE0MTA1ODkwOTQsImZ4YS12ZXJpZmllZEVtYWlsIjoic3luYzJAcGVsaXNzZXQuY29tIiwicHVibGljLWtleSI6eyJhbGdvcml0aG0iOiJEUyIsInkiOiJhYzNjMTMxNGNmNmE3NTRjZWIxYzgyZTdjMGEyMWExZWI0MzgyMmFmMDQxNmE4OGFkYTNmNTYwNzRmMGMwMDFhMzRkMzAxN2M0YjQxYzFkYmZlZjY1M2YzOTQyOGE2YTg1YjI0ZTU4MTlhOWFjM2E5NWI5NmM3MzA1YmJiZTlkZDY3YTNkZTU0YmQ4Y2M1YjNkMjc4OGM1NzA1ZmNmMTllNzIwYzI4NTdmZWE3OGQ2MGRmYWE2ZmE0NzA1OWNjMDU3NmYxOTM3ZjRiYmNjMmM0NjE0NjE3YjQ2ZTk2ODllZjI3ZTc4MGRkMmE5NjM0MjJkYmVkZDU0MDI1ZDBlNWQ3IiwicCI6ImZmNjAwNDgzZGI2YWJmYzViNDVlYWI3ODU5NGIzNTMzZDU1MGQ5ZjFiZjJhOTkyYTdhOGRhYTZkYzM0ZjgwNDVhZDRlNmUwYzQyOWQzMzRlZWVhYWVmZDdlMjNkNDgxMGJlMDBlNGNjMTQ5MmNiYTMyNWJhODFmZjJkNWE1YjMwNWE4ZDE3ZWIzYmY0YTA2YTM0OWQzOTJlMDBkMzI5NzQ0YTUxNzkzODAzNDRlODJhMThjNDc5MzM0MzhmODkxZTIyYWVlZjgxMmQ2OWM4Zjc1ZTMyNmNiNzBlYTAwMGMzZjc3NmRmZGJkNjA0NjM4YzJlZjcxN2ZjMjZkMDJlMTciLCJxIjoiZTIxZTA0ZjkxMWQxZWQ3OTkxMDA4ZWNhYWIzYmY3NzU5ODQzMDljMyIsImciOiJjNTJhNGEwZmYzYjdlNjFmZGYxODY3Y2U4NDEzODM2OWE2MTU0ZjRhZmE5Mjk2NmUzYzgyN2UyNWNmYTZjZjUwOGI5MGU1ZGU0MTllMTMzN2UwN2EyZTllMmEzY2Q1ZGVhNzA0ZDE3NWY4ZWJmNmFmMzk3ZDY5ZTExMGI5NmFmYjE3YzdhMDMyNTkzMjllNDgyOWIwZDAzYmJjNzg5NmIxNWI0YWRlNTNlMTMwODU4Y2MzNGQ5NjI2OWFhODkwNDFmNDA5MTM2YzcyNDJhMzg4OTVjOWQ1YmNjYWQ0ZjM4OWFmMWQ3YTRiZDEzOThiZDA3MmRmZmE4OTYyMzMzOTdhIn0sInByaW5jaXBhbCI6eyJlbWFpbCI6IjBmOGFhYmE5YjkwNzQyNjFhYjllMDRhY2Y5Y2FlMWFkQGFwaS5hY2NvdW50cy5maXJlZm94LmNvbSJ9LCJpYXQiOjE0MTA1ODkwODUwODksImV4cCI6MTQxMDYxMDY5NTA4OSwiaXNzIjoiYXBpLmFjY291bnRzLmZpcmVmb3guY29tIn0.CE12inB7YQky3qJpQBYWmJx-ieLS5uZKq6rXtZlV_AVPifMZSPUDxj2MmzhPypqzTlgGKMWTmLpTwtZWYFAmM8yaOuwAVG0c3S5GSs1v741h7HoBcgshqJFvCDUDvhCYzDlz1wDupAAjMpAXMoZtvE_YRu9yh9S8xWCRRv2XG9tDRny3BziMWOT83N9PU7je9bgwkLTZ-OYtXl4xi30u7Nyf8cGsuudqeY79VH1Ut3sZg0ylAiImE_TN5fbsTFudMzD3r9cTbeUMuFBAlfShQ2yDBzMU6RTM6Nw94mMSSwl1_wZ-gFfFalDK8QoSWGwBRxH6_GMR5p_C3ePeHZQasw~eyJhbGciOiJEUzEyOCJ9.eyJleHAiOjIxOTg5ODkwOTUwMDAsImF1ZCI6Imh0dHBzOi8vdG9rZW4uc2VydmljZXMubW96aWxsYS5jb20ifQ==.b4XRvihqq2yBJvE06yNZySRr0Qh_W2IjeHLld1hSqPBBQyYDVBKaIA==';
    $token= new Token();
    try{
      $token->verifyAssertion($audience,$oldAssertion);
      $ret=true;
    }
    catch (\Exception $e) {

      if (strpos($e->getMessage(),'assertion has expired')!== false)
      $ret=true;
      else
      $ret=false;
    }
    $this->assertTrue($ret);
  }


  # Wrong audience
  public function testTokenWrongAudience(){
    $wrongAudience ='https://192.168.0.491';
    $token= new Token();
    $browserIDCorrectAssertion=$token->createAssertion('testtest@signedMessage.com',$this->audience);
    $ret=false;
    try {
      $a=$token->verifyAssertion($wrongAudience,$browserIDCorrectAssertion);
    }
    catch (\Exception $e) {
      if (strpos($e->getMessage(),'domain mismatch' )!== false
       || strpos($e->getMessage(),'port mismatch' )!== false
       || strpos($e->getMessage(),'scheme mismatch' )!== false) {
        $ret=true;
      }
      else {
        \Codeception\Util\Debug::debug($e->getMessage());
        $ret=false;
      }
    }
    $this->assertTrue($ret);
  }
  /**
  * Create a valide token
  */
  public function testTokenProper(){
    $token= new Token();
    # Proper token == valid.
    $createdToken=$token->makeToken('{"hello": "world"}');
    $ret=true;
    try {
      $data=$token->verifyToken($createdToken);
    }
    catch (\Exception $e) {
      $ret=false;
    }
    if ($ret!==false){
      $ret=$data['hello']==="world";
    }
    $this->assertTrue($ret);
  }
  /**
  * Badly-encoded bytes == not valid.
  */
  public function testTokenBadlyEncoded(){
    $token = new Token();
    $goodToken=$token->makeToken('{"hello": "world"}');
    $bad_token = "@°°".$goodToken;
    try{
      $token->verifyToken($bad_token);
    } catch (\Exception $e){
      if (strpos($e->getMessage(),'Invalid decode_token_bytes' )!== false){
        $ret=true;
      } 	else {
        $ret=false;
      }
    }
    $this->assertTrue($ret);
  }
  /**
  * Badly-encoded json data == not valid.
  */
  public function testTokenBadJson(){
    $bad_token=base64_encode(str_repeat('X',50));
    $token = new Token();
    try{
      $token->verifyToken($bad_token);
    }
    catch (\Exception $e){if (strpos($e->getMessage(),'Invalid json payload' )!== false) $ret=true;	else $ret=false;}
    $this->assertTrue($ret);
  }
  /**
  * Bad signature == not valid.
  */
  public function testTokenBadSignature(){
    $tokenC = new Token();
    $token=$tokenC->makeToken('{"hello": "world"}');
    $token_bytes=$tokenC->decode_token_bytes($token);
    $bad_token=substr($token_bytes,0,strlen($token_bytes)-6).'000000'; //mess the signature
    $bad_token_bytes=$tokenC->encode_token_bytes($bad_token);
    try{
      $tokenC->verifyToken($bad_token_bytes);
    } catch (\Exception $e){ if (strpos($e->getMessage(),'Invalid tokenSignature' )!== false) $ret=true;	else $ret=false;}
    $this->assertTrue($ret);
  }
  /**
  * Modified payload == not valid.
  */
  public function testTokenModifiedPayload(){
    $tokenC = new Token();
    $token=$tokenC->makeToken('{"hello": "world"}');
    $token_bytes=$tokenC->decode_token_bytes($token);
    $jSonData=substr($token_bytes,0,strlen($token_bytes)-$tokenC->hashmod_digest_size);
    $sigData=substr($token_bytes,strlen($token_bytes)-$tokenC->hashmod_digest_size,strlen($token_bytes));
    $jSonData=json_decode($jSonData);
    $jSonData->salt=bin2hex(openssl_random_pseudo_bytes(3));
    $bad_token=$tokenC->encode_token_bytes(json_encode($jSonData).$sigData);
    try{
      $tokenC->verifyToken($bad_token);
    } catch (\Exception $e){ if (strpos($e->getMessage(),'Invalid tokenSignature' )!== false) $ret=true;	else $ret=false;}
    $this->assertTrue($ret);
  }
  /**
  *
  */
  public function testTokenExpired(){
    $tokenC = new Token(['timeout'=>0.2]);
    # Expired token == not valid.
    $token=$tokenC->makeToken('{"hello": "world"}');
    sleep(1);
    $tokenC2 = new Token();
    try{
      $r=$tokenC2->verifyToken($token);
      $ret=false; // an expired token provoque an assertion
    }
    catch (\Exception $e) {
      if (strpos($e->getMessage(),'Invalid token : expired' )!== false) {
        $ret=true;
      }	else {
        $ret=false;
      }
    }
    $this->assertTrue($ret);
  }
  /**
  * test_loading_hashmod_by_string_name
  */
  public function testTokenMd5Hashmode(){
    $params['hashmode']='md5';
    $myTokenMd5=new Token($params);
    $token=$myTokenMd5->makeToken('{"hello": "world"}');
    $data=$myTokenMd5->verifyToken($token);
    $this->assertTrue($data['hello']==="world");
  }
  /**
  * test_token_secrets_differ_for_each_token
  */
  public function testTokenWrongSecret(){
    $payload='{"hello": "world"}';
    $myToken=new Token();
    $token=$myToken->makeToken($payload);
    $myToken->changeSecretToken(bin2hex(openssl_random_pseudo_bytes(32)));
    $token2=$myToken->makeToken($payload);
    $this->assertFalse($token===$token2);
  }
  /**
  *
  */
  public function testHawkIdentification(){
    $myToken=new Token();
    try{
      $assertion = $myToken->createAssertion('testtest@signedMessage.com',\Yii::$app->params['publicURI']);
      \Yii::$app->request->headers->set('authorization',$assertion);
      $token= $myToken->createAuthToken();
      //Fake assertion
      $assertion = $myToken->createAssertion('testtest@signedMessage.com',\Yii::$app->params['publicURI']);
      \Yii::$app->request->headers->set('authorization',$assertion);

      $authToken= $myToken->createAuthToken();
      $hawk = \Hawk\Hawk::generateHeader($authToken['id'], $authToken['key'], 'GET', \Yii::$app->params['publicURI']);

      $res=$myToken->verifyHawk($hawk,\Yii::$app->params['publicURI']);

      $ret=true;
    } catch (\Exception $e){
      \Codeception\Util\Debug::debug($e->getMessage().\Yii::$app->params['publicURI']);
      $ret=false;
    }
    $this->assertTrue($ret);
  }
}
?>
