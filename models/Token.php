<?php
namespace app\models;

use Yii;
use yii\base\Model;

use Codeception\Util\Debug;
use BrowserID\AbstractPublicKey;
use BrowserID\AbstractSecretKey;

use BrowserID\CertAssertion;
use BrowserID\CertBundle;

use app\models\SyncUser;

/**
* LoginForm is the model behind the login form.
*/
class Token extends Model
{
  /**
  * Is initialized
  *
  * @access private
  * @static
  * @var type
  */

  //DEFAULT_SECRET = os.urandom(32);
  const DEFAULT_TIMEOUT = 3600; // 1 hours 60 * 60 secondes
  const DEFAULT_HASHMOD = "sha256";
  const DEFAULT_DIGEST_SIZE = 32;
  //  Unique info strings for mixing into HKDF.
  const HKDF_INFO_SIGNING = "services.mozilla.com/tokenlib/v1/signing";
  const HKDF_INFO_DERIVE = "services.mozilla.com/tokenlib/v1/derive/";


  private $assertionData;
  private $sigSecret;
  public $hashmod_digest_size;
  public $param;
  private $hashmode,$timeout;

  private $secretToken;

  private $uid =null;

  /**
  * @return array the validation rules.
  */
  public function rules()
  {
    return [ ];
  }

  public function __construct($param=null){

    //Create a secret token if needed
    $secretToken=\Yii::getAlias('@storage/secretToken');

    if (!is_file($secretToken)){
      file_put_contents($secretToken,bin2hex(openssl_random_pseudo_bytes(32)));
    }

    if (!isset($param['timeout'])){
      $param['timeout']=self::DEFAULT_TIMEOUT;
    }

    if (!isset($param['hashmode'])){
      $param['hashmode']=self::DEFAULT_HASHMOD;
    }

    if (!in_array($param['hashmode'], hash_algos())){
      throw new \Exception('Invalid hash algo : '.$param['hashmode']);
      return null;
    }
    $param['hashmod_digest_size']=strlen(hex2bin(hash($param['hashmode'], 'hop', false)));  //grab hash size ...

    foreach ($param as $key=>$value){
      $this->{$key}=$value;
    }

    $this->secretToken=file_get_contents($secretToken);
    $this->sigSecret=\Crypto\Crypto::HKDF($this->secretToken, $this->hashmode,null,$this->hashmod_digest_size,self::HKDF_INFO_SIGNING);

  }
  /**
  * Generate a new token embedding the given dict of data.
  *
  * The token is a JSON dump of the given data along with an expiry
  * time and salt.  It has a HMAC signature appended and is b64-encoded
  * for transmission.
  */
  public function makeToken($data){
    if (!is_array($data)){
      $data=(array)json_decode($data);
    }

    if (!isset($data['salt'])){
      $data["salt"] = $this->genSalt();
    }

    if (!isset($data['expires'])){
      $data["expires"] = $this->decistamp(microtime(true)+ $this->timeout);
    }
    $payload = json_encode($data);
    $sig = $this->_get_signature($payload);
    return $this->encode_token_bytes($payload.$sig);
  }

  /**
  * Extract the data embedded in the given token, if valid.
  * The token is valid if it has a valid signature and if the embedded
  * expiry time has not passed.  If the token is not valid then this
  * method raises ValueError.
  */
  public function verifyToken($token,$secret=null,$now=null){
    $decoded_token = $this->decode_token_bytes($token);
    list($payload,$sig)=$this->extractTokenData($decoded_token);

    json_decode($payload);
    if (JSON_ERROR_NONE!=json_last_error()){
      throw new \Exception('Invalid json payload');
    }
    # Carefully check the signature.
    # This is a deliberately slow string-compare to avoid timing attacks.
    # Read the docstring of strings_differ for more details.
    $expected_sig =$this->_get_signature($payload,$secret);
    //signature valid ?

    if (strcmp($sig, $expected_sig)==0)
    {
      # Only decode *after* we've confirmed the signature.
      # This should never fail, but well, you can't be too careful.
      //try:
      $data = (array)json_decode($payload);

      //raise errors.MalformedTokenError(str(e))
      # Check whether it has expired.
      if ($now ==null){
        $now = $this->decistamp();
      }
      //Allow  expired token

      if ($data["expires"] < $now){
        throw new \Exception('Invalid token : expired');
      }
      return $data;
      //raise errors.ExpiredTokenError()

    }
    throw new \Exception('Invalid tokenSignature');
  }

  /**
  *
  */
  public function changeSecretToken($secret){
    $this->sigSecret=$secret;
  }
  /*
  *
  */
  public function genSalt($size=3){
    return bin2hex(openssl_random_pseudo_bytes($size));
  }
  /*
  * Extract payload, sig from a token base on digestSize
  */
  public function extractTokenData($decoded_token){
    $payload = substr($decoded_token,0,strlen($decoded_token)-$this->hashmod_digest_size);
    $sig = substr($decoded_token,strlen($decoded_token)-$this->hashmod_digest_size,strlen($decoded_token));
    return array($payload,$sig);
  }
  /*
  * Calculate the HMAC signature for the given value."""
  * return in binary format
  */
  private function _get_signature($payload,$secret=null){
    if ($secret==null){
      $secret=$this->sigSecret;
    }

    $signature=hex2bin(hash_hmac($this->hashmode,$secret, $payload));
    return $signature;
  }
  /*
  Encode the string like python url64encode
  */
  public function encode_token_bytes($data){
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }
  /*
  Decode the string like python url64encode
  return null if not base 64 encoded string
  */

  public function decode_token_bytes($data){
    $data=base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT),true);
    if ($data===false){
      throw new \Exception('Invalid decode_token_bytes');
    }
    return $data;
  }
  /*
  Get the derived secret key associated with the given token.
  A per-token secret key is calculated by deriving it from the master
  secret with HKDF.
  */
  public function get_derived_secret($token){
    $decoded_token=$this->decode_token_bytes($token);
    list($payload,$sig)=$this->extractTokenData($decoded_token);
    $salt=$payload=json_decode($payload)->salt;
    $info = self::HKDF_INFO_DERIVE.$token;
    $secret = \Crypto\Crypto::HKDF($this->secretToken, $this->hashmode,$salt,$this->hashmod_digest_size,$info);
    return $this->encode_token_bytes($secret);
  }


  // ##
  // ## Assertion part
  // ##

  /*
  * Verify an assertion
  */
  public  function verifyAssertion($audience,$assertion=null){
    if ($assertion===null && \Yii::$app->request->headers->has('Autorization')){
      $assertion=\Yii::$app->request->headers['HTTP_AUTHORIZATION'];
    }
    $assertion=str_replace('BrowserID ','',$assertion);
    $jSonRes= json_decode(\BrowserID\Verifier::verify($assertion, $audience));

    //class ExceptFoo extends \Exception { }
    if (property_exists($jSonRes,'status') && $jSonRes->status=="okay"){
      if (in_array($jSonRes->issuer,\Yii::$app->params['assertionIssuer'])){
        \Yii::info('Assertion ok for : ['.$jSonRes->issuer.']');
        return $this->assertionData=(array)$jSonRes;
      }
      else{
        $jSonRes->reason='bad issuer';
      }
    }
    throw new \Exception($jSonRes->reason);
  }

  /*
  * Create an assertion
  */
  public function createAssertion($principal,$audience){

    //generate a new key ONLY if any present
    \Crypto\Crypto::generateNewDSAKey(\Yii::getAlias('@storage/BrowserID/keys/'));

    $keyPublic=file_get_contents(\Yii::getAlias('@storage/BrowserID/keys/public_key_content.txt'));
    $keyPrivate=file_get_contents(\Yii::getAlias('@storage/BrowserID/keys/private_key_content.txt'));


    $publicKeyIdentity = AbstractPublicKey::deserialize($keyPublic);
    $secretKeyIdentity = AbstractSecretKey::deserialize($keyPrivate);

    $assertion = CertAssertion::createAssertion($audience, $secretKeyIdentity);

    $identityCert = CertAssertion::createIdentityCert($principal, $publicKeyIdentity);

    $bundle = new CertBundle($assertion, array($identityCert));
    $assertion = 'BrowserID '.$bundle->bundle();

    return $assertion;
  }
  // ##
  // ## other part
  // ##
  /*
  *
  */
  public function getEmail(){
    if (isset($this->assertionData['email']))
    return $this->assertionData['email'];
    else
    return false;
  }
  /*
  * return the complete array after the usage of verifyA ssertion
  */
  public function getAssertionResult(){
    return $this->assertionData;
  }

  /*
  *
  */
  public function generateDSKey(){




  }
  /*
  Create a user if any after the assertion is valid
  id – a signed authorization token, containing the user’s id for the application and the node.
  key – a secret derived from the shared secret
  uid – the user id for this service
  api_endpoint – the root URL for the user for the service.
  duration – the validity duration of the issued token, in seconds.
  Exemple de la demande :
  "uid": 5977387
  "hashalg": "sha256"
  "api_endpoint": "https://sync-100-us-west-2.sync.services.mozilla.com/1.5/5977387"
  "key": "RFa6tjjQiOAA59_13YjuCvNOiKhFPxmJuLJmGkaIJSU="
  "duration": 3600
  "id": "eyJub2RlIjogImh0dHBzOi8vc3luYy0xMDAtdXMtd2VzdC0yLnN5bmMuc2VydmljZXMubW96aWxsYS5jb20iLCAiZXhwaXJlcyI6IDE0MTA1OTI2OTUsICJzYWx0IjogImQ2OTg2OCIsICJ1aWQiOiA1OTc3Mzg3fWOO1f1Io9xPPau_YbbsL0CZWbnOVUqrHddN_dQZRdGb"}';
  "node": "https://sync-100-us-west-2.sync.services.mozilla.com"
  "expires": 1410592695
  "salt": "d69868"
  "uid": 5977387} + signature

  //$endpointUrl= '/fxasync/'.$f3->get('FFSync.ProtocoleVersion');
  //This is valid for all modules
  //$f3->set('audience',$f3->get('publicURI').$endpointUrl.'/');

  */

  public  function createAuthToken(){
    //the only valid audience for the token
    $audience=\Yii::$app->params['publicURI'];

    \Yii::info('Audience :'.$audience, __METHOD__);
    /*\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $ret=\Yii::$app->request->headers;
    return $ret;*/
    try{
      if (!\Yii::$app->request->headers->has('Authorization') || strpos(\Yii::$app->request->headers->get('Authorization'),'BrowserID')===false){
        throw new \Exception('assertion not found');
      }
      $assertion=\Yii::$app->request->headers->get('authorization');
      $assertion=str_replace('BrowserID ','',$assertion);
      \Yii::info('Assertion :'.$assertion);
      $data=$this->verifyAssertion($audience,$assertion);
    }
    catch (\Exception $e){
      throw new \Exception('Invalid BrowserID : '.$e->getMessage());
    }
    if (strcmp($data['audience'] ,$audience)!=0){
      throw new \Exception('Invalid Audience'.$data['audience']. ' '.$audience);
    }
    //Assertion is validated, we can create an authToken
    $time=$this->decistamp();
    header('X-Timestamp: '.$time);
    //generate the derived secret and join it in the id
    $hdkfEmail=\Crypto\Crypto::HKDF('', self::DEFAULT_HASHMOD,null,self::DEFAULT_DIGEST_SIZE,$data['email']);

    $userInfo=SyncUser::Find()->Where(['email'=>$hdkfEmail])->One();
    //create a user if needed, update access time
    if (count($userInfo)==0){
      $userInfo = new SyncUser();
      \Yii::info('User not found, creating it');
      $lastUserId=(new \yii\db\Query)->from('sync_user')->max('user_id');
      if ($lastUserId===null){
        $userInfo->user_id=1;
      }
      else{
        $userInfo->user_id=($lastUserId+1);
      }
      $userInfo->email=$hdkfEmail;

      if (!$userInfo->save()){
        \Yii::warning($userInfo->getErrors() );
      }
    }
    else{
      \Yii::info('User found :'.$userInfo->user_id);
    }
    //set user_id for the class
    $this->getUid($userInfo->user_id);
    $tokenData['uid']=$userInfo->user_id;
    $tokenData['node']=\Yii::$app->params['publicURI'].'/'.\Yii::$app->params['endPointUrl'];

    $data['id']=$this->makeToken(json_encode($tokenData));
    $data['uid']=$userInfo->user_id;	// l'uid de utilisateur fourni par l'assertion
    $data['hashalg']=$this->hashmode;
    $data['duration']=$this->timeout;
    $data['api_endpoint']=$tokenData['node'].'/'.$this->getUid();
    $data['key']=$this->get_derived_secret($data['id']);
    return $data;
  }

  // ##
  // ## HawkId part
  // ##
  /*
  *	validate a Hawk autorisation (in the sync server context, where ID = authToken)
  */
  public function verifyHawk($myHawk=null,$testUrl=null,$method='GET'){

    //a-ton un HawkId ?
    if ($myHawk==null && (!\Yii::$app->request->headers->has('Authorization') ||
    strpos(\Yii::$app->request->headers->get('Authorization'),'Hawk id=')===false)){
      return FxaError::fxaAuthError(110,'Hawkid not found');
    }
    if ($myHawk==null){
      $myHawk=\Yii::$app->request->headers->get('Authorization');
    }

    //try{
    // on valide le token présent dans l'attribut "id" du HawkId
    $params=\Hawk\Hawk::parseHeader($myHawk);

    $data=$this->extractTokenData($this->decode_token_bytes($params['id']));
    $tokenData=$this->verifyToken($params['id']);

    //recherche de l'utilisateur
    $userInfo=SyncUser::find()
    ->Where([ 'user_id'=>$tokenData['uid']])
    ->One();

    \Yii::info('tokenUid :'.$tokenData['uid']);
      //pas de validation de l'utilisateur, on pourrais être ne création
    /*if (count($userInfo)!=1){
      throw new \yii\web\ForbiddenHttpException('Unknow user');
    }*/

      //Only valid in test env to validate an hawk in console env
    if (defined('YII_ENV') && YII_ENV=='test'  && $testUrl!=null){
      $url=$testUrl;
    }
    else{
      $url=\Yii::$app->request->absoluteUrl;
      $method=\Yii::$app->request->getMethod();
    }

    if (strlen($url)==0){
      throw new \exception ('invalide url for Hawk')  ;
    }

    $secret=$this->get_derived_secret($params['id']);

    //On valide le hawk ID (exception en cas de non validation)
    \Hawk\Hawk::verifyHeader($myHawk, $secret, $method,$url);
        //if we are here, no exception = good hawk
      /*}catch (\Exception $e){
    return FxaError::fxaAuthError(110,$e->getMessage());
  }*/
  return $tokenData['uid'];
}
/*
* create a userId
*/
private function getUid($nb=null){
  if ($this->uid===null){
    $this->uid=(int)$nb;
  }
  return $this->uid;
}
// ################################################
// ################################################
// ################################################
/*
*
We need to format timestamp data with decimal .00
*/
function decistamp($time=null){
  if ($time===null){
    $time=microtime(true) ;

  }
  $time=number_format((float)$time, 2, '.', '')		;

  return $time;
}

}
?>
