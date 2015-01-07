<?php

namespace app\Controllers;

use app\models\AccAccounts;
use app\models\FxaError;
use app\models\AccKeyfetchtokens;

use app\models\AccSessionTokens;
use Crypto\Crypto;


class AccountController extends \yii\web\Controller
{
  public $enableCsrfValidation = false;

  private $uid = null;

  private $compte  = null; //the current user

  /**
  * Creation of an account
  * POST /v1/account/create
  */
  public function actionCreate($keys=false){
    //Only for testing :p
    \Yii::$app->request->rawBody='{"email":"test@test.com","authPW":"04253ecdf6ab0bf828e8bbdaade510575a86d5dd5e3b77f3ebfaada9a19a1390"}';

    $postJson=json_decode(\Yii::$app->request->rawBody,true);
    if (json_last_error()!==JSON_ERROR_NONE
    || $postJson==null
    ||!isset($postJson['email'])
    ||!isset($postJson['authPW'])){
      return FxaError::fxaAuthError(404,'Invalid Json');
    }

    //file_put_contents('/var/www/yii/basic/body.txt',print_r($postJson,true).'-',FILE_APPEND);
    //Testing if we want to create another account with existing email
    $normalizedEmail=strtolower(trim($postJson['email']));

    $nb=AccAccounts::find()->where(['normalizedEmail'=>$normalizedEmail])->count();
    $data['nb']=$nb;
    //Only for testing !=
    if ($nb!=0){
      return FxaError::fxaAuthError(101);
    }

    $this->compte=new AccAccounts();
    $this->compte->normalizedEmail=$normalizedEmail;


    $this->uid = $this->getUid();
    $this->compte->uid = $this->uid;


    $this->compte->email=$postJson['email'];
    $this->compte->emailCode='';
    $this->compte->emailVerified=false;
    $this->compte->authSalt=	   $this->getRand();
    $this->compte->kA=			     $this->getRand();
    $this->compte->wrapWrapKb=	 $this->getRand();

    $bigStretchedPW= $this->getBigStretchedPW($postJson['authPW'],$this->compte->authSalt);

    $this->compte->verifyHash=   $this->getVerifyHash($bigStretchedPW);
    $this->compte->verifierVersion=1;	//we use Scrypt
    $this->compte->verifierSetAt=1;
    //save the created user
    if (!$this->compte->save()){
      \Yii::warning($this->compte->getErrors());
      return FxaError::fxaAuthError(400,'error saving account');
    }

    $jsonData['sessionToken']=$this->getSessionToken();

    if (\Yii::$app->request->get('keys')!==null){
      $jsonData['keyFetchToken']=$this->getKeyFetchToken($jsonData['sessionToken'],$bigStretchedPW);
    }

    $jsonData['uid']=$this->compte->uid;
    $jsonData['authAt']=decistamp();//second since epoch


/*
    {"uid":"acdbcf5254c34d74b30fe45c3cea6018",
      "sessionToken":"12b06df038bcfd90693277a2e93ffb8165bf34ee9a8fe3de079614e9e2555e68",
      "keyFetchToken":"3d9b1b0d3a1e031ffe57033f98abf0c0d9d4cfebd7dbf9622edaaf23eb8da271",
      "authAt":1412421388}
*/
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $jsonData;
  }
  /**
  * Gets the status of an account
  */
  public function actionAccountStatus(){
    $status=AccAccounts::find()->Where(['uid'=>\Yii::$app->request->get('uid')])->count();

    if ($status>0){
      $res['exists']=true;
    }
    else{
      $res['exists']=false;
    }
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $res;
  }

  /**
  *:lock: HAWK-authenticated with sessionToken
  *
  * Gets the collection of devices currently authenticated and syncing for the user.
  * The request must include a Hawk header that authenticates the request using a sessionToken received from /v1/account/login.
  * TODO the read stuff

  * status code 401, errno 109: invalid request signature
  * status code 401, errno 110: invalid authentication token
  * status code 401, errno 111: invalid authentication timestamp
  * status code 401, errno 115: invalid authentication nonce

  */
  public function actionDevice(){
    // get list of sessionToken for the account
    // test all for the hawk identification with the provided Hawk
    $res['devices']=[
      [
        'id'=>'4c352927-cd4f-4a4a-a03d-7d1893d950b8',
        'type'=>'computer',
        'name'=>"Foxy's Macbook",
      ],
      [
        'id'=>'FF352927-cd4f-4a4a-a03d-7d1893d950b8',
        'type'=>'B2B',
        'name'=>"Foxy's phone",
      ],
    ];
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $res;
  }
  /**
  * Is the email in an account verified ?
  * (by session.tokenID)
  * path : /recovery_email/status
  */
  public function actionEmailStatus(){
    //sessionToken -> session tokenId
    $sessionToken=\Yii::$app->request->get('sessionToken');
    $sessionToken='de73e4bea42143f4834bfff55ddff79dc59af824be2510f4eb43552974b8fb17';
    if ($sessionToken===null){
      return FxaError::fxaAuthError(400,'missing sessionToken');
    }
    $status=AccAccounts::find()
    ->joinWith('accSessionTokens')
    ->Where(['TokenId'=>$sessionToken])
    ->One();
    if ($status->emailVerified==1){
      $status->emailVerified=true;
    }
    else{
      $status->emailVerified=false;
    }
    $jsonData=array('verified'=>($status->emailVerified),'email'=>$status->normalizedEmail);
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $jsonData;
  }


  public function emailVerified($uid){
    $status=AccAccounts::find()
    ->select('emailVerified')
    ->Where(['uid'=>$uid])
    ->One();
    if ($status->emailVerified==0){
      return false;
    }
    else{
      return true;
    }
  }

  /**
  * GET /v1/account/keys (:lock: keyFetchToken) (verf-required)
  * :lock: HAWK-authenticated with keyFetchToken
  * Get the base16 bundle of encrypted kA|wrapKb.
  * The return value must be decrypted with a key derived from keyFetchToken,
  * and then wrapKb must be further decrypted with a key derived from the user's password.
  * Since keyFetchToken is single-use, this can only be done once per session.
  * Note that the keyFetchToken is consumed regardless of whether the request succeeds or fails.
  * This request will fail unless the account's email address has been verified.
  * TODO
  */
  public function actionAccountKeys(){
    $uid='027ad496fa1c40448edd967a3dedd858';
    if (!$this->emailVerified($uid)){
      return FxaError::fxaAuthError(104);
    }
    $data["bundle"]="d486e79c9f3214b0010fe31bfb50fa6c12e1d093f7770c81c6b1c19c7ee375a6558dd1ab38dbc5eba37bc3cfbd6ac040c0208a48ca4f777688a1017e98cedcc1c36ba9c4595088d28dcde5af04ae2215bce907aa6e74dd68481e3edc6315d47efa6c7b6536e8c0adff9ca426805e9479607b7c105050f1391dffed2a9826b8ad";
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $data;

  }
  /**
  * :lock: HAWK-authenticated with accountResetToken
  * This sets the account password and resets wrapKb to a new random value.
  * The accountResetToken is single-use, and is consumed regardless of whether the request succeeds or fails.
  * POST /v1/account/reset
  * TODO
  */
  public function actionAccountReset(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }
  /**
  * This deletes the account completely.
  * All stored data is erased.
  * The client should seek user confirmation first.
  * The client should erase data stored on any attached services before deleting the user's account data.
  * Parameters :
  *  email - the account email address
  *  authPW - the PBKDF2/HKDF stretched password as a hex string
  */
  public function actionAccountDestroy(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }

  /**
  * POST /v1/account/login
  * Obtain a "sessionToken" and optionally a "keyFetchToken" by adding the query parameter "keys=true".
  * TODO
  */
  public function actionAuthenticationLogin(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }

  /**
  * GET /v1/session/status
  * lock: HAWK-authenticated with the sessionToken.
  * The request will return a success response as long as the token is valid.
  * TODO
  */
  public function actionSessionStatus(){
    $data["uid"]= "80dc2f2e373b4b3bb992468e6d578cd2";
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $data;
  }

  /**
  * POST /v1/session/destroy
  * :lock: HAWK-authenticated with the sessionToken.
  * Destroys this session, by invalidating the sessionToken.
  * This is used when a device "signs-out", detaching itself from the account.
  * After calling this, the device must re-perform the /v1/account/login sequence to obtain a new sessionToken.
  * TODO
  */
  public function actionSessionDestroy(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }
  /**
  * GET /v1/recovery_email/status
  * :lock: HAWK-authenticated with the sessionToken.
  * Returns the "verified" status for the account's recovery email address.
  * Currently, each account is associated with exactly one email address.
  * This address must be "verified" before the account can be used (specifically,
  * /v1/certificate/sign and /v1/account/keys will return errors until the address is verified).
  *  In the future, this may be expanded to include multiple addresses, and/or alternate types
  * of recovery methods (e.g., SMS). A new API will be provided for this extra functionality.
  * This call is used to determine the current state (verified or unverified) of the recovery email address.
  * During account creation, until the address is verified, the agent can poll this method to discover
  * when it should proceed with /v1/certificate/sign and /v1/account/keys.
  *
  * Failing requests may be due to the following errors:
  * - status code 401, errno 109: invalid request signature
  * - status code 401, errno 110: invalid authentication token
  * - status code 401, errno 111: invalid authentication timestamp
  * - status code 401, errno 115: invalid authentication nonce
  * TODO
  */
  public function actionRecoveryEmailStatus(){
    $Json['email']='sync2@pelisset.com';
    $Json['verified']=False;

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $Json;
  }
  /**
  * POST /v1/recovery_email/resend_code (:lock: sessionToken)
  * :lock: HAWK-authenticated with the sessionToken.
  * Re-sends a verification code to the account's recovery email address.
  * The code is first sent when the account is created, but if the user thinks
  * the message was lost or accidentally deleted, they can request a new message to be sent with this endpoint.
  * The new message will contain the same code as the original message. When this code is provided to
  * /v1/recovery_email/verify_code (below), the email will be marked as "verified".
  * This endpoint may send a verification email to the user. Callers may optionally provide the service
  * parameter to indicate what Identity-Attached Service they are acting on behalf of.
  * This is an opaque alphanumeric token which will be embedded in the verification link as a query parameter.
  * TODO
  */
  public function actionRecoveryEmailResendCode(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }
  /**
  * POST /v1/recovery_email/verify_code
  * Not HAWK-authenticated.
  * Used to submit a verification code that was previously sent to a user's recovery email.
  * If correct, the account's recovery email address will be marked as "verified".
  * The verification code will be a random token, delivered in the fragment portion of a URL sent
  * to the user's email address. The URL will lead to a page that extracts the code from the URL fragment,
  * and performs a POST to /recovery_email/verify_code. This endpoint should be CORS-enabled,
  * to allow the linked page to be hosted on a different (static) domain.
  * The link can be clicked from any browser, not just the one being attached to the PICL account.
  */
  public function actionRecoveryEmailVerifyCode(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }
  /**
  * POST  /v1/certificate/sign
  * :lock: HAWK-authenticated with the sessionToken.
  * Sign a BrowserID public key. The server is given a public key, and returns a signed certificate using
  * the same JWT-like mechanism as a BrowserID primary IdP would (see the browserid-certifier project). .
  * The signed certificate includes a principal.email property to indicate the Firefox Account identifier
  * (a uuid at the account server's primary domain) and is stamped with an expiry time based on the "duration" parameter.
  * This request will fail unless the account's email address has been verified.
  * le cert est signé :
  * {"alg":"RS256"}
  * {   "fxa-generation":1409823397933,
  *     "fxa-lastAuthAt":1413447532,
  *     "fxa-verifiedEmail":"sync2@pelisset.com",
  *     "public-key":{"algorithm":"DS",
  *     "y":"68b237f5e8d80bd45032ab89540471cff733b79fe79bbcc6e76f038337ebea05308dd5ebfc5afdcf9e6814af906394f39ec88b132fc28447535b6e02f8199ee49530aa60373e7903082aad06df758ee20d1c3de60b4a64d4f1377848369be4a4cdef1ec4cb6ead44e8b450c7b02be5e3c433175c446155159427b2005826170e",
  *     "p":"ff600483db6abfc5b45eab78594b3533d550d9f1bf2a992a7a8daa6dc34f8045ad4e6e0c429d334eeeaaefd7e23d4810be00e4cc1492cba325ba81ff2d5a5b305a8d17eb3bf4a06a349d392e00d329744a5179380344e82a18c47933438f891e22aeef812d69c8f75e326cb70ea000c3f776dfdbd604638c2ef717fc26d02e17",
  *     "q":"e21e04f911d1ed7991008ecaab3bf775984309c3",
  *     "g":"c52a4a0ff3b7e61fdf1867ce84138369a6154f4afa92966e3c827e25cfa6cf508b90e5de419e1337e07a2e9e2a3cd5dea704d175f8ebf6af397d69e110b96afb17c7a03259329e4829b0d03bbc7896b15b4ade53e130858cc34d96269aa89041f409136c7242a38895c9d5bccad4f389af1d7a4bd1398bd072dffa896233397a"
  * },
  *"principal":
  * {
  *     "email":"0f8aaba9b9074261ab9e04acf9cae1ad@api.accounts.firefox.com"},
  *     "iat":1413451497373,
  *     "exp":1413473107373,
  *     "iss":"api.accounts.firefox.com"
  *}
  * https://github.com/mozilla/fxa-auth-server/blob/master/docs/api.md#get-v1recovery_emailstatus
  * TODO
  */
  public function actionCertificateSign(){
    $jsonData=['err'=>null,'cert'=>""];
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $jsonData;
  }

  /**
  * POST /v1/password/change/start
  * Begin the "change password" process. It returns a single-use passwordChangeToken,
  *  which will be delivered to /v1/password/change/finish. It also returns a single-use keyFetchToken.
  * Parameters
  *   email - the account email address
  *   oldAuthPW - the PBKDF2/HKDF stretched password as a hex string
  * TODO
  */
  public function actionPasswordChangeStart(){

    $data["keyFetchToken"]="fa6c7b6536e8c0adff9ca426805e9479607b7c105050f1391dffed2a9826b8ad";
    $data["passwordChangeToken"]="0208a48ca4f777688a1017e98cedcc1c36ba9c4595088d28dcde5af04ae2215b";
    $data["verified"]=true;
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $data;
  }
  /**
  * POST /v1/password/change/finish (:lock: passwordChangeToken)
  * :lock: HAWK-authenticated with the passwordChangeToken.
  * Change the password and update wrapKb.
  * TODO
  */
  public function actionPasswordChangeFinish(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }
  /**
  * POST /v1/password/forgot/send_code
  * Not HAWK-authenticated.
  * This requests a "reset password" code to be sent to the user's recovery email.
  * The user should type this code into the agent, which will then submit it to
  * /v1/password/forgot/verify_code (described below). verify_code will then return a accountResetToken,
  * which can be used to reset the account password.
  * This code will be either 8 or 16 digits long, and the send_code response indicates the code length
  * (so the UI can display a suitable input form). The email will either contain the code itself, or will contain
  * a link to a web page which will display the code.
  * The send_code response includes a passwordForgotToken, which must be submitted with the code to
  * /v1/password/forgot/verify_code later.
  * The response also specifies the ttl of this token, and a limit on the number of times verify_code can be called
  * with this token. By limiting the number of submission attempts, we also limit an attacker's ability to guess the code.
  * After the token expires, or the maximum number of submissions have happened, the agent must use send_code again
  * to generate a new code and token.
  * Each account can have at most one passwordForgotToken valid at a time. Calling send_code causes any existing
  * tokens to be canceled and a new one created. Each token is associated with a specific code, so send_code also
  * invalidates any existing code
  */
  public function actionPasswordForgotSendCode(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }
  /**
  * POST /v1/password/forgot/resend_code (:lock: passwordForgotToken)
  * :lock: HAWK-authenticated with the passwordForgotToken.
  * While the agent is waiting for the user to paste in the forgot-password code, if the user believes the email has been lost or accidentally deleted, the /v1/password/forgot/resend_code API can be used to send a new copy of the same code.
  * This API requires the passwordForgotToken returned by the original send_code call (only the original browser which started the process may request a replacement message). It will return the same response as send_code did, except with a shorter ttl indicating the remaining validity period. If verify_code has been called some number of times with the same token, then tries will be smaller too.
  */
  public function actionPasswordForgotResendCode(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }
  /**
  * POST /v1/password/forgot/verify_code (:lock: passwordForgotToken)
  * :lock: HAWK-authenticated with the passwordForgotToken.
  * Once the code created by /v1/password/forgot/send_code is emailed to the user,
  * and they paste it into their browser, the browser agent should deliver it to this verify_code endpoint
  * (along with the passwordForgotToken). This will cause the server to allocate and return an accountResetToken,
  * which can be used to reset the account password and wrap(kB) with the /v1/account/reset API (described above).
  */
  public function actionPasswordForgotVerifyCode(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }

  /**
  * GET /v1/password/forgot/status (:lock: passwordForgotToken)
  *:lock: HAWK-authenticated with the passwordForgotToken.
  * Returns the status for the passwordForgotToken.
  * If the request returns a success response, the token has not yet been consumed.
  * When the token is consumed by a successful reset or expires you can expect to get
  * a 401 HTTP status code with an errno of 110.
  */
  public function actionPasswordStatus(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return new \StdClass();
  }
  /**
  * POST /v1/get_random_bytes
  * Not HAWK-authenticated.
  * Get 32 bytes of random data. This should be combined with locally-sourced entropy when creating salts, etc.
  */
  public function actionMiscellaneousRandomBytes(){
    $json["data"]= "ac55c0520f2edfb026761443da0ab27b1fa18c98912af6291714e9600aa34991";
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $json;
  }

  /**
  * GET .well-known/browserid
  * Return the public key for this account server
  */
  public function actionGetPublicKey(){
    $publicKey=file_get_contents(\Yii::getAlias("@app/storage/BrowserID/keys/public_key_content.txt"));
    return '{"public-key":'.$publicKey.',"provisioning":"/provision","authentication":"/auth#native"}';
  }
  /*
  *	Get the session token if the couple is ok
  */
  public function actionGetValidatedSessionToken($email,$authPW){
    if ($this->isPasswordOk){
      return getSessionToken();
    }
    else
    {
      return false;
    }
  }
  /*
  *
  */
  public function getWrapWrapKey($bigStretchedPW){
    $info=$this->KW('wrapwrapKey');
    $salt="";
    $length=32;
    $wrapwrapKey=Crypto::hkdf($bigStretchedPW, 'sha256', $salt, $length, $info);
    return $wrapwrapKey;
  }
  /*
  *
  */
  public function getWrapkB($wrapwrapkey,$wrapwrapkB=null){

    if ($wrapwrapkB==null)
    $wrapwrapkB=$this->compte->wrapWrapKb;	//normaly we don't pass 2nd argument, only for tests

    return bin2hex(hex2bin($wrapwrapkey) ^ hex2bin($wrapwrapkB));	//Xor work on bin
  }
  public function getUnwrapBkey($wrapwrapkey,$wrapwrapkB=null){
    $info=$this->KW('unwrapBkey');
    $salt="";
    $length=32;
    $unwrapBkey=hkdf($authPW, 'sha256', $salt, $length, $info);
    return $unwrapBkey;
  }






  /*
  * create or return the current session Token
  */
  public function getSessionToken(){
    $session=AccSessionTokens::find()->where(['uid'=>$this->uid])->One();
    if ($session==null){
      $session = new AccSessionTokens();
      //we create a new session token
      $session->tokenId=$this->getRand();
      $session->tokenData='hey dude !!';
      $session->createdAt=decistamp();
      $session->uid=$this->compte->uid;
      if (!$session->save()){
        \Yii::warning($session->getErrors());
        return FxaError::fxaAuthError(400,'error saving session token');
      }
    }
    return $session->tokenId;
  }

  /*
  *
  */
  private function getKeyFetchToken($sessionTokenId,$bigStretchedPW){
    $keys=AccKeyfetchtokens::find()
    ->where(['uid'=>$this->compte->uid])
    ->One();
    if ($keys===null){
      $keys=new AccKeyfetchtokens();
      $keys->uid=$this->compte->uid;

      $keyFetchToken=$this->getRand();
      list($keys->tokenId,$keys->authKey)=$this->getTokenID_reqHMACkey($sessionTokenId);

      $keys->createdAt=decistamp();
      //calculate warpkb
      $wrapwrapKey=$this->getWrapWrapKey($bigStretchedPW);
      $wrapkB=$this->getWrapkB($wrapwrapKey,$this->compte->wrapWrapKb);

      $keys->keyBundle=$this->compte->kA.$wrapkB;
      if (!$keys->save()){
        \Yii::warning($keys->getErrors());
        return FxaError::fxaAuthError(400,'error key token');
      }
    }
    return $keyFetchToken;
  }

  /**
  * Key Wrapping with a name
  *
  * @method kw
  * @static
  * @param {String} name The name of the salt
  * @return {bitArray} the salt combination with the namespace
  */
  public function KW($name){
    return "identity.mozilla.com/picl/v1/".$name;
  }
  /*
  *
  */
  public function KWE($name, $emailUTF8){
    return "identity.mozilla.com/picl/v1/" + $name +":" + $emailUTF8;
  }

  /*
  *
  */
  public function getTokenID_reqHMACkey($sessionToken){
    $info=$this->KW('sessionToken');
    $salt=null;
    $length=64;	//tokenId && reqHMACkey
    $result=Crypto::hkdf($sessionToken, 'sha256', $salt, $length, $info);
    return  str_split($result,64); //1st is token id, 2nd is reqHMACkey
  }
  /*
  * Used when testing password / create account
  */
  public function getBigStretchedPW($AuthPW,$salt){

    return Crypto::myScrypt($AuthPW, $salt,64*1024, 8, 1, 32);

  }
  /*
  * Used when creating account
  */
  // PRK = HKDF-Extract([test vector values])
  // OKM = HKDF-Expand(PRK, [test vector values])
  public function getVerifyHash($IKM){
    $info=$this->KW('verifyHash');
    $salt="";
    $length=32;
    return Crypto::hkdf($IKM, 'sha256', $salt, $length, $info);
  }
  /*
  * get uid RFC4122
  */
  private function getUid(){
    $data=openssl_random_pseudo_bytes(16);
    assert(strlen($data) == 16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
  }
  /*
  *
  */
  private function printHex($name, $value, $groups_per_line=1){
    printf("%s:\n",$name);
    $tblChaines=str_split($value,16);
    if ($tblChaines!=false)
    foreach ($tblChaines as $chaine)
    printf("%s\n",$chaine);
  }
  /*
  * return an hex random string
  */
  public function getRand($size=32){
    //Cstrong est un bool qui sais si l'algo utilisé estr strong ou pas
    return bin2hex(openssl_random_pseudo_bytes ($size,$cStrong));
  }



}
/*
*   Login
The server uses the email address to look up the database row, extracts authSalt,
performs the same stretching as during account creation to obtain "bigStretchedPW" and then "verifyHash",
then compares verifyHash against the stored value. If they match, the client has proven knowledge of the password,
and the server creates a new session. The server returns the newly-generated
sessionToken to the client, along with its account identifier (uid).
POST /account/login?keys=true If the client wants to get encryption keys for Sync in addition to signed certificates
JSON reponse bundle=e3e3837ee72484dd76088747d5a0762acf0e8ca3694d3573126edecf4e80970be561e18c3043223332e90468d4610671d45c9b639b640fa8f1555eb551616426b82e7cc77ae3669cc8e42543e9c79d5b3f43785734bc889f696784d3778dea6b
@return  sessionToken
The server can support multiple sessions per account (typically one per client device,
plus perhaps others for account-management portals). The sessionToken lasts foreve

Many keyserver APIs require a HAWK-protected request that uses the sessionToken.
Some of them require that the account be in the "verified" state.

GET /account/devices
POST /session/destroy
GET /recovery_email/status
POST /recovery_email/resend_code
POST /certificate/sign (requires "verified" account)


{"uid":"3cc2486486294f74bcdd7c0f6bc53b01",
"sessionToken":"54dd113b3e9233053875da57faf22420ec6cce2ce8d33f783b738668ac072552",
"keyFetchToken":"b266fd908c7acdfcafce8d6666afab9102679d1b6869c6075056f3b22353a2f0",
"verified":true,
"authAt":1409651914}
*/
