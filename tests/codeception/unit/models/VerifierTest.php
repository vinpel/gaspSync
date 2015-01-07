<?php
// codecept -v --debug  run unit models/VerifierTest.php
namespace tests\codeception\unit\models;

use yii\codeception\TestCase;
use app\models\Token;

use Codeception\Util\Debug;

use Crypto\Crypto;


class VerifierTest extends TestCase
{

  private $keyPath;
  private $privKey;
  private $pubKey;
  /**
  *
  */
  protected function setUp()
  {
    parent::setUp();

    $this->keyPath=\Yii::getAlias('@storage/BrowserID/keys/tests/');
    $dsa=new Crypto();
    $dsa->generateNewDSAKey($this->keyPath);

    Debug::debug($this->keyPath);
    $this->privKey=file_get_contents($this->keyPath.'private_key.pem');
    $this->pubKey=file_get_contents($this->keyPath.'public_key.pem');

  }
  /**
  *
  */
  public function testVerifierSignature(){
    //Debug::debug($this->keyPath);
    $dsa=new Crypto();
    $dsa->generateNewDSAKey($this->keyPath.'/');

    $message='thisisatest';
    $signature=$dsa->sign($this->privKey,$message);
    $res=$dsa->verifySign($this->pubKey,$message,$signature.'-');
    //sign text from generated pub/priv DSA Key
    $this->assertTrue($res);
  }
/**
* 'Reject signed message forged'
*/
  public function testVerifierCreateVerifyAsser(){
    $message='thisisatest';

    $dsa=new Crypto();
    $signature2=$dsa->sign($this->privKey,$message."5465468435484");

    $res=$dsa->verifySign($this->pubKey,$message,$signature2);

    $this->assertFalse($res);
    //test key -> PEM for DSA
  }
  /*
  * transform KeyElement to PEM

  public function testTokenKey2Pem(){
    $dsa=new Crypto();

    $DSAKey = json_decode(file_get_contents($this->keyPath.'public_key_content.txt'));

    $calculatedPEM=$dsa->KeyToDsaPem($DSAKey);
    $generatedPEM=$dsa->removeHeaderFooter($this->pubKey,false);    //remove 1st & last line
    $res=strcmp($generatedPEM,$calculatedPEM);
    $this->assertTrue($res==0);


    //print "Calc PEM :\n";
    //$dsa->viewPEMElement($generatedPEM);
    //print "Gen from key PEM :\n";
    //$dsa->viewPEMElement($generatedPEM);

  }
  */

}
?>
