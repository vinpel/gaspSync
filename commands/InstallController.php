<?php
/**
* @link http://www.yiiframework.com/
* @copyright Copyright (c) 2008 Yii Software LLC
* @license http://www.yiiframework.com/license/
*/

namespace app\commands;

use yii\console\Controller;



//use BrowserID\AbstractPublicKey;
//use BrowserID\AbstractSecretKey;

use BrowserID\CertAssertion;
use BrowserID\CertBundle;


use BrowserID\Secrets;
use BrowserID\Algs\RSAKeyPair;
use BrowserID\WebToken;
/**
* This command configure gaspSync
*
* Run it !
*
* @author Qiang Xue <qiang.xue@gmail.com>
* @since 2.0
*/
class InstallController extends Controller
{
  private $RC="\n";
  /**
  * special Configuration for travis
  */
  public function actionTravis() {

    copy(\Yii::getAlias('@app/config/travis.init.php'),
    \Yii::getAlias('@app/config/local.init.php'));
    $this->actionIndex();
  }
  /**
  * special Configuration for travis
  */
  public function actionSynology() {

    $this->RC='<br/>';
    $this->actionIndex();
  }

  /**
  * This command echoes what you have entered as the message.
  * @param string $message the message to be echoed.
  */
  public function actionIndex()
  {
    echo "gaspSync Installation(based on yii advanced template v1.0)".$this->RC;
    if (!extension_loaded('mcrypt')) {
      die('The mcrypt PHP extension is required by Yii2.');
    }

    $root =substr(str_replace('\\', '/', __DIR__),0,-8);;

    $local=$root.'/config/local.init.php';

    if (!is_file($local)){
      echo "\n  Enter the public URI of the server without ending slash (need to be https) [https://localhost:4000]:\n";
      $data['publicURI'] = trim(fgets(STDIN));
      if (strlen($data['publicURI'])==0){
        $data['publicURI']='https://localhost:4000';
      }
      echo "\n  Information for the database connexion (/conf/db.php): ";
      echo "\n  --------------------";

      echo "\n  Host:  [localhost]";
      $data['host'] = trim(fgets(STDIN));
      if (strlen($data['host'])==0){
        $data['host']='localhost';
      }
      echo "\n  Database Name:  [sync]";
      $data['database'] = trim(fgets(STDIN));
      if (strlen($data['database'])==0){
        $data['database']='sync';
      }
      echo "\n  Username:  [root]";
      $data['username'] = trim(fgets(STDIN));
      if (strlen($data['username'])==0){
        $data['username']='root';
      }
      echo "\n  Password:  [toor]";
      $data['password'] = trim(fgets(STDIN));
      if (strlen($data['password'])==0){
        $data['password']='toor';
      }
        //save the configuration, if you need to launch multiple time the install
      echo "\n      create file config/local.init.php";
      $fc=var_export($data,true);
      file_put_contents($root.'/config/local.init.php',"<?php\n return $fc \n?>");
    }
    else{
      $data=require($local);
    }
    echo "      editing config/db.php".$this->RC;
    $db=var_export([
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host='.$data['host'].';dbname='.$data['database'],
      'username' => $data['username'],
      'password' => $data['password'],
      'charset' => 'utf8',
    ],true);
    $fileContent="<?php\n return $db \n?>";
    file_put_contents($root.'/config/db.php',$fileContent);



    $this->setPublicURI($root,$data['publicURI']);
    $this->setIssuer($root,$data['publicURI']);


    $this->setWritable($root,['runtime','web/assets','storage']);

    //Storage paths
    $target=[
        'BrowserID',
        'BrowserID/keys',
        //'BrowserID/keys/tests', //this directory is created when you make tests
        'BrowserID/var',
        'BrowserID/well-known'
        ];
    foreach ($target as $rep){
      $path=\Yii::getAlias('@storage/'.$rep);
      echo "      mkdir $path".$this->RC;
      if (!is_dir($path)){
        mkdir($path, 0777, true);
      }
    }
    $this->setWritable($root,$target);
    $this->setCookieValidationKey($root.'/config',['web.php']);

    if (!is_file($root.'/storage/secretToken')){
      echo "      creating new secretToken".$this->RC;
      $length = 64;
      $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
      $secretKey = bin2hex(strtr(substr(base64_encode($bytes), 0, $length), '+/=', '_-.'));
      file_put_contents($root.'/storage/secretToken',$secretKey);
    }

    echo "      creating dsaKeyPair".$this->RC;
    $dsa=new \Crypto\Crypto();
    $keyPath=\Yii::getAlias('@storage/BrowserID/keys/');
    $dsa->generateNewDSAKey($keyPath);

    echo "      creating root certificate".$this->RC;
    // Comment the following line out to test the script!

    $name = 'root';
    $keysize = 256;

      //Place the openSSL config file
    copy(\Yii::getAlias('@vendor/vinpel/php-browseridlib/storage/BrowserID/var/openssl.cnf'),
    \Yii::getAlias('@storage/BrowserID/var/openssl.cnf'));

    // Generate keypair:
    echo "      -> generate key pair with keysize $keysize...".$this->RC;
    $pair = RSAKeyPair::generate($keysize);

    echo "      -> keys were generated!".$this->RC;

    // Write secret key to file:
    echo "      -> write Secret Key...".$this->RC;

    $pathSecretKey = Secrets::getPathSecretKey($name);


    $handle = fopen($pathSecretKey, "w+");

    fwrite($handle, $pair->getSecretKey()->serialize());
    fclose($handle);
    echo "      -> secret Key was written to " . $pathSecretKey.$this->RC ;

    // Write public key to file:
    echo "      -> write Public Key...".$this->RC;
    $pathPublicKey = Secrets::getPathPublicKey($name);
    $public = array("public-key"=>json_decode($pair->getPublicKey()->serialize(), true));
    $token = new WebToken($public);
    $handle = fopen($pathPublicKey, "w+");
    fwrite($handle, $token->serialize($pair->getSecretKey()));
    fclose($handle);
    echo "      -> public Key was written to " . $pathPublicKey .$this->RC;















    echo "End of install".$this->RC;
  }
  function setWritable($root, $paths)
  {
    foreach ($paths as $writable) {
      echo "      chmod 0777 $writable".$this->RC;
      @chmod("$root/$writable", 0777);
    }
  }

  function setExecutable($root, $paths)
  {
    foreach ($paths as $executable) {
      echo "      chmod 0755 $executable".$this->RC;
      @chmod("$root/$executable", 0755);
    }
  }

  function setCookieValidationKey($root, $paths)
  {
    foreach ($paths as $file) {
      echo "      generate cookie validation key in $file".$this->RC;
      $file = $root . '/' . $file;
      $length = 32;
      $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
      $key = strtr(substr(base64_encode($bytes), 0, $length), '+/=', '_-.');
      $content = preg_replace('/(("|\')cookieValidationKey("|\')\s*=>\s*)(""|\'[A-Za-z0-9._:\/-]*\')/', "\\1'$key'", file_get_contents($file));
      file_put_contents($file, $content);
    }
  }

  function createSymlink($links)
  {
    foreach ($links as $link => $target) {
      echo "    symlink $target as $link".$this->RC;
      if (!is_link($link)) {
        symlink($target, $link);
      }
    }
  }

  function setPublicURI($root,$uri){
    $file =  $root.'/config/params.php';
    $content = preg_replace('/(("|\')publicURI("|\')\s*=>\s*)(""|\'[A-Za-z0-9._:\/]*\')/', "\\1'$uri'", file_get_contents($file));
    file_put_contents($file, $content);
  }

  function setIssuer($root,$uri){
    $file =  $root.'/config/params.php';
    $data=parse_url($uri);

    $content = preg_replace('/(("|\')assertionIssuer("|\')\s*=>\s*\[)(""|[A-Z,\'a-z0-9._:\/]*)(\])/', "\\1'localhost','api.accounts.firefox.com','".$data['host']."']", file_get_contents($file));
    file_put_contents($file, $content);
  }
}



?>
